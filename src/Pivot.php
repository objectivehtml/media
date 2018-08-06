<?php

namespace Objectivehtml\Media;

use Illuminate\Database\Eloquent\Relations\Pivot as BasePivot;

class Pivot extends BasePivot
{

    /**
     * The name of the database table.
     * @var string
     */
    protected $table = 'mediables';

}
