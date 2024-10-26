<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Phone extends Model
{
    use HasApiTokens, HasFactory, Notifiable;
    // Define o nome da tabela
    protected $table = 'phones';

    // Define a chave primaria, se não for 'id'
    protected $primaryKey = 'extension'; // ou o nome da sua chave primária, se diferente

    // Se a tabela não usa timestamps (created_at e updated_at)
    public $timestamps = false; // Altere para true se sua tabela tiver timestamps

    // Campos que podem ser preenchidos
    protected $fillable = [
        'extension',
        'dialplan_number',
        'voicemail_id',
        'phone_ip',
        'computer_ip',
        'server_ip',
        'login',
        'pass',
        'status',
        'active',
        'phone_type',
        'fullname',
        'company'
        ];
}