<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhiteLabelConfig extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $fillable = [
        'logo_url', 'favicon_url', 'cor_primaria',
        'cor_secundaria', 'cor_fundo', 'titulo_login', 'custom_css',
        'custom_js', 'template_nome', 'mostrar_marca_plataforma',
    ];
}
