<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class VicidialAgentLog extends Model
{
    use HasApiTokens, HasFactory, Notifiable;
    // Define o nome da tabela
    protected $table = 'vicidial_agent_log';

    // Define a chave primaria, se não for 'id'
   // protected $primaryKey = 'id'; // ou o nome da sua chave prim�ria, se diferente

    // Se a tabela não usa timestamps (created_at e updated_at)
    public $timestamps = false; // Altere para true se sua tabela tiver timestamps

    // Campos que podem ser preenchidos
    protected $fillable = [
        'user',
        'lead_id',
        'campaign_id',
        'event_time',
        'status',
        'comments',
        'sub_status',
        'pause_epoch',
        'wait_epoch',
        'talk_epoch',
        'dispo_epoch',
        'uniqueid',
        'user_group'
    ];

    protected $dates = [
        'event_time'
    ];
}