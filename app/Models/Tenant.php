<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'dni',
        'notes',
        'owner_id',
    ];

    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }

    public function properties()
    {
        return $this->belongsToMany(Property::class);
    }

    public function contracts()
    {
        return $this->belongsToMany(Contract::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }
}
