<?php

namespace App;

use Eloquent;

class SearchYou extends Eloquent
{
    protected $table = 'search_you';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'growth_from', 'growth_to', 'age_from', 'age_to', 'date_from', 'date_to', 'comment', 'person_sex',
        'polygon_coords_serialized', 'visible', 'ts', 'oid'
    ];

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [''];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        
    ];
}
