<?php

namespace Modules\Queue\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{
    use HasFactory;

    protected $fillable = [
        'register',
        'code',
        'number',
        'service_id',
        'status',
        'qr_code',
        'start_at',
        'end_at',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id')->select('*')->with('counter');
    }

    public function scopeFilterInput($query, $request)
    {
        //if keyword
        if ($request->keyword != "") {
            $keyword = $request->keyword;
            $query->where(function ($qry) use ($keyword) {
                $qry->where('queues.code', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('queues.number', 'LIKE', '%' . $keyword . '%');
            });
        }
        //if id
        if ($request->id >0) {
            $query->where('queues.id', $request->id);
        }
        //if service_id
        if ($request->service_id != "") {
            $query->where('queues.service_id', $request->service_id);
        }
        //if counter_id
        if ($request->counter_id != "") {
            $query->where('services.counter_id', $request->counter_id);
        }
        //if register
        if ($request->register != "") {
            $query->where('queues.register', $request->register);
        }
        //if status
        if ($request->status != "") {
            $query->where('queues.status', $request->status);
        }
        //if restart
        if ($request->restart != "") {
            $restartQry = "(SELECT MIN(queues.number) FROM queues where status='pending' AND register = '$request->register')";
            $query->whereRaw("queues.number = ".$restartQry);
        }
        //if next
        if ($request->next != "") {
            $query->where('queues.number', '>', $request->next);
        }
        //if back
        if ($request->back != "") {
            $query->where('queues.number', '<', $request->back);
        }
        return $query;
    }

    public function scopeSetOrderBy($query, $request)
    {
        //if parent
        if (isset($request->order_by)) {
            if(!isset($request->order_by_dir)){
                $order_by_dir = 'ASC';
            }else{
                $order_by_dir = $request->order_by_dir;
            }
            $query->orderBy('queues.'.$request->order_by, $order_by_dir);
        }
        return $query;
    }

    protected static function newFactory()
    {
        return \Modules\Queue\Database\factories\QueueFactory::new ();
    }
}
