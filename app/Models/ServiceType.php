<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceType extends Model
{
    use HasFactory;

    // Tabla en la BBDD (por si acaso Laravel no la pluraliza bien, aunque debería)
    protected $table = 'service_types';

    protected $guarded = ['id'];

    // --- RELACIONES ---
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'service_type_id');
    }
}
