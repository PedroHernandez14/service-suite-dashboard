<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Contact extends Model
{
    use HasFactory;

    // --- ¡ESTO ES LO QUE FALTA! ---
    // Permite que todos los campos se guarden masivamente
    protected $guarded = ['id'];

    // O si prefieres ser estricto, usa $fillable:
    // protected $fillable = ['type', 'value', 'prefix', 'label', 'contactable_id', 'contactable_type'];

    public function contactable(): MorphTo
    {
        return $this->morphTo();
    }
}
