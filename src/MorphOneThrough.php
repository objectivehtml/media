<?php

namespace Objectivehtml\Media;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class MorphOneThrough extends MorphToMany
{
    /**
     * Get the results of the relationship.
     *
     * @return mixed
     */
    public function getResults()
    {
        // Always return the first item of the results instead of the entire collection.
        return $this->get()->first();
    }

    /**
     * Execute the query and get the first result.
     *
     * @param  array   $columns
     * @return mixed
     */
    public function first($columns = ['*'])
    {
        // Return the first item found, override the default return of a collection.
        // If no item is found, null will be returned.
        return $this->take(1)->get($columns)->first();
    }

    /**
     * Find multiple related models by their primary keys.
     *
     * @param  mixed  $ids
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findMany($ids, $columns = ['*'])
    {
        // In this relationship we need to ensure null is returned if nothing
        // is found. In this case, null is being returned instead of an new
        // empty collection instance.
        return empty($ids) ? null : $this->whereIn(
            $this->getRelated()->getQualifiedKeyName(), $ids
        )->get($columns);
    }

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param  array   $models
     * @param  \Illuminate\Database\Eloquent\Collection  $results
     * @param  string  $relation
     * @return array
     */
    public function match(array $models, $results, $relation)
    {
        $dictionary = $this->buildDictionary((new Collection($results))->filter());

        // Once we have an array dictionary of child objects we can easily match the
        // children back to their parent using the dictionary and the keys on the
        // the parent models. Then we will return the hydrated models back out.
        foreach ($models as $model) {

            // This is the key difference, if the model is found then set the
            // relationship, otherwise return null. This will ensure either
            // the matching object is returned, or null instead of an empty array.
            if (isset($dictionary[$key = $model->{$this->parentKey}])) {
                $model->setRelation($relation, reset($dictionary[$key]));
            }
            else {
                $model->setRelation($relation, null);
            }
        }

        return $models;
    }
}
