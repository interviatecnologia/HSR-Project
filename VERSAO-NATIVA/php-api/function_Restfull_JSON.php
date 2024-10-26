<?php

###############ADJUST INTERVIA - RESTFULL+JSON##########################
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



################################################################################
### BEGIN - preview_dial_action - sends a SKIP, DIALONLY, ALTDIAL, ADR3DIAL or FINISH when a lead is being previewed or manual alt dial
################################################################################
if ($function == 'preview_dial_action')
	{
	$value = preg_replace("/[^A-Z0-9]/","",$value);

	if ( (strlen($value)<4) or ( (strlen($agent_user)<2) and (strlen($alt_user)<2) ) or ( ($value != 'SKIP') and ($value != 'DIALONLY') and ($value != 'ALTDIAL') and ($value != 'ADR3DIAL') and ($value != 'FINISH') ) )
		{
		$result = _QXZ("ERROR");
		$result_reason = _QXZ("preview_dial_action not valid");
		$data = "";
		echo "$result: $result_reason - $value|$data|$agent_user|$alt_user\n";
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
			$stmt = "SELECT campaign_id,status FROM vicidial_live_agents where user='$agent_user';";
			$rslt=mysql_to_mysqli($stmt, $link);
			$vlac_conf_ct = mysqli_num_rows($rslt);
			if ($vlac_conf_ct > 0)
				{
				$row=mysqli_fetch_row($rslt);
				$vac_campaign_id =	$row[0];
				$vac_status =		$row[1];
				}
			$stmt = "SELECT manual_preview_dial,alt_number_dialing FROM vicidial_campaigns where campaign_id='$vac_campaign_id';";
			$rslt=mysql_to_mysqli($stmt, $link);
			$vcc_conf_ct = mysqli_num_rows($rslt);
			if ($vcc_conf_ct > 0)
				{
				$row=mysqli_fetch_row($rslt);
				$manual_preview_dial =	$row[0];
				$alt_number_dialing =	$row[1];
				}
			if ($manual_preview_dial == 'DISABLED')
				{
				$result = _QXZ("ERROR");
				$result_reason = _QXZ("preview dialing not allowed on this campaign");
				$data = "$vac_campaign_id|$manual_preview_dial";
				echo "$result: $result_reason - $agent_user|$data\n";
				api_log($link,$api_logging,$api_script,$user,$agent_user,$function,$value,$result,$result_reason,$source,$data);
				exit;
				}
			if ( ($manual_preview_dial == 'PREVIEW_ONLY') and ($value == 'SKIP') )
				{
				$result = _QXZ("ERROR");
				$result_reason = _QXZ("preview dial skipping not allowed on this campaign");
				$data = "$vac_campaign_id|$manual_preview_dial";
				echo "$result: $result_reason - $agent_user|$data\n";
				api_log($link,$api_logging,$api_script,$user,$agent_user,$function,$value,$result,$result_reason,$source,$data);
				exit;
				}
			if ( ($alt_number_dialing == 'N') and ( ($value == 'ALTDIAL') or ($value == 'ADR3DIAL') or ($value == 'FINISH') ) )
				{
				$result = _QXZ("ERROR");
				$result_reason = _QXZ("alt number dialing not allowed on this campaign");
				$data = "$vac_campaign_id|$alt_number_dialing";
				echo "$result: $result_reason - $agent_user|$data\n";
				api_log($link,$api_logging,$api_script,$user,$agent_user,$function,$value,$result,$result_reason,$source,$data);
				exit;
				}

			$stmt = "select count(*) from vicidial_live_agents where user='$agent_user' and status='PAUSED';";
			if ($DB) {echo "$stmt\n";}
			$rslt=mysql_to_mysqli($stmt, $link);
			$row=mysqli_fetch_row($rslt);
			$agent_ready = $row[0];

			if ($agent_ready > 0)
				{
				$stmt = "select count(*) from vicidial_users where user='$agent_user' and agentcall_manual='1';";
				if ($DB) {echo "$stmt\n";}
				$rslt=mysql_to_mysqli($stmt, $link);
				$row=mysqli_fetch_row($rslt);
				if ($row[0] > 0)
					{
					$stmt="UPDATE vicidial_live_agents set external_dial='$value' where user='$agent_user';";
						if ($format=='debug') {echo "\n<!-- $stmt -->";}
					$rslt=mysql_to_mysqli($stmt, $link);

					if ($format=='debug') {echo "\n<!-- $stmt -->";}
					$rslt=mysql_to_mysqli($stmt, $link);
					$result = _QXZ("SUCCESS");
					$result_reason = _QXZ("preview_dial_action function set");
					$data = "$value";
					echo "$result: $result_reason - $value|$agent_user|$data\n";
					api_log($link,$api_logging,$api_script,$user,$agent_user,$function,$value,$result,$result_reason,$source,$data);
					}
				else
					{
					$result = _QXZ("ERROR");
					$result_reason = _QXZ("agent_user is not allowed to place manual dial calls");
					echo "$result: $result_reason - $agent_user\n";
					api_log($link,$api_logging,$api_script,$user,$agent_user,$function,$value,$result,$result_reason,$source,$data);
					}
				}
			else
				{
				$result = _QXZ("ERROR");
				$result_reason = _QXZ("agent_user is not paused");
				echo "$result: $result_reason - $agent_user\n";
				api_log($link,$api_logging,$api_script,$user,$agent_user,$function,$value,$result,$result_reason,$source,$data);
				}
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
### END - preview_dial_action
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


 ################################################################################
 ### END - external_add_lead
 ################################################################################
>?