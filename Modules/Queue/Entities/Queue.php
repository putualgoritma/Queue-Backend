<?php

namespace Modules\Queue\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

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
        'name',
        'email',
        'phone',
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
                    ->orWhere('queues.name', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('queues.number', 'LIKE', '%' . $keyword . '%');
            });
        }
        //if id
        if ($request->id >0) {
            $query->where('queues.id', $request->id);
        }
        //if service_id
        if ($request->service_id_filter != "") {
            $query->where('queues.service_id', $request->service_id_filter);
        }
        //if counter_id
        if ($request->counter_id != "") {
            $query->where('services.counter_id', $request->counter_id);
        }
        //if register
        if ($request->register != "") {
            $query->where('queues.register', $request->register);
        }
        //if register start
        if ($request->register_start != "") {
            $query->whereBetween(DB::raw('DATE(queues.register)'), [$request->register_start, $request->register_end]);
        }
        //if status
        if ($request->status != "") {
            if($request->status =='pending'){
                $query->where(function ($qry) use ($request) {
                    $qry->where('queues.status', 'pending')
                    ->orWhere('queues.status', 'call');
                });
            }else{
                $query->where('queues.status', $request->status);
            }
            
        }
        //if restart
        if ($request->restart != "") {
            $restartQry = "(SELECT MIN(queues.number) FROM queues inner join services on services.id = queues.service_id inner join counters on counters.id = services.counter_id where queues.status='pending' AND queues.register = '$request->register' AND services.counter_id= '$request->counter_id')";
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
                $order_by_dir = 'DESC';
            }else{
                $order_by_dir = $request->order_by_dir;
            }
            $query->orderBy('queues.'.$request->order_by, $order_by_dir);
        }else{
            $order_by_dir = 'DESC';
            $order_by = 'number';
            $query->orderBy('queues.'.$order_by, $order_by_dir);
        }
        return $query;
    }

    protected static function newFactory()
    {
        return \Modules\Queue\Database\factories\QueueFactory::new ();
    }
}
