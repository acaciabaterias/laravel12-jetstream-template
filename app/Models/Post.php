<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use \App\Traits\HasFilial;
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = ['title', 'filial_id'];
}
