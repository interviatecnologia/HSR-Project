<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Recording extends Model
{
    use HasApiTokens, HasFactory, Notifiable;
    // Define o nome da tabela
    protected $table = 'recording_log';

    // Define a chave primaria, se não for 'id'
    protected $primaryKey = 'recording_id'; // ou o nome da sua chave primária, se diferente

    // Se a tabela não usa timestamps (created_at e updated_at)
    public $timestamps = false; // Altere para true se sua tabela tiver timestamps

    // Campos que podem ser preenchidos
    protected $fillable = [
        'recording_id', 
        'channel', 
        'server_ip', 
        'extension', 
        'start_time', 
        'start_epoch', 
        'end_time', 
        'end_epoch', 
        'length_in_sec', 
        'length_in_min', 
        'filename', 
        'location', 
        'lead_id', 
        'user', 
        'vicidial_id'
        ];
}