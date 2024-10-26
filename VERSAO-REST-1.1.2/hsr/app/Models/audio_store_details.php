<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class audio_store_details extends Model
{
    use HasApiTokens, HasFactory, Notifiable;
    // Define o nome da tabela
    protected $table = 'audio_store_details';

    // Define a chave primária, se não for 'id'
    protected $primaryKey = ''; // ou o nome da sua chave primária, se diferente
    protected $fillable = [];
}
