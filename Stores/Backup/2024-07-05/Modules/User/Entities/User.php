<?php

namespace Modules\User\Entities;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use DB;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'email',
        'password',
        'contact_id',
        'role_id',
        'status',
        'type',
        'register',
        'approved',
        'activated',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id')->select('*')->with('permissions');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id')->select('*')->with('contactAddress');
    }

    public function scopeFilterInput($query, $request)
    {
        //if keyword
        if ($request->keyword != "") {
            $keyword = $request->keyword;
            $query->where(function ($qry) use ($keyword) {
                $qry->where('users.code', 'LIKE', '%'.$keyword.'%')
                    ->orWhere('users.email', 'LIKE', '%'.$keyword.'%');
            });
        }
        //if register
        if ($request->register_start != "") {
            $query->whereBetween(DB::raw('DATE(users.register)'), [$request->register_start, $request->register_end]);
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
            if(!isset($request->order_by_dir)){
                $order_by_dir = 'ASC';
            }else{
                $order_by_dir = $request->order_by_dir;
            }
            $query->orderBy('users.'.$request->order_by, $order_by_dir);
        }
        return $query;
    }
}
