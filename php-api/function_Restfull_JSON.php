
<?php

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

######################ADJUST INTERVIA - RESTFULL+JSON##########################
################################################################################
### BEGIN - logout - log the agent out of the system
################################################################################
if ($function == 'logout')
	{
	if ( (strlen($value)<1) or ( (strlen($agent_user)<1) and (strlen($alt_user)<1) ) or (!preg_match("/LOGOUT/",$value)) )
		{
		$result = _QXZ("ERROR");
		$result_reason = _QXZ("logout not valid");
		echo "$result: $result_reason - $value|$agent_user\n";
		api_log($link,$api_logging,$api_script,$user,$agent_user,$function,$value,$result,$result_reason,$source,$data);
		}
	else
		{
		if ( (!preg_match("/ $function /",$VUapi_allowed_functions)) and (!preg_match("/ALL_FUNCTIONS/",$VUapi_allowed_functions)) )
			{
			$result = _QXZ("ERROR");
			$result_reason = _QXZ("auth USER DOES NOT HAVE PERMISSION TO USE THIS FUNCTION");
			echo "$result: $result_reason - $value|$user|$function|$VUuser_group\n";
			api_log($link,$api_logging,$api_script,$user,$agent_user,$function,$value,$result,$result_reason,$source,$data);
			exit;
			}
		if (strlen($alt_user)>1)
			{
			$stmt = "select count(*) from vicidial_users where custom_three='$alt_user';";
			if ($DB) {echo "$stmt\n";}
			$rslt=mysql_to_mysqli($stmt, $link);
			$row=mysqli_fetch_row($rslt);
			if ($row[0] > 0)
				{
				$stmt = "select user from vicidial_users where custom_three='$alt_user' order by user;";
				if ($DB) {echo "$stmt\n";}
				$rslt=mysql_to_mysqli($stmt, $link);
				$row=mysqli_fetch_row($rslt);
				$agent_user = $row[0];
				}
			else
				{
				$result = _QXZ("ERROR");
				$result_reason = _QXZ("no user found");
				echo "$result: $result_reason - $alt_user\n";
				api_log($link,$api_logging,$api_script,$user,$agent_user,$function,$value,$result,$result_reason,$source,$data);
				}
			}
		$stmt = "select count(*) from vicidial_live_agents where user='$agent_user';";
		if ($DB) {echo "$stmt\n";}
		$rslt=mysql_to_mysqli($stmt, $link);
		$row=mysqli_fetch_row($rslt);
		if ($row[0] > 0)
			{
			$stmt="UPDATE vicidial_live_agents set external_pause='$value' where user='$agent_user';";
				if ($format=='debug') {echo "\n<!-- $stmt -->";}
			$rslt=mysql_to_mysqli($stmt, $link);
			$result = _QXZ("SUCCESS");
			$result_reason = _QXZ("logout function set");
			#echo "$result: $result_reason - $value|$epoch|$agent_user\n";
			$data = "$epoch";
			api_log($link,$api_logging,$api_script,$user,$agent_user,$function,$value,$result,$result_reason,$source,$data);
			}
		else
			{
			$result = _QXZ("ERROR");
			$result_reason = _QXZ("agent_user is not logged in");
			echo "$result: $result_reason - $agent_user\n";
			api_log($link,$api_logging,$api_script,$user,$agent_user,$function,$value,$result,$result_reason,$source,$data);
			}
		}
	}

################################################################################
### FINAL - logout 
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


?>