ADDLEAD - LISTA
#######################################################JSON COM ARRAYFIM####################################################################
###ADDLEAD - LISTA

$json = json_decode(file_get_contents('php://input'), true);

$source = $json['source'];
$user = $json['user'];
$pass = $json['pass'];
$function = $json['function'];
$campaign_id = $json['campaign_id'];
$list_id = $json['list_id'];

if (isset($json['leads'])) {
    $leads = $json['leads'];
    foreach ($leads as $lead) {
        $first_name = $lead['first_name'];
        $last_name = $lead['last_name'];
        $phone_number = $lead['phone_number'];
        $phone_code = $lead['phone_code'];

        // Verificar se o número de telefone tem o comprimento correto
        if (strlen($phone_number) < 8 || strlen($phone_number) > 15) {
            // Retorne um erro ou uma mensagem de erro
            echo json_encode(array('error' => 'INVALID PHONE NUMBER LENGTH'));
            exit;
        }

        // Processar o lead aqui
        // ...
    }
} else {
    // Código anterior para lidar com um único lead
    $first_name = $json['first_name'];
    $last_name = $json['last_name'];
    $phone_number = $json['phone_number'];
    $phone_code = $json['phone_code'];

    // Adicionar lead individualmente
    $add_lead_response = add_lead($first_name, $last_name, $phone_number, $phone_code, $campaign_id, $list_id);
    if ($add_lead_response) {
        echo json_encode(array('success' => 'LEAD HAS BEEN ADDED - ' . $phone_number));
    } else {
        echo json_encode(array('error' => 'FAILED TO ADD LEAD'));
    }
}



#######################################################FIM####################################################################
