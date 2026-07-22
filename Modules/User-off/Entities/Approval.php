<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'login_id',
        'request_id',
        'transaction_id',
        'type',
        'status',
        'module',
        'memo',
        'status_trs_old',
        'status_trs_new',
    ];

    public function scopeFilterInput($query, $request)
    {
        //if keyword
        if ($request->keyword != "") {
            $keyword = $request->keyword;
            $query->where(function ($qry) use ($keyword) {
                $qry->where('approvals.memo', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('approvals.type', 'LIKE', '%' . $keyword . '%');
            });
        }
        //if register
        if ($request->register_start != "") {
            $query->whereBetween(DB::raw('DATE(approvals.created_at)'), [$request->register_start, $request->register_end]);
        }
        //if type
        if ($request->type != "") {
            $query->where('type', $request->type);
        }
        //if status
        if ($request->status != "") {
            $query->where('status', $request->status);
        }
        return $query;
    }

    public function scopeSetOrderBy($query, $request)
    {
        //if parent
        if (isset($request->order_by)) {
            if (!isset($request->order_by_dir)) {
                $order_by_dir = 'ASC';
            } else {
                $order_by_dir = $request->order_by_dir;
            }
            $query->orderBy('approvals.' . $request->order_by, $order_by_dir);
        }
        return $query;
    }
}
