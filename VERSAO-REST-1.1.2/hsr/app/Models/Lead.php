<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Lead extends Model

    {            
            
            use HasApiTokens, HasFactory, Notifiable;
            // Define o nome da tabela
            protected $table = 'vicidial_list';
        
            // Define a chave primaria, se não for 'id'
            protected $primaryKey = 'lead_id'; // ou o nome da sua chave primária, se diferente
        
            // Se a tabela não usa timestamps (created_at e updated_at)
            public $timestamps = false; // Altere para true se sua tabela tiver timestamps
        
            // Campos que podem ser preenchidos
        

    protected $fillable = [
        'phone_code', 'phone_number', 'list_id', 'status', 'user', 'vendor_lead_code',
        'source_id', 'title', 'first_name', 'middle_initial', 'last_name', 'address1',
        'address2', 'address3', 'city', 'state', 'province', 'postal_code', 'country_code',
        'gender', 'date_of_birth', 'alt_phone', 'email', 'security_phrase', 'comments',
        'called_since_last_reset', 'entry_date', 'last_local_call_time', 'rank', 'owner'
    ];
}
