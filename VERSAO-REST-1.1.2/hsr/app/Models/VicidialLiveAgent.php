<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class VicidialLiveAgent extends Model
{
    use HasApiTokens, HasFactory, Notifiable;
    // Define o nome da tabela
    protected $table = 'vicidial_live_agents';

    // Define a chave primaria, se não for 'id'
    protected $primaryKey = 'user'; // ou o nome da sua chave prim�ria, se diferente

    // Se a tabela não usa timestamps (created_at e updated_at)
    public $timestamps = false; // Altere para true se sua tabela tiver timestamps

    // Campos que podem ser preenchidos
    protected $fillable = [
        
        'live_agent_id', 
        'user', 
        'server_ip', 
        'conf_exten', 
        'extension', 
        'status', 
        'lead_id', 
        'campaign_id', 
        'uniqueid', 
        'callerid', 
        'channel', 
        'random_id', 
        'last_call_time', 
        'last_update_time', 
        'last_call_finish', 
        'closer_campaigns', 
        'call_server_ip', 
        'user_level', 
        'comments', 
        'campaign_weight', 
        'calls_today', 
        'external_hangup', 
        'external_status', 
        'external_pause', 
        'external_dial', 
        'external_ingroups', 
        'external_blended', 
        'external_igb_set_user', 
        'external_update_fields', 
        'external_update_fields_data', 
        'external_timer_action', 
        'external_timer_action_message', 
        'external_timer_action_seconds', 
        'agent_log_id', 'last_state_change', 
        'agent_territories', 
        'outbound_autodial', 
        'manager_ingroup_set', 
        'ra_user', 
        'ra_extension', 
        'external_dtmf', 
        'external_transferconf', 
        'external_park', 
        'external_timer_action_destination', 
        'on_hook_agent', 
        'on_hook_ring_time', 
        'ring_callerid', 
        'last_inbound_call_time', 
        'last_inbound_call_finish', 
        'campaign_grade', 
        'external_recording', 
        'external_pause_code', 
        'pause_code', 
        'preview_lead_id', 
        'external_lead_id', 
        'last_inbound_call_time_filtered', 
        'last_inbound_call_finish_filtered', 
        'dial_campaign_id' 
        ];
}