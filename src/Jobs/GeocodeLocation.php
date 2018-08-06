<?php

namespace App\Jobs;

use Objectivehtml\Media\Model;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

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
        dd('geocode');
        
        $response = null;

        if(!$response && $this->location->latitude && $this->location->longitude) {
            $response = app('geocoder')->reverse(
                $this->location->latitude,
                $this->location->longitude
            )->get()->first();
        }

        if(!$response && $this->location->address) {
            $response = app('geocoder')->geocode($this->location->address)->get()->first();
        }

        if(!$response && $this->location->ip) {
            $response = app('geocoder')->geocode($this->location->ip)->get()->first();
        }

        if($response) {
            if(!$this->location->address) {
                $this->location->address = $response->getFormattedAddress();
            }

            if(!$this->location->latitude && !$this->location->longitude) {
                $this->location->coordinate = Model::point(
                    $response->getCoordinates()->getLatitude(),
                    $response->getCoordinates()->getLongitude()
                );
            }

            $this->location->geocoder_response = $response->toArray();
            $this->location->geocoded_at = now();
            $this->location->save();
        }
    }
}
