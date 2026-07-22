<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;

class Regency extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'province_id',
    ];

    public function province( )
    {
        return $this->belongsTo(Province::class, 'province_id')->select('*');
    }
}
