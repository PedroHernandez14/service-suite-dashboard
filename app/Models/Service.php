<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // --- RELACIONES ---

    public function serviceType(): BelongsTo
    {
        // Apunta a la tabla 'service_types'
        return $this->belongsTo(ServiceType::class);
    }
}
