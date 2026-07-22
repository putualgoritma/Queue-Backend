<?php

namespace Modules\Queue\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class Guestbook extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'card_id',
        'departement',
        'email',
        'phone',
    ];

    public function scopeFilterInput($query, $request)
    {
        //if keyword
        if ($request->keyword != "") {
            $keyword = $request->keyword;
            $query->where(function ($qry) use ($keyword) {
                $qry->where('guestbooks.card_id', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('guestbooks.name', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('guestbooks.departement', 'LIKE', '%' . $keyword . '%');
            });
        }
        //if register start
        if ($request->register_start != "") {
            $query->whereBetween(DB::raw('DATE(guestbooks.created_at)'), [$request->register_start, $request->register_end]);
        }
        //if id
        if ($request->id >0) {
            $query->where('guestbooks.id', $request->id);
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
            $query->orderBy('guestbooks.'.$request->order_by, $order_by_dir);
        }else{
            $order_by_dir = 'DESC';
            $order_by = 'id';
            $query->orderBy('guestbooks.'.$order_by, $order_by_dir);
        }
        return $query;
    }

    protected static function newFactory()
    {
        return \Modules\Queue\Database\factories\QueueFactory::new ();
    }
}
