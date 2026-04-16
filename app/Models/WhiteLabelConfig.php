<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WhiteLabelConfig extends Model
{
    use HasFactory;
    protected $fillable = [
        'filial_id', 'logo_url', 'favicon_url', 'cor_primaria',
        'cor_secundaria', 'cor_fundo', 'titulo_login', 'custom_css',
        'custom_js', 'template_nome', 'mostrar_marca_plataforma'
    ];
    
    public function filial()
    {
        return $this->belongsTo(Filial::class);
    }
}
