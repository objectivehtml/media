<?php

namespace Objectivehtml\Media\Plugins;

use Closure;
use Http\Client\HttpClient;
use Objectivehtml\Media\Model;
use Geocoder\Provider\Provider;
use Geocoder\ProviderAggregator;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Model\AddressCollection;
use Http\Adapter\Guzzle6\Client;
use Objectivehtml\Media\MediaService;
use Objectivehtml\Media\Support\Applyable;
use Objectivehtml\Media\Support\ApplyToImages;

class GeocoderPlugin extends Plugin {

    use Applyable, ApplyToImages;

    protected $geocoder;

    public function creating(Model $model)
    {
        // Do not geocode if the model doesn't apply, if the model already has
        // geocoder data the file doesn't exist, if the model has no ExifData,
        // or there is no latitude or longitude.
        if(!$this->doesApplyToModel($model) ||
            $model->meta->get('geocoder') ||
            !$model->fileExists ||
            !$model->exif ||
            !$model->exif->latitude ||
            !$model->exif->longitude) {
            return;
        }

        $response = collect($this->reverse($model->exif->latitude, $model->exif->longitude))
            ->flatten(1)
            ->map(function($location) {
                return $location->toArray();
            });

        $model->meta('geocoder', $response);
    }

    public function client(): HttpClient
    {
        return new Client();
    }

    public function locale()
    {
        return app(MediaService::class)->config('geocoder.locale');
    }

    public function store()
    {
        return app(MediaService::class)->config('geocoder.cache.store');
    }

    public function duration()
    {
        return app(MediaService::class)->config('geocoder.cache.store') ?: 0;
    }

    public function providers()
    {
        return app(MediaService::class)->config('geocoder.providers');
    }

    public function geocoder()
    {
        if($this->geocoder) {
            return $this->geocoder;
        }

        $geocoder = new ProviderAggregator();

        $providers = collect($this->providers())->map(function($provider) {
            return $this->instantiateFromArray(key($provider), reset($provider));
        });

        $geocoder->registerProviders($providers->toArray());

        return $this->geocoder = $geocoder;
    }

    public function instantiateFromArray($class, array $args = [])
    {
        return new $class($this->client(), ...$args);
    }

    public function geocode(string $address): AddressCollection
    {
        $cacheKey = str_slug(strtolower(urlencode($address)));

        return $this->cache($cacheKey, function() use ($address) {
            return $this->geocoder()->geocodeQuery(
                GeocodeQuery::create($address)
            );
        });
    }

    public function reverse(float $latitude, float $longitude): AddressCollection
    {
        $cacheKey = str_slug(strtolower(urlencode("{$latitude}-{$longitude}")));

        return $this->cache($cacheKey, function() use ($latitude, $longitude) {
            return $this->geocoder()->reverseQuery(
                ReverseQuery::fromCoordinates($latitude, $longitude)
            );
        });
    }

    public function cache($key, Closure $callback): AddressCollection
    {
        $hashedKey = sha1($key);

        $cachedResponse = app('cache')
            ->store($this->store())
            ->remember($hashedKey, $this->duration(), function () use ($key, $callback) {
                return [
                    'key' => $key,
                    'value' => $callback()
                ];
            });

        if($cachedResponse['key'] !== $key) {
            $this->forget($hashedKey);

            return $this->cache($key, $callback);
        }

        if ($cachedResponse['value']->isEmpty()) {
            app('cache')->forget($key);
        }

        return $cachedResponse['value'];
    }

    public function forget($key): bool
    {
        return app("cache")->store($this->store())->forget($key);
    }

}
