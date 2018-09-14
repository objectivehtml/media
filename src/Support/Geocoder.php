<?php

namespace Objectivehtml\Media\Support;

use Closure;
use Http\Client\HttpClient;
use Http\Client\Curl\Client;
use Geocoder\Provider\Provider;
use Geocoder\ProviderAggregator;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Model\AddressCollection;
use Objectivehtml\Media\MediaService;

class Geocoder {

    protected $geocoder;

    public function client(): HttpClient
    {
        return new Client();
    }

    public function store(): string
    {
        return app(MediaService::class)->config('geocoder.cache.store', config('cache.default'));
    }

    public function duration(): int
    {
        return app(MediaService::class)->config('geocoder.cache.duration', 0);
    }

    public function providers(): array
    {
        return app(MediaService::class)->config('geocoder.providers');
    }

    public function geocoder()
    {
        if(!$this->geocoder) {
            $providers = collect($this->providers())->map(function($provider) {
                return $this->instantiateFromArray(key($provider), reset($provider));
            });

            $this->geocoder = new ProviderAggregator();
            $this->geocoder->registerProviders($providers->toArray());
        }

        return $this->geocoder;
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

    /*
    public function nearbyPlaces(float $latitude, float $longitude): AddressCollection
    {
        $cacheKey = str_slug(strtolower(urlencode("nearby-{$latitude}-{$longitude}")));

        return $this->cache($cacheKey, function() use ($latitude, $longitude) {
            return $this->geocoder()->reverseQuery(
                ReverseQuery::fromCoordinates($latitude, $longitude)
            );
        });
    }
    */

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

    protected function instantiateFromArray($class, array $args = [])
    {
        return new $class($this->client(), ...$args);
    }

}
