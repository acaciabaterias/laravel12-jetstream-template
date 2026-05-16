<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContatoBlacklist extends Model
{
    protected $table = 'contato_blacklist';

    protected $fillable = [
        'numero_tel',
        'motivo',
    ];
}
