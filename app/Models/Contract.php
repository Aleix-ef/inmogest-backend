<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'start_date',
        'end_date',
        'rent_price',
        'deposit',
        'status',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function tenants()
    {
        return $this->belongsToMany(Tenant::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }
}
