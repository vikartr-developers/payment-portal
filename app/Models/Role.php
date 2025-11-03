<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Role extends SpatieRole
{
    use HasFactory, SoftDeletes;

    protected $table = 'roles';

    protected $fillable = [
        'name',
        'guard_name',
        'encryption_id',
        'created_by',
        'updated_by',
        'display_name',
        'deleted_by',
        'status',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Generate UUID only if not set
            if (empty($model->encryption_id)) {
                $model->encryption_id = (string) Str::uuid();
            }

            if (Auth::check()) {
                $model->created_by = Auth::id();
                $model->updated_by = Auth::id();
            }
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });

        static::deleting(function ($model) {
            if (Auth::check()) {
                $model->deleted_by = Auth::id();
                $model->save(); // save manually to persist deleted_by before soft delete
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'encryption_id';
    }
    // public function users()
    // {
    //     return $this->belongsToMany(User::class);
    // }
}
