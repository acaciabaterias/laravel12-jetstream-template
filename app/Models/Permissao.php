<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permissao extends Model
{
    use HasFactory;

    protected $table = 'permissoes';

    protected $fillable = [
        'nome',
        'slug',
        'descricao',
    ];

    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'papel_permissao', 'permissao_id', 'papel', 'id', 'papel');
    }
}
