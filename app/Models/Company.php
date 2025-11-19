<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Company extends Model
{
    public function contacts(): MorphMany
    {
        return $this->morphMany(Contact::class, 'contactable');
    }
}
