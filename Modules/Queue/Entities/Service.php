<?php

namespace Modules\Queue\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'counter_id',
    ];

    public function counter()
    {
        return $this->belongsTo(Counters::class, 'counter_id')->select('*');
    }

    protected static function newFactory()
    {
        return \Modules\Queue\Database\factories\ServiceFactory::new();
    }
}
