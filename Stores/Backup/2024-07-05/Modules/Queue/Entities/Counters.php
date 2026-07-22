<?php

namespace Modules\Queue\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Counters extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code'
    ];

    protected static function newFactory()
    {
        return \Modules\Queue\Database\factories\CountersFactory::new();
    }
}
