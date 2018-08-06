<?php

namespace App\Images;

class ExifData {

    public $data;

    public function __construct(array $data)
    {
        $this->data = (array) $data;
    }

    public function __get($key)
    {
        if(method_exists($this, $key)) {
            return $this->$key();
        }

        return $this->key($key);
    }

    public function key($key)
    {
        $parts = explode('.', $key);

        while(count($parts)) {
            $part = array_shift($parts);

            if(!isset($subject) && isset($this->data[$part])) {
                $subject = $this->data[$part];
            }
            else if(isset($subject[$part])) {
                $subject = $subject[$part];
            }
        }

        return isset($subject) ? $subject : null;
    }

    public function latitude()
    {
        $coordinate = ($latitude = $this->key('GPSLatitude')) ? $this->convertDegreesToDecimates($latitude) : null;

        $ref = $this->key('GPSLatitudeRef') ? $this->key('GPSLatitudeRef') : 'N';

        return $coordinate * ($ref == 'N' ? 1 : -1);
    }

    public function longitude()
    {
        $coordinate = ($longitude = $this->key('GPSLongitude')) ? $this->convertDegreesToDecimates($longitude) : null;

        $ref = $this->key('GPSLongitudeRef') ? $this->key('GPSLongitudeRef') : 'W';

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
