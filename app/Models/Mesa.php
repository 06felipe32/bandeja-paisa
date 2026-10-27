<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mesa extends Model
{
    protected $fillable = ['numero', 'capacidad', 'ubicacion', 'descripcion', 'habilitada'];

    protected function casts(): array
    {
        return ['numero' => 'integer', 'capacidad' => 'integer', 'habilitada' => 'boolean'];
    }
}
