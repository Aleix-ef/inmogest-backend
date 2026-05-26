<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'file_path',
        'type',
        'property_id',
        'tenant_id',
        'contract_id',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }


    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }
}
