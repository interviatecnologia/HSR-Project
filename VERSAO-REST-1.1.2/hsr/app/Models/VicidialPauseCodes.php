<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class VicidialPauseCodes extends Model
{
    use HasApiTokens, HasFactory, Notifiable;
    // Define o nome da tabela
    protected $table = 'vicidial_pause_codes';

    // Define a chave primaria, se não for 'id'
    protected $primaryKey = 'extension'; // ou o nome da sua chave primária, se diferente

    // Se a tabela não usa timestamps (created_at e updated_at)
    public $timestamps = false; // Altere para true se sua tabela tiver timestamps

    // Campos que podem ser preenchidos
    protected $fillable = [
        'pause_code', 
        'pause_code_name', 
        'billable', 
        'campaign_id', 
        'time_limit', 
        'require_mgr_approval'
        ];
}