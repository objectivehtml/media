<?php

namespace Objectivehtml\Media\Jobs;

use Illuminate\Bus\Queueable;
use Objectivehtml\Media\Model;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Objectivehtml\Media\Support\Geocoder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Objectivehtml\Media\Events\GeocodedMedia;

class GeocodeModel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $model;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if(!$this->shouldGeocodeExif()) {
            return;
        }
        
        $geocoder = new Geocoder;

        $addresses = $geocoder->reverse(
            $this->model->exif->latitude,
            $this->model->exif->longitude
        );

        $response = collect($addresses)->flatten(1);

        $this->model->meta('geocoder', $response->map(function($location) {
            return $location->toArray();
        }));

        $this->model->save();

        event(new GeocodedMedia($this->model, $response));
    }


    protected function isGeocoded(): bool
    {
        return !!$this->model->meta->get('geocoder');
    }

    protected function shouldGeocodeExif(): bool
    {
        return !$this->isGeocoded() && (
            $this->model->exif && $this->model->exif->latitude && $this->model->exif->longitude
        );
    }
}
