<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Owner extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'dni',
        'notes',
    ];

    public function properties()
    {
        return $this->belongsToMany(Property::class);
    }

    public function tenants()
    {
        return $this->hasMany(Tenant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
