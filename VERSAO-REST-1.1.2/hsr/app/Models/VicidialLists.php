<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class VicidialLists extends Model
{
    use HasApiTokens, HasFactory, Notifiable;
    // Define o nome da tabela
    protected $table = 'vicidial_lists';

    // Define a chave primaria, se não for 'id'
    protected $primaryKey = 'list_id'; // ou o nome da sua chave prim�ria, se diferente

    // Se a tabela não usa timestamps (created_at e updated_at)
    public $timestamps = false; // Altere para true se sua tabela tiver timestamps

    // Campos que podem ser preenchidos
    protected $fillable = [
        'list_id', 
        'list_name', 
        'campaign_id', 
        'active', 
        'list_description', 
        'list_changedate', 
        'list_lastcalldate', 
        'reset_time', 
        'agent_script_override', 
        'campaign_cid_override', 
        'am_message_exten_override', 
        'drop_inbound_group_override', 
        'xferconf_a_number', 
        'xferconf_b_number', 
        'xferconf_c_number', 
        'xferconf_d_number', 
        'xferconf_e_number', 
        'web_form_address', 
        'web_form_address_two', 
        'time_zone_setting', 
        'inventory_report', 
        'expiration_date', 
        'na_call_url', 
        'local_call_time', 
        'web_form_address_three', 
        'status_group_id', 
        'user_new_lead_limit', 
        'inbound_list_script_override', 
        'default_xfer_group', 
        'daily_reset_limit', 
        'resets_today', 
        'auto_active_list_rank', 
        'cache_count', 
        'cache_count_new', 
        'cache_count_dialable_new', 
        'cache_date', 
        'inbound_drop_voicemail', 
        'inbound_after_hours_voicemail', 
        'qc_scorecard_id', 
        'qc_statuses_id', 
        'qc_web_form_address', 
        'auto_alt_threshold', 
        'cid_group_id', 
        'dial_prefix', 
        'weekday_resets_container'
    ];

    protected $dates = [
        'entry_date',
        'modify_date',
        'last_local_call_time'
    ];
}