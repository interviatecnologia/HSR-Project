<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class VicidailUser extends Model
{
    use HasApiTokens, HasFactory, Notifiable;
    // Define o nome da tabela
    protected $table = 'vicidial_users';

    // Define a chave primaria, se não for 'id'
    protected $primaryKey = 'user_id'; // ou o nome da sua chave prim�ria, se diferente

    // Se a tabela não usa timestamps (created_at e updated_at)
    public $timestamps = false; // Altere para true se sua tabela tiver timestamps

    // Campos que podem ser preenchidos
    protected $fillable = [
        'user',
        'pass',
        'full_name',
        'user_level',
        'user_group',
        'phone_login',
        'phone_pass',
        'campaign_detail',
        'view_reports',
        'last_login_date',
        'last_ip'
        ];
}