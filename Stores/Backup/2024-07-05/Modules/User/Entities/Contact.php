<?php

namespace Modules\User\Entities;

use DB;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'card_id',
        'birthday',
        'sex',
        'email',
        'phone',
        'phone2',
        'description',
    ];

    public function contactAddress()
    {
        return $this->hasMany(ContactAddress::class, 'contact_id')->select('*')->with('subdistrict');
    }

    public function scopeFilterInput($query, $request)
    {
        //if id
        if (isset($request->id)) {
            $query->where('contacts.id', $request->id);
        }
        //if keyword
        if ($request->keyword != "") {
            $keyword = $request->keyword;
            $query->where(function ($qry) use ($keyword) {
                $qry->where('contacts.name', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('contacts.email', 'LIKE', '%' . $keyword . '%');
            });
        }
        //if register
        if ($request->register_start != "") {
            $query->whereBetween(DB::raw('DATE(contacts.created_at)'), [$request->register_start, $request->register_end]);
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
            $query->orderBy('contacts.' . $request->order_by, $order_by_dir);
        }
        return $query;
    }

    public function scopeFilterDelete($query, $request)
    {
        //if id
        if ($request->id != "") {
            $query->where('contacts.id', $request->id);
        }
        return $query;
    }
}
