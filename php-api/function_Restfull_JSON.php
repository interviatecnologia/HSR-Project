
<?php

######################ADJUST INTERVIA - RESTFULL+JSON##########################
################################################################################
### BEGIN - version - show version and date information for the API
################################################################################
if ($function == 'version') {
    $response = array(
        'result' => _QXZ("SUCCESS"),
        'version' => $version,
        'build' => $build,
        'date' => $NOW_TIME,
        'epoch' => $StarTtime
    );
    header('Content-Type: application/json');
    header('HTTP/1.1 200 OK');
    echo json_encode($response);
    api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, $response['result'], '', $source, $response);
    exit;
}
################################################################################
### END - version
################################################################################

######################ADJUST INTERVIA - RESTFULL+JSON##########################
##############################################################################
### BEGIN - external_status - set the dispo code or status for a call and move on
################################################################################


if ($function == 'external_status') {
    // VALIDAR DADOS
    // Verifica se os dados de entrada são válidos
    if ((strlen($value) < 1) || ((strlen($agent_user) < 1) && (strlen($alt_user) < 2))) {
        // RESPOSTA DE ERRO
        // Retorna uma resposta de erro em formato JSON
        $response = array(
            'result' => _QXZ("ERROR"),
            'result_reason' => _QXZ("external_status not valid"),
            'details' => array(
                'value' => $value,
                'agent_user' => $agent_user,
                'alt_user' => $alt_user
            )
        );
        header('Content-Type: application/json');
        header('HTTP/1.1 400 Bad Request');
        echo json_encode($response);
        // REGISTRO DE LOG
        // Registra o erro no log
        api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, $response['result'], $response['result_reason'], $source, $data);
    } else {
        // VERIFICAR PERMISSÃO
        // Verifica se o usuário tem permissão para executar a função
        if ((!preg_match("/ $function /", $VUapi_allowed_functions)) && (!preg_match("/ALL_FUNCTIONS/", $VUapi_allowed_functions))) {
            // RESPOSTA DE ERRO
            // Retorna uma resposta de erro em formato JSON
            $response = array(
                'result' => _QXZ("ERROR"),
                'result_reason' => _QXZ("auth USER DOES NOT HAVE PERMISSION TO USE THIS FUNCTION"),
                'details' => array(
                    'user' => $user,
                    'function' => $function,
                    'VUuser_group' => $VUuser_group
                )
            );
            header('Content-Type: application/json');
            header('HTTP/1.1 403 Forbidden');
            echo json_encode($response);
            // REGISTRO DE LOG
            // Registra o erro no log
            api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, $response['result'], $response['result_reason'], $source, $data);
            exit;
        }
        // VERIFICAR USUÁRIO
        // Verifica se o usuário existe
        if (strlen($alt_user) > 1) {
            $stmt = "SELECT count(*) FROM vicidial_users WHERE custom_three='$alt_user';";
            if ($DB) {echo "$stmt\n";}
            $rslt = mysql_to_mysqli($stmt, $link);
            $row = mysqli_fetch_row($rslt);
            if ($row[0] > 0) {
                $stmt = "SELECT user FROM vicidial_users WHERE custom_three='$alt_user' ORDER BY user;";
                if ($DB) {echo "$stmt\n";}
                $rslt = mysql_to_mysqli($stmt, $link);
                $row = mysqli_fetch_row($rslt);
                $agent_user = $row[0];
            } else {
                // RESPOSTA DE ERRO
                // Retorna uma resposta de erro em formato JSON
                $response = array(
                    'result' => _QXZ("ERROR"),
                    'result_reason' => _QXZ("no user found"),
                    'details' => array(
                        'alt_user' => $alt_user
                    )
                );
                header('Content-Type: application/json');
                header('HTTP/1.1 404 Not Found');
                echo json_encode($response);
                // REGISTRO DE LOG
                // Registra o erro no log
                api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, $response['result'], $response['result_reason'], $source, $data);
            }
        }
        // VERIFICAR AGENTE
        // Verifica se o agente está logado
        $stmt = "SELECT count(*) FROM vicidial_live_agents WHERE user='$agent_user';";
        if ($DB) {echo "$stmt\n";}
        $rslt = mysql_to_mysqli($stmt, $link);
        $row = mysqli_fetch_row($rslt);
        if ($row[0] > 0) {
            // ATUALIZAR AGENTE
            // Atualiza o agente com o valor de external_status
            $stmt = "UPDATE vicidial_live_agents SET external_status='$value' WHERE user='$agent_user';";
            if ($format == 'debug') {echo "\n<!-- $stmt -->";}
            $rslt = mysql_to_mysqli($stmt, $link);
            // RESPOSTA DE SUCESSO
            // Retorna uma resposta de sucesso em formato JSON
            $response = array(
                'result' => _QXZ("SUCCESS"),
                'result_reason' => _QXZ("external_status function set"),
                'details' => array(
                    'value' => $value,
                    'agent_user' => $agent_user
                )
            );
            header('Content-Type: application/json');
            header('HTTP/1.1 200 OK');
            echo json_encode($response);
            // REGISTRO DE LOG
            // Registra o sucesso no log
            api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, $response['result'], $response['result_reason'], $source, $data);
        } else {
            // RESPOSTA DE ERRO
            // Retorna uma resposta de erro em formato JSON
            $response = array(
                'result' => _QXZ("ERROR"),
                'result_reason' => _QXZ("agent_user is not logged in"),
                'details' => array(
                    'agent_user' => $agent_user
                )
            );
            header('Content-Type: application/json');
            header('HTTP/1.1 400 Bad Request');
            echo json_encode($response);
            // REGISTRO DE LOG
            // Registra o erro no log
            api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, $response['result'], $response['result_reason'], $source, $data);
        }
    }
}

##############################################################################
### FINAL - external_status 
################################################################################


######################ADJUST INTERVIA - RESTFULL+JSON##########################
###############################################################################
### BEGIN - call_agent - send a call to connect the agent to their session
################################################################################

if ($function == 'call_agent') {
    // VALIDAR DADOS
    if (($value != 'CALL') || ((strlen($agent_user) < 1) && (strlen($alt_user) < 2))) {
        // RESPOSTA DE ERRO
        $response = array(
            'result' => _QXZ("ERROR"),
            'result_reason' => _QXZ("call_agent not valid"),
            'details' => array(
                'value' => $value,
                'agent_user' => $agent_user
            )
        );
        header('Content-Type: application/json');
        header('HTTP/1.1 400 Bad Request');
        echo json_encode($response);
        api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, $response['result'], $response['result_reason'], $source, $data);
    } else {
        // VERIFICAR PERMISSÃO
        if ((!preg_match("/ $function /", $VUapi_allowed_functions)) && (!preg_match("/ALL_FUNCTIONS/", $VUapi_allowed_functions))) {
            // RESPOSTA DE ERRO
            $response = array(
                'result' => _QXZ("ERROR"),
                'result_reason' => _QXZ("auth USER DOES NOT HAVE PERMISSION TO USE THIS FUNCTION"),
                'details' => array(
                    'user' => $user,
                    'function' => $function,
                    'VUuser_group' => $VUuser_group
                )
            );
            header('Content-Type: application/json');
            header('HTTP/1.1 403 Forbidden');
            echo json_encode($response);
            api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, $response['result'], $response['result_reason'], $source, $data);
            exit;
        }
        // VERIFICAR USUÁRIO
        if (strlen($alt_user) > 1) {
            $stmt = "select count(*) from vicidial_users where custom_three='$alt_user';";
            if ($DB) {echo "$stmt\n";}
            $rslt = mysql_to_mysqli($stmt, $link);
            $row = mysqli_fetch_row($rslt);
            if ($row[0] > 0) {
                $stmt = "select user from vicidial_users where custom_three='$alt_user' order by user;";
                if ($DB) {echo "$stmt\n";}
                $rslt = mysql_to_mysqli($stmt, $link);
                $row = mysqli_fetch_row($rslt);
                $agent_user = $row[0];
            } else {
                // RESPOSTA DE ERRO
                $response = array(
                    'result' => _QXZ("ERROR"),
                    'result_reason' => _QXZ("no user found"),
                    'details' => array(
                        'alt_user' => $alt_user
                    )
                );
                header('Content-Type: application/json');
                header('HTTP/1.1 404 Not Found');
                echo json_encode($response);
                api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, $response['result'], $response['result_reason'], $source, $data);
            }
        }
        // VERIFICAR AGENTE
        $stmt = "select count(*), conf_exten from vicidial_live_agents where user='$agent_user';";
        if ($DB) {echo "$stmt\n";}
        $rslt = mysql_to_mysqli($stmt, $link);
        $row = mysqli_fetch_row($rslt);
        if ($row[0] > 0) {
            $conf_exten = $row[1];
            $stmt = "select agent_login_call from vicidial_session_data where user='$agent_user';";
            if ($DB) {echo "$stmt\n";}
            $rslt = mysql_to_mysqli($stmt, $link);
            $rl_ct = mysqli_num_rows($rslt);
            if ($rl_ct > 0) {
                $row = mysqli_fetch_row($rslt);
                $agent_login_call = $row[0];
                if (strlen($agent_login_call) > 5) {
                    $call_agent_conference = preg_replace("/(.+?Exten: )\d{7}(\|Priority.+)/", "$1 $conf_exten$2", $agent_login_call);
                    $call_agent_string = preg_replace("/\|/", "','", $call_agent_conference);
                    $stmt = "INSERT INTO vicidial_manager values('$call_agent_string');";
                    if ($format == 'debug') {
                        echo "\n<!-- $stmt -->";
                    }
                    $rslt = mysql_to_mysqli($stmt, $link);
                    $response = array(
                        'result' => _QXZ("SUCCESS"),
                        'result_reason' => _QXZ("call_agent function sent"),
                        'details' => array(
                            'agent_user' => $agent_user
                        )
                    );
                    header('Content-Type: application/json');
                    header('HTTP/1.1 200 OK');
                    echo json_encode($response);
                    $data = "$epoch";
                    api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, $response['result'], $response['result_reason'], $source, $data);
                } else {
                    $response = array(
                        'result' => _QXZ("ERROR"),
                        'result_reason' => _QXZ("call_agent error - entry is empty"),
                        'details' => array(
                            'agent_user' => $agent_user
                        )
                    );
                    header('Content-Type: application/json');
                    header('HTTP/1.1 400 Bad Request');
                    echo json_encode($response);
                    api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, $response['result'], $response['result_reason'], $source, $data);
                }
            } else {
                $response = array(
                    'result' => _QXZ("ERROR"),
                    'result_reason' => _QXZ("call_agent error - no session data"),
                    'details' => array(
                        'agent_user' => $agent_user
                    )
                );
                header('Content-Type: application/json');
                header('HTTP/1.1 400 Bad Request');
                echo json_encode($response);
                api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, $response['result'], $response['result_reason'], $source, $data);
            }
        } else {
            $response = array(
                'result' => _QXZ("ERROR"),
                'result_reason' => _QXZ("agent_user is not logged in"),
                'details' => array(
                    'agent_user' => $agent_user
                )
            );
            header('Content-Type: application/json');
            header('HTTP/1.1 400 Bad Request');
            echo json_encode($response);
            api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, $response['result'], $response['result_reason'], $source, $data);
        }
	}

    }


################################################################################
### END - call_agent
################################################################################

#######################ADJUST INTERVIA - RESTFULL_JSON##########################
################################################################################
### BEGIN - logout - log the agent out of the system
################################################################################

// FUNÇÃO DE LOGOUT
// Verifica se a função solicitadação "logout"
if ($function == 'logout') {
    // VALIDAR DADOS
    // Verifica se os dados de entrada sõ válidos
    if ((strlen($value) < 1) || ((strlen($agent_user) < 1) && (strlen($alt_user) < 1)) || (!preg_match("/LOGOUT/", $value))) {
        // RESPOSTA DE ERRO
        // Retorna uma resposta de erro em formato JSON
        $response = array(
            'result' => _QXZ("ERROR"),
            'result_reason' => _QXZ("logout not valid"),
            'details' => array(
                'value' => $value,
                'agent_user' => $agent_user
            )
        );
        header('Content-Type: application/json');
        header('HTTP/1.1 400 Bad Request');
        echo json_encode($response);
        // REGISTRO DE LOG
        // Registra o erro no log
        api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, $response['result'], $response['result_reason'], $source, $data);
    } else {
        // VERIFICAR PERMISSÃO
        // Verifica se o usuário tem permissão para executar a função
        if ((!preg_match("/ $function /", $VUapi_allowed_functions)) && (!preg_match("/ALL_FUNCTIONS/", $VUapi_allowed_functions))) {
            // RESPOSTA DE ERRO
            // Retorna uma resposta de erro em formato JSON
            $response = array(
                'result' => _QXZ("ERROR"),
                'result_reason' => _QXZ("auth USER DOES NOT HAVE PERMISSION TO USE THIS FUNCTION"),
                'details' => array(
                    'user' => $user,
                    'function' => $function,
                    'VUuser_group' => $VUuser_group
                )
            );
            header('Content-Type: application/json');
            header('HTTP/1.1 403 Forbidden');
            echo json_encode($response);
            // REGISTRO DE LOG
            // Registra o erro no log
            api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, $response['result'], $response['result_reason'], $source, $data);
            exit;
        }
        // VERIFICAR USUÃRIO
        // Verifica se o usuário existe
        if (strlen($alt_user) > 1) {
            $stmt = "select count(*) from vicidial_users where custom_three='$alt_user';";
            if ($DB) {echo "$stmt\n";}
            $rslt = mysql_to_mysqli($stmt, $link);
            $row = mysqli_fetch_row($rslt);
            if ($row[0] > 0) {
                $stmt = "select user from vicidial_users where custom_three='$alt_user' order by user;";
                if ($DB) {echo "$stmt\n";}
                $rslt = mysql_to_mysqli($stmt, $link);
                $row = mysqli_fetch_row($rslt);
                $agent_user = $row[0];
            } else {
                // RESPOSTA DE ERRO
                // Retorna uma resposta de erro em formato JSON
                $response = array(
                    'result' => _QXZ("ERROR"),
                    'result_reason' => _QXZ("no user found"),
                    'details' => array(
                        'alt_user' => $alt_user
                    )
                );
                header('Content-Type: application/json');
                header('HTTP/1.1 404 Not Found');
                echo json_encode($response);
                // REGISTRO DE LOG
                // Registra o erro no log
                api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, $response['result'], $response['result_reason'], $source, $data);
            }
        }
        // VERIFICAR AGENTE
        // Verifica se o agente está logado
        $stmt = "select count(*) from vicidial_live_agents where user='$agent_user';";
        if ($DB) {echo "$stmt\n";}
        $rslt = mysql_to_mysqli($stmt, $link);
        $row = mysqli_fetch_row($rslt);
        if ($row[0] > 0) {
            // ATUALIZAR AGENTE
            // Atualiza o agente com o valor de logout
            $stmt = "UPDATE vicidial_live_agents set external_pause='$value' where user='$agent_user';";
            if ($format == 'debug') {echo "\n<!-- $stmt -->";}
            $rslt = mysql_to_mysqli($stmt, $link);
            // RESPOSTA DE SUCESSO
            // Retorna uma resposta de sucesso em formato JSON
            $response = array(
                'result' => _QXZ("SUCCESS"),
                'result_reason' => _QXZ("logout function set"),
                'details' => array(
                    'value' => $value,
                    'agent_user' => $agent_user
                )
            );
            header('Content-Type: application/json');
            header('HTTP/1.1 200 OK');
            echo json_encode($response);
            // REGISTRO DE LOG
            // Registra o sucesso no log
            api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, $response['result'], $response['result_reason'], $source, $data);
        } else {
            // RESPOSTA DE ERRO
            // Retorna uma resposta de erro em formato JSON
            $response = array(
                'result' => _QXZ("ERROR"),
                'result_reason' => _QXZ("agent_user is not logged in"),
                'details' => array(
                    'agent_user' => $agent_user
                )
            );
            header('Content-Type: application/json');
            header('HTTP/1.1 400 Bad Request');
            echo json_encode($response);
            // REGISTRO DE LOG
            // Registra o erro no log
            api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, $response['result'], $response['result_reason'], $source, $data);
        }
    }
}
################################################################################
### END- logout
################################################################################

######################ADJUST INTERVIA - RESTFULL+JSON##########################
################################################################################
### BEGIN - external_pause - pause or resume the agent
################################################################################

// FUNÇÃO DE EXTERNAL_PAUSE
// Verifica se a função solicitada é "external_pause"
if ($function == 'external_pause') {
    // VALIDAR DADOS
    // Verifica se os dados de entrada são válidos
    if ((strlen($value) < 1) || ((strlen($agent_user) < 1) && (strlen($alt_user) < 1)) || (!preg_match("/PAUSE|RESUME/", $value))) {
        // RESPOSTA DE ERRO
        // Retorna uma resposta de erro em formato JSON
        $response = array(
            'result' => _QXZ("ERROR"),
            'result_reason' => _QXZ("external_pause not valid"),
            'details' => array(
                'value' => $value,
                'agent_user' => $agent_user
            )
        );
        header('Content-Type: application/json');
        header('HTTP/1.1 400 Bad Request');
        echo json_encode($response);
        // REGISTRO DE LOG
        // Registra o erro no log
        api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, $response['result'], $response['result_reason'], $source, $data);
    } else {
        // VERIFICAR PERMISSÃO
        // Verifica se o usuário tem permissão para executar a função
        if ((!preg_match("/ $function /", $VUapi_allowed_functions)) && (!preg_match("/ALL_FUNCTIONS/", $VUapi_allowed_functions))) {
            // RESPOSTA DE ERRO
            // Retorna uma resposta de erro em formato JSON
            $response = array(
                'result' => _QXZ("ERROR"),
                'result_reason' => _QXZ("auth USER DOES NOT HAVE PERMISSION TO USE THIS FUNCTION"),
                'details' => array(
                    'user' => $user,
                    'function' => $function,
                    'VUuser_group' => $VUuser_group
                )
            );
            header('Content-Type: application/json');
            header('HTTP/1.1 403 Forbidden');
            echo json_encode($response);
            // REGISTRO DE LOG
            // Registra o erro no log
            api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, $response['result'], $response['result_reason'], $source, $data);
            exit;
        }
        // VERIFICAR USUÁRIO
        // Verifica se o usuário existe
        if (strlen($alt_user) > 1) {
            $stmt = "select count(*) from vicidial_users where custom_three='$alt_user';";
            if ($DB) {echo "$stmt\n";}
            $rslt = mysql_to_mysqli($stmt, $link);
            $row = mysqli_fetch_row($rslt);
            if ($row[0] > 0) {
                $stmt = "select user from vicidial_users where custom_three='$alt_user' order by user;";
                if ($DB) {echo "$stmt\n";}
                $rslt = mysql_to_mysqli($stmt, $link);
                $row = mysqli_fetch_row($rslt);
                $agent_user = $row[0];
            } else {
                // RESPOSTA DE ERRO
                // Retorna uma resposta de erro em formato JSON
                $response = array(
                    'result' => _QXZ("ERROR"),
                    'result_reason' => _QXZ("no user found"),
                    'details' => array(
                        'alt_user' => $alt_user
                    )
                );
                header('Content-Type: application/json');
                header('HTTP/1.1 404 Not Found');
                echo json_encode($response);
                // REGISTRO DE LOG
                // Registra o erro no log
                api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, $response['result'], $response['result_reason'], $source, $data);
            }
        }
        // VERIFICAR AGENTE
        // Verifica se o agente está logado
        $stmt = "select count(*) from vicidial_live_agents where user='$agent_user';";
        if ($DB) {echo "$stmt\n";}
        $rslt = mysql_to_mysqli($stmt, $link);
        $row = mysqli_fetch_row($rslt);
        if ($row[0] > 0) {
            // VERIFICAR SE O AGENTE ESTÁ PAUSADO
            // Verifica se o agente está pausado antes de tentar pausar ou retomar
            if (preg_match("/RESUME/", $value)) {
                $stmt = "select count(*) from vicidial_live_agents where user='$agent_user' and status IN('READY','QUEUE','INCALL','CLOSER');";
                if ($DB) {echo "$stmt\n";}
                $rslt = mysql_to_mysqli($stmt, $link);
                $row = mysqli_fetch_row($rslt);
                if ($row[0] > 0) {
                    // RESPOSTA DE ERRO
                    // Retorna uma resposta de erro em formato JSON
                    $response = array(
                        'result' => _QXZ("ERROR"),
                        'result_reason' => _QXZ("external_pause agent is not paused"),
                        'details' => array(
                            'value' => $value,
                            'agent_user' => $agent_user
                        )
                    );
                    header('Content-Type: application/json');
                    header('HTTP/1.1 400 Bad Request');
                    echo json_encode($response);
                    // REGISTRO DE LOG
                    // Registra o erro no log
                    api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, $response['result'], $response['result_reason'], $source, $data);
                    exit;
                }
            }
            // ATUALIZAR AGENTE
            // Atualiza o agente com o valor de pausa ou retomada
            $stmt = "UPDATE vicidial_live_agents set external_pause='$value!$epoch' where user='$agent_user';";
            if ($format == 'debug') {echo "\n<!-- $stmt -->";}
            $rslt = mysql_to_mysqli($stmt, $link);
            // RESPOSTA DE SUCESSO
            // Retorna uma resposta de sucesso em formato JSON
            $response = array(
                'result' => _QXZ("SUCCESS"),
                'result_reason' => _QXZ("external_pause function set"),
                'details' => array(
                    'value' => $value,
                    'agent_user' => $agent_user
                )
            );
            header('Content-Type: application/json');
            header('HTTP/1.1 200 OK');
            echo json_encode($response);
                    // REGISTRO DE LOG
					// Registra o erro no log
                    api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, $response['result'], $response['result_reason'], $source, $data);
                    exit;
                }
                   
		}

	}



################################################################################
### FINAL - external_pause - pause or resume the agent
################################################################################

 
######################ADJUST INTERVIA - RESTFULL+JSON##########################
################################################################################
### BEGIN - external_dial - place a manual dial phone call
################################################################################

// FUNÇÃO DE EXTERNAL DIAL  
// Verifica se a função solicitada é "external_dial"  
if ($function == 'external_dial') {  
    // VALIDAR DADOS  
   // Verifica se os dados de entrada são válidos  
   if ((strlen($value) < 2 && strlen($lead_id) < 1) || (strlen($agent_user) < 2 && strlen($alt_user) < 2) || strlen($search) < 2 || strlen($preview) < 2 || strlen($focus) < 2) {  
      // RESPOSTA DE ERRO  
      // Retorna uma resposta de erro em formato JSON  
      $response = array(  
        'result' => _QXZ("ERROR"),  
        'result_reason' => _QXZ("external_dial not valid"),  
        'details' => array(  
           'value' => $value,  
           'lead_id' => $lead_id,  
           'agent_user' => $agent_user,  
           'alt_user' => $alt_user,  
           'search' => $search,  
           'preview' => $preview,  
           'focus' => $focus  
        )  
      );  
      header('Content-Type: application/json');  
      header('HTTP/1.1 400 Bad Request');  
      echo json_encode($response);  
      // REGISTRO DE LOG  
      // Registra o erro no log  
      api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, $response['result'], $response['result_reason'], $source, $data);  
   } else {  
      // VERIFICAR PERMISSÃO  
      // Verifica se o usuário tem permissão para executar a função  
      if ((!preg_match("/ $function /", $VUapi_allowed_functions)) && (!preg_match("/ALL_FUNCTIONS/", $VUapi_allowed_functions))) {  
        // RESPOSTA DE ERRO  
        // Retorna uma resposta de erro em formato JSON  
        $response = array(  
           'result' => _QXZ("ERROR"),  
           'result_reason' => _QXZ("auth USER DOES NOT HAVE PERMISSION TO USE THIS FUNCTION"),  
           'details' => array(  
              'user' => $user,  
              'function' => $function,  
              'VUuser_group' => $VUuser_group  
           )  
        );  
        header('Content-Type: application/json');  
        header('HTTP/1.1 403 Forbidden');  
        echo json_encode($response);  
        // REGISTRO DE LOG  
        // Registra o erro no log  
        api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, $response['result'], $response['result_reason'], $source, $data);  
        exit;  
      }  
      // VERIFICAR USUÁRIO  
      // Verifica se o usuário existe  
      if (strlen($alt_user) > 1) {  
        $stmt = "SELECT count(*) FROM vicidial_users WHERE custom_three='$alt_user';";  
        if ($DB) {echo "$stmt\n";}  
        $rslt = mysql_to_mysqli($stmt, $link);  
        $row = mysqli_fetch_row($rslt);  
        if ($row[0] > 0) {  
           $stmt = "SELECT user FROM vicidial_users WHERE custom_three='$alt_user' ORDER BY user;";  
           if ($DB) {echo "$stmt\n";}  
           $rslt = mysql_to_mysqli($stmt, $link);  
           $row = mysqli_fetch_row($rslt);  
           $agent_user = $row[0];  
        } else {  
           // RESPOSTA DE ERRO  
           // Retorna uma resposta de erro em formato JSON  
           $response = array(  
              'result' => _QXZ("ERROR"),  
              'result_reason' => _QXZ("no user found"),  
              'details' => array(  
                'alt_user' => $alt_user  
              )  
           );  
           header('Content-Type: application/json');  
           header('HTTP/1.1 404 Not Found');  
           echo json_encode($response);  
           // REGISTRO DE LOG  
           // Registra o erro no log  
           api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, $response['result'], $response['result_reason'], $source, $data);  
        }  
      }  
      // VERIFICAR AGENTE  
// Verifica se o agente está logado  
$stmt = "SELECT count(*) FROM vicidial_live_agents WHERE user='$agent_user';";  
if ($DB) {echo "$stmt\n";}  
$rslt = mysql_to_mysqli($stmt, $link);  
$row = mysqli_fetch_row($rslt);  
if ($row[0] > 0) {  
   // ATUALIZAR AGENTE  
   // Atualiza o agente com o valor de external_dial  
   $stmt = "UPDATE vicidial_live_agents SET external_dial='$value!$phone_code!$search!$preview!$focus!$vendor_id!$epoch!$dial_prefix!$group_alias!$caller_id_number!$vtiger_callback_id!$lead_id!$alt_dial!$dial_ingroup' WHERE user='$agent_user';";  
   if ($format == 'debug') {echo "\n<!-- $stmt -->";}  
   $rslt = mysql_to_mysqli($stmt, $link);  
   // RESPOSTA DE SUCESSO  
   // Retorna uma resposta de sucesso em formato JSON  
   $response = array(  
      'result' => _QXZ("SUCCESS"),  
      'result_reason' => _QXZ("external_dial function set"),  
      'details' => array(  
        'value' => $value,  
        'agent_user' => $agent_user  
      )  
   );  
   header('Content-Type: application/json');  
   header('HTTP/1.1 200 OK');  
   echo json_encode($response);  
   // REGISTRO DE LOG  
   api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, $response['result'], $response['result_reason'], $source, $data);  
} else {  
   // RESPOSTA DE ERRO  
   // Retorna uma resposta de erro em formato JSON  
   $response = array(  
      'result' => _QXZ("ERROR"),  
      'result_reason' => _QXZ("agent_user is not logged in"),  
      'details' => array(  
        'agent_user' => $agent_user  
      )  
   );  
   header('Content-Type: application/json');  
   header('HTTP/1.1 400 Bad Request');  
   echo json_encode($response);  
   // REGISTRO DE LOG  
   api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, $response['result'], $response['result_reason'], $source, $data);  
    }
   }
}
################################################################################
### FINAL - external_dial - place a manual dial phone call
################################################################################


######################ADJUST INTERVIA - RESTFULL+JSON##########################
################################################################################
### BEGIN - external_add_lead - add lead in manual dial list of the campaign for logged-in agent
################################################################################

  
// FUNÇÃO DE EXTERNAL ADD LEAD  
// Verifica se a função solicitada é "external_add_lead"  
if ($function == 'external_add_lead') {  
    // VALIDAR DADOS  
    // Verifica se os dados de entrada são válidos  
    if ((strlen($value) < 1 && strlen($phone_number) > 1) || (strlen($agent_user) < 2 && strlen($alt_user) < 2) || strlen($phone_code) < 1) {  
       // RESPOSTA DE ERRO  
       // Retorna uma resposta de erro em formato JSON  
       $response = array(  
         'result' => _QXZ("ERROR"),  
         'result_reason' => _QXZ("external_add_lead not valid"),  
         'details' => array(  
            'value' => $value,  
            'phone_code' => $phone_code,  
            'agent_user' => $agent_user,  
            'alt_user' => $alt_user  
         )  
       );  
       header('Content-Type: application/json');  
       header('HTTP/1.1 400 Bad Request');  
       echo json_encode($response);  
       // REGISTRO DE LOG  
       // Registra o erro no log  
       api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, $response['result'], $response['result_reason'], $source, $data);  
       exit;  
    } else {  
       // VERIFICAR PERMISSÃO  
       // Verifica se o usuário tem permissão para executar a função  
       if ((!preg_match("/ $function /", $VUapi_allowed_functions)) && (!preg_match("/ALL_FUNCTIONS/", $VUapi_allowed_functions))) {  
         // RESPOSTA DE ERRO  
         // Retorna uma resposta de erro em formato JSON  
         $response = array(  
            'result' => _QXZ("ERROR"),  
            'result_reason' => _QXZ("auth USER DOES NOT HAVE PERMISSION TO USE THIS FUNCTION"),  
            'details' => array(  
               'user' => $user,  
               'function' => $function,  
               'VUuser_group' => $VUuser_group  
            )  
         );  
         header('Content-Type: application/json');  
         header('HTTP/1.1 403 Forbidden');  
         echo json_encode($response);  
         // REGISTRO DE LOG  
         // Registra o erro no log  
         api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, $response['result'], $response['result_reason'], $source, $data);  
         exit;  
       }  
       // VERIFICAR USUÁRIO  
       // Verifica se o usuário existe  
       if (strlen($alt_user) > 1) {  
         $stmt = "SELECT count(*) FROM vicidial_users WHERE custom_three='$alt_user';";  
         if ($DB) {echo "$stmt\n";}  
         $rslt = mysql_to_mysqli($stmt, $link);  
         $row = mysqli_fetch_row($rslt);  
         if ($row[0] > 0) {  
            $stmt = "SELECT user FROM vicidial_users WHERE custom_three='$alt_user' ORDER BY user;";  
            if ($DB) {echo "$stmt\n";}  
            $rslt = mysql_to_mysqli($stmt, $link);  
            $row = mysqli_fetch_row($rslt);  
            $agent_user = $row[0];  
         } else {  
            // RESPOSTA DE ERRO  
            // Retorna uma resposta de erro em formato JSON  
            $response = array(  
               'result' => _QXZ("ERROR"),  
               'result_reason' => _QXZ("no user found"),  
               'details' => array(  
                 'alt_user' => $alt_user  
               )  
            );  
            header('Content-Type: application/json');  
            header('HTTP/1.1 404 Not Found');  
            echo json_encode($response);  
            // REGISTRO DE LOG  
            // Registra o erro no log  
            api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, $response['result'], $response['result_reason'], $source, $data);  
         }  
       }  
       // VERIFICAR AGENTE  
       // Verifica se o agente está logado  
       $stmt = "SELECT count(*) FROM vicidial_live_agents WHERE user='$agent_user';";  
       if ($DB) {echo "$stmt\n";}  
       $rslt = mysql_to_mysqli($stmt, $link);  
       $row = mysqli_fetch_row($rslt);  
       if ($row[0] > 0) {  
         // ATUALIZAR AGENTE  
         // Atualiza o agente com o valor de external_add_lead  
         $stmt = "INSERT INTO vicidial_list SET phone_code=\"$phone_code\",phone_number=\"$value\",list_id=\"$list_id\",status=\"NEW\",user=\"$user\",vendor_lead_code=\"$vendor_lead_code\",source_id=\"$source_id\",title=\"$title\",first_name=\"$first_name\",middle_initial=\"$middle_initial\",last_name=\"$last_name\",address1=\"$address1\",address2=\"$address2\",address3=\"$address3\",city=\"$city\",state=\"$state\",province=\"$province\",postal_code=\"$postal_code\",country_code=\"$country_code\",gender=\"$gender\",date_of_birth=\"$date_of_birth\",alt_phone=\"$alt_phone\",email=\"$email\",security_phrase=\"$security_phrase\",comments=\"$comments\",called_since_last_reset=\"N\",entry_date=\"$ENTRYdate\",last_local_call_time=\"$NOW_TIME\",rank=\"$rank\",owner=\"$owner\";";  
         if ($DB) {echo "$stmt\n";}  
         $rslt = mysql_to_mysqli($stmt, $link);  
         $affected_rows = mysqli_affected_rows($link);  
         if ($affected_rows > 0) {  
            $lead_id = mysqli_insert_id($link);  
            // RESPOSTA DE SUCESSO  
    // Retorna uma resposta de sucesso em formato JSON  
    $response = array(  
     'result' => _QXZ("SUCCESS"),  
     'result_reason' => _QXZ("external_add_lead function set"),  
     'details' => array(  
       'value' => $value,  
       'agent_user' => $agent_user,
       'campaign_id' => $campaign_id,
       'list_id' => $list_id  
     )  
  );  
  header('Content-Type: application/json');  
  header('HTTP/1.1 200 OK');  
  echo json_encode($response);  
  // REGISTRO DE LOG  
  api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, $response['result'], $response['result_reason'], $source, $data);  
 } else {  
  // RESPOSTA DE ERRO  
  // Retorna uma resposta de erro em formato JSON  
  $response = array(  
     'result' => _QXZ("ERROR"),  
     'result_reason' => _QXZ("agent_user is not logged in"),  
     'details' => array(  
       'agent_user' => $agent_user  
     )  
  );  
  header('Content-Type: application/json');  
  header('HTTP/1.1 400 Bad Request');  
  echo json_encode($response);  
  // REGISTRO DE LOG  
  api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, $response['result'], $response['result_reason'], $source, $data);  
           }
      }
 }
}
 ################################################################################
 ### END - external_add_lead
 ################################################################################
 
 

?>