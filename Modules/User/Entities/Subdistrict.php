<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;

class Subdistrict extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'regency_id',
    ];

    public function regency( )
    {
        return $this->belongsTo(Regency::class, 'regency_id')->select('*')->with('province');
    }
}
