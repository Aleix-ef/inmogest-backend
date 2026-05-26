<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'address',
        'price',
        'size',
        'rooms',
        'bathrooms',
        'status',
        'description'
    ];

    public function tenants()
    {
        return $this->belongsToMany(Tenant::class);
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    public function owners()
    {
        return $this->belongsToMany(Owner::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }
}
