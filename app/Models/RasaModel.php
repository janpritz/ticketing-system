<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RasaModel extends Model
{
    protected $fillable = ['model_name', 'size', 'is_current'];
}
