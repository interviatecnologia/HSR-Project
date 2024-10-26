<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class VicidialList extends Model
{
    use HasApiTokens, HasFactory, Notifiable;
    // Define o nome da tabela
    protected $table = 'vicidial_list';

    // Define a chave primaria, se não for 'id'
    protected $primaryKey = 'lead_id'; // ou o nome da sua chave prim�ria, se diferente

    // Se a tabela não usa timestamps (created_at e updated_at)
    public $timestamps = false; // Altere para true se sua tabela tiver timestamps

    // Campos que podem ser preenchidos
    protected $fillable = [
        'phone_number',
        'status',
        'phone_code',
        'list_id',
        'entry_date',
        'modify_date',
        'called_since_last_reset',
        'source_id',
        'vendor_lead_code',
        'gmt_offset_now',
        'title',
        'campaign_id',
        'last_local_call_time'
    ];

    protected $dates = [
        'entry_date',
        'modify_date',
        'last_local_call_time'
    ];
}