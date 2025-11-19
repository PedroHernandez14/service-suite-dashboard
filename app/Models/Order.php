<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory; // HasUuids permite que el ID se genere automáticamente

    // Protegemos solo el ID, el resto se puede asignar masivamente
    protected $guarded = ['id'];

    // Convierte el campo JSON 'adjustments' a array automáticamente
    protected $casts = [
        'adjustments' => 'array',
        'order_date' => 'datetime',
    ];

    // --- RELACIONES (Esto es lo que te falta) ---

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }
}
