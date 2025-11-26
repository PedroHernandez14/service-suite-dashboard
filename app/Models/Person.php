<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Person extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // Castear la fecha es vital para el DatePicker
    protected $casts = [
        'birth_date' => 'date',
    ];

    protected function dniAndFullName(): Attribute
    {
        $identificationType = $this->identificationType->acronym;
        return Attribute::make(
            get: fn ($vale, $attributes) => $identificationType.$attributes['dni'].' - '.$attributes['first_name'].' '.$attributes['last_name'],
        );
    }

    // Relación inversa (opcional, pero útil): Una persona puede ser un usuario
    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }
    public function contacts(): MorphMany
    {
        return $this->morphMany(Contact::class, 'contactable');
    }

    public function identificationType(): BelongsTo
    {
        return $this->belongsTo(IdentificationType::class);
    }

}
