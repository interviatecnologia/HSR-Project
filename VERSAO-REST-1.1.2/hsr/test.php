<?php

require 'vendor/autoload.php'; // Adicione esta linha

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;

$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'database'  => 'asterisk',
    'username'  => 'laravel',
    'password'  => 'wWO_l8ALyaD0Bkq]',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

try {
    $capsule->connection()->getPdo();
    echo "Conexão com o banco de dados estabelecida com sucesso!";
} catch (\Exception $e) {
    echo "Erro ao estabelecer conexão com o banco de dados: " . $e->getMessage();
}
