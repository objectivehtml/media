<?php

namespace Objectivehtml\Media\Support;

use Illuminate\Support\Arr;

class ExifData {

    protected $data;

    public function __construct(array $data)
    {
        $this->data = (array) $data;
    }

    public function __get($key)
    {
        if(method_exists($this, $key)) {
            return $this->$key();
        }

        return $this->get($key);
    }

    public function get($key)
    {
        return Arr::get($this->data, $key);
    }

    public function latitude()
    {
        $coordinate = ($latitude = $this->get('GPSLatitude')) ? $this->convertDegreesToDecimates($latitude) : null;

        if($coordinate === null) {
            return null;
        }

        $ref = $this->get('GPSLatitudeRef') ? $this->get('GPSLatitudeRef') : 'N';

        return $coordinate * ($ref == 'N' ? 1 : -1);
    }

    public function longitude()
    {
        $coordinate = ($longitude = $this->get('GPSLongitude')) ? $this->convertDegreesToDecimates($longitude) : null;

        if($coordinate === null) {
            return null;
        }

        $ref = $this->get('GPSLongitudeRef') ? $this->get('GPSLongitudeRef') : 'W';

        return $coordinate * ($ref == 'W' ? -1 : 1);
    }

    protected function convertDegreesToDecimates($coord)
    {
        $d = $this->divideString($coord[0]);
        $m = $this->divideString($coord[1]);
        $s = $this->divideString($coord[2]);

        return $this->signum($d) * (abs($d) + ($m / 60.0) + ($s / 3600.0));
    }

    protected function divideString($string)
    {
        $string = explode('/', $string);

        return $string[1] ? (float) $string[0] / (float) $string[1] : $string[0];
    }

	protected function signum($number)
	{
		return $number ? ($number < 0 ? - 1 : 1) : 0;
	}

}
