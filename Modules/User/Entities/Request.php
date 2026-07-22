<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'module',
        'memo',
        'login_id',
        'transaction_id',
        'status',
        'read'
    ];

    public function scopeFilterInput($query, $request)
    {
        //if keyword
        if ($request->keyword != "") {
            $keyword = $request->keyword;
            $query->where(function ($qry) use ($keyword) {
                $qry->where('requests.memo', 'LIKE', '%'.$keyword.'%')
                    ->orWhere('requests.type', 'LIKE', '%'.$keyword.'%');
            });
        }
        //if register
        if ($request->register_start != "") {
            $query->whereBetween(DB::raw('DATE(requests.created_at)'), [$request->register_start, $request->register_end]);
        }
        //if type
        if ($request->type != "") {
            $query->where('type', $request->type);
        }
        //if status
        if ($request->status != "") {
            $query->where('status', $request->status);
        }
        //if module
        if ($request->module != "") {
            $query->where('module', $request->module);
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
            $query->orderBy('requests.'.$request->order_by, $order_by_dir);
        }
        return $query;
    }
}
