<?php

namespace Tests\Unit\Support;

use Tests\TestCase;
use Geocoder\Model\AddressCollection;
use Objectivehtml\Media\Support\Geocoder;

class GeocoderTest extends TestCase
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
}
