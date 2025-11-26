<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Company extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function rifAndName() : Attribute
    {
        $identificationType = $this->identificationType->acronym;
        return Attribute::make(
            get: fn ($vale, $attributes) => $identificationType.$attributes['identification_number'].' - '.$attributes['name'],
        );
    }
    public function contacts(): MorphMany
    {
        return $this->morphMany(Contact::class, 'contactable');
    }

    public function identificationType(): BelongsTo
    {
        return $this->belongsTo(IdentificationType::class);
    }
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

}
