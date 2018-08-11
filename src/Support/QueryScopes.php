<?php

namespace Objectivehtml\Media\Support;

use Objectivehtml\Media\MediaService;

trait QueryScopes {

    /**
     * Add a query scope for for audio tags.
     *
     * @param Illuminate\Database\Eloquent\Builder $query
     * @return void
     */
    public function scopeAudio($query)
    {
        $query->tag('audio');
    }

    /**
     * Add a query scope for the caption attribute
     *
     * @param Illuminate\Database\Eloquent\Builder $query
     * @param mixed $value
     * @return void
     */
    public function scopeCaption($query, $value)
    {
        $query->whereCaption($value);
    }

    /**
     * Add a query scope for the context attribute
     *
     * @param Illuminate\Database\Eloquent\Builder $query
     * @param mixed $value
     * @return void
     */
    public function scopeContext($query, $value)
    {
        $query->whereContext($value);
    }

    /**
     * Add a query scope for the conversions attribute
     *
     * @param Illuminate\Database\Eloquent\Builder $query
     * @param mixed $conversions
     * @return void
     */
    public function scopeConversion($query, ...$conversions)
    {
        $this->scopeConversions($query, $conversions);
    }

    /**
     * Add a query scope for the conversions attribute
     *
     * @param Illuminate\Database\Eloquent\Builder $query
     * @param array $conversions
     * @return void
     */
    public function scopeConversions($query, array $conversions)
    {
        $query->where(function($q) use ($conversions) {
            foreach($conversions as $conversion) {
                $q->orWhereJsonContains('conversions', $conversion);
            }
        });
    }

    /**
     * Add a query scope for the disk attribute
     *
     * @param Illuminate\Database\Eloquent\Builder $query
     * @param mixed $value
     * @return void
     */
    public function scopeDisk($query, $value)
    {
        $query->whereDisk($value);
    }

    /**
     * Add a query scope for the extension attribute
     *
     * @param Illuminate\Database\Eloquent\Builder $query
     * @param mixed $value
     * @return void
     */
    public function scopeExtension($query, $value)
    {
        $query->whereExtension($value);
    }

    /**
     * Add a query scope for the filename attribute
     *
     * @param Illuminate\Database\Eloquent\Builder $query
     * @param mixed $value
     * @return void
     */
    public function scopeFilename($query, $value)
    {
        $query->whereFilename($value);
    }

    /**
     * Add a query scope for the filters attribute
     *
     * @param Illuminate\Database\Eloquent\Builder $query
     * @param mixed $filters
     * @return void
     */
    public function scopeFilter($query, ...$filters)
    {
        $this->scopeFilters($query, $filters);
    }

    /**
     * Add a query scope for the filters attribute
     *
     * @param Illuminate\Database\Eloquent\Builder $query
     * @param array $filters
     * @return void
     */
    public function scopeFilters($query, array $filters)
    {
        $query->where(function($q) use ($filters) {
            foreach($filters as $filter) {
                $q->orWhereJsonContains('filter', $filter);
            }
        });
    }

    /**
     * Add a query scope for for image tags.
     *
     * @param Illuminate\Database\Eloquent\Builder $query
     * @return void
     */
    public function scopeImages($query)
    {
        $query->tag('image');
    }

    /**
     * Add a query scope for the meta attribute
     *
     * @param Illuminate\Database\Eloquent\Builder $query
     * @param mixed $meta
     * @return void
     */
    public function scopeMeta($query, $meta)
    {
        $query->whereRaw('JSON_CONTAINS(`meta`, '.json_encode($meta).')');
    }

    /**
     * Add a query scope for the mime attribute
     *
     * @param Illuminate\Database\Eloquent\Builder $query
     * @param mixed $value
     * @return void
     */
    public function scopeMime($query, $value)
    {
        $query->whereMime($value);
    }

    /**
     * Add a query scope for the original context
     *
     * @param Illuminate\Database\Eloquent\Builder $query
     * @return void
     */
    public function scopeOriginal($query)
    {
        $query->context('original');
    }

    /**
     * Add a query scope for the orig_filename attribute
     *
     * @param Illuminate\Database\Eloquent\Builder $query
     * @param mixed $value
     * @return void
     */
    public function scopeOrigFilename($query, $value)
    {
        $query->whereOrigFilename($value);
    }

    /**
     * Add a query scope for the orig_filename attribute
     *
     * @param Illuminate\Database\Eloquent\Builder $query
     * @return void
     */
    public function scopeParents($query)
    {
        $query->whereNull('parent_id');
    }

    /**
     * Add a query scope for the ready attribute
     *
     * @param Illuminate\Database\Eloquent\Builder $query
     * @param bool $value
     * @return void
     */
    public function scopeReady($query, bool $value = true)
    {
        $query->whereReady($value);
    }

    /**
     * Add a query scope for the size attribute
     *
     * @param Illuminate\Database\Eloquent\Builder $query
     * @param mixed $value
     * @return void
     */
    public function scopeSize($query, $value)
    {
        $query->whereSize($value);
    }

    /**
     * Add a query scope for the tags attribute
     *
     * @param Illuminate\Database\Eloquent\Builder $query
     * @param mixed ...$tags
     * @return void
     */
    public function scopeTag($query, ...$tags)
    {
        $this->scopeTags($query, $tags);
    }

    /**
     * Add a query scope for the tags attribute
     *
     * @param Illuminate\Database\Eloquent\Builder $query
     * @param array $tags
     * @return void
     */
    public function scopeTags($query, array $tags)
    {
        $query->where(function($q) use ($tags) {
            foreach($tags as $tag) {
                $q->orWhereJsonContains('tags', $tag);
            }
        });
    }

    /**
     * Add a query scope for the temporary context attribute
     *
     * @param Illuminate\Database\Eloquent\Builder $query
     * @param mixed $value
     * @return void
     */
    public function scopeTemporary($query)
    {
        $query->context(app(MediaService::class)->config('temp.context', '__temp__'));
    }


    /**
     * Add a query scope for the title attribute
     *
     * @param Illuminate\Database\Eloquent\Builder $query
     * @param mixed $value
     * @return void
     */
    public function scopeTitle($query, $value)
    {
        $query->whereTitle($value);
    }

    /**
     * Add a query scope for for video tags.
     *
     * @param Illuminate\Database\Eloquent\Builder $query
     * @return void
     */
    public function scopeVideo($query)
    {
        $query->tag('video');
    }

}
