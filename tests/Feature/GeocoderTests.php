<?php

namespace Tests\Feature;

use Tests\TestCase;
use Objectivehtml\Media\Model;
use Geocoder\Model\AddressCollection;
use Objectivehtml\Media\Support\Geocoder;

class GeocoderTests extends TestCase
{

    public function testGeocode()
    {
        $geocoder = new Geocoder;

        $addresses = $geocoder->geocode('Rocky Mountain National Park');

        $this->assertInstanceOf(AddressCollection::class, $addresses);
    }

    public function testReverseGeocode()
    {
        $geocoder = new Geocoder;

        $addresses = $geocoder->reverse(40, -86);

        $this->assertInstanceOf(AddressCollection::class, $addresses);
    }

    public function testGeocodeNearbyPlaces()
    {
        $geocoder = new Geocoder;

        $addresses = $geocoder->nearbyPlaces(40, -86);

        dd($addresses);

        $this->assertInstanceOf(AddressCollection::class, $addresses);
    }

}
