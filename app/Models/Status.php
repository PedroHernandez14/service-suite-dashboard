<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Status extends Model
{
    use HasFactory;

    // Protegemos solo el ID para permitir asignación masiva
    protected $guarded = ['id'];

    // --- RELACIONES ---

    /**
     * Un estado puede estar asignado a muchas compañías (locales)
     */
    public function companies(): HasMany
    {
        return $this->hasMany(Company::class);
    }

    /**
     * Un estado puede estar asignado a muchos tipos de servicio
     */
    public function serviceTypes(): HasMany
    {
        return $this->hasMany(ServiceType::class);
    }

    /**
     * Un estado puede estar asignado a muchas órdenes
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
