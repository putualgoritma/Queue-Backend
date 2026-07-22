<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;

class ContactAddress extends Model
{
    protected $table = 'contact_address';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'contact_id',
        'location',
        'subdistrict_id',
    ];

    public function subdistrict( )
    {
        return $this->belongsTo(Subdistrict::class, 'subdistrict_id')->select('*')->with('regency');
    }
}
