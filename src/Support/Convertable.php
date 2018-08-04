<?php

namespace Objectivehtml\Media\Support;

use Illuminate\Support\Collection;
use Objectivehtml\Media\Model;
use Objectivehtml\Media\Conversions\Conversions;
use Objectivehtml\Media\Contracts\Conversion as ConversionInterface;
use Objectivehtml\Media\Contracts\Convertable as ConvertableInterface;
use Objectivehtml\Media\Contracts\StreamableResource as StreamableResourceInterface;

trait Convertable {

    protected $conversions;

    public function getConversions(): Conversions
    {
        return $this->conversions ?: $this->conversions = new Conversions;
    }

    public function setConversions(Conversions $conversions): Conversions
    {
        return $this->conversions = $conversions;
    }

    public function conversions(array $conversions = null)
    {
        if(is_null($conversions)) {
            return $this->getConversions();
        }

        foreach($conversions as $conversion) {
            if($conversion instanceof ConversionInterface) {
                $this->conversion($conversion);
            }
            else if(is_array($conversion)) {
                $this->conversion($conversion[0], ...(isset($conversion[1]) ? $conversion[1] : []));
            }
        }

        return $this;
    }

    public function conversion($conversion, ...$args): ConvertableInterface
    {
        if(!$this->conversions) {
            $this->conversions = new Conversions;
        }

        if(!$conversion instanceof ConversionInterface) {
            $conversion = $conversion::make(...$args);
        }

        if($conversion->doesApply($this->mime(), $this->extension())) {
            $this->conversions->push($conversion);
        }

        return $this;
    }

    public function convert(Model $parent, array $arguments = null): Model
    {
        $child = $this->model($arguments);
        $child->parent()->associate($parent);
        $child->save();

        return $child;
    }

}
