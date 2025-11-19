<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IdentificationType extends Model
{
    protected $guarded = ['id'];

    public function people(): HasMany
    {
        return $this->hasMany(Person::class);
    }
}
