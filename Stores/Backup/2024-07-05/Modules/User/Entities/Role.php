<?php

namespace Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use DB;

class Role extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
    ];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permission', 'role_id', 'permission_id');
    }

    public function scopeFilterInput($query, $request)
    {
        //if keyword
        if ($request->keyword != "") {
            $keyword = $request->keyword;
            $query->where(function ($qry) use ($keyword) {
                $qry->where('roles.title', 'LIKE', '%'.$keyword.'%');
            });
        }
        //if register
        if ($request->register_start != "") {
            $query->whereBetween(DB::raw('DATE(roles.created_at)'), [$request->register_start, $request->register_end]);
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
            $query->orderBy('roles.'.$request->order_by, $order_by_dir);
        }
        return $query;
    }
}
