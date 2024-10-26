<?php
if ($function == 'add_user') {  
    if (strlen($source) < 2) {  
       // RESPOSTA DE ERRO  
       $response = array(  
         'result' => 'ERROR',  
         'result_reason' => 'Invalid Source',  
         'details' => array(  
            'source' => $source  
         )  
       );  
       http_response_code(400);  
       echo json_encode($response);  
       // REGISTRO DE LOG  
       api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, $response['result'], $response['result_reason'], $source, $data);  
       exit;  
    } else {  
       if ((!preg_match("/ $function /", $api_allowed_functions)) && (!preg_match("/ALL_FUNCTIONS/", $api_allowed_functions))) {  
         // RESPOSTA DE ERRO  
         $response = array(  
            'result' => 'ERROR',  
            'result_reason' => 'auth USER DOES NOT HAVE PERMISSION TO USE THIS FUNCTION',  
            'details' => array(  
               'user' => $user,  
               'function' => $function  
            )  
         );  
         http_response_code(403);  
         echo json_encode($response);  
         // REGISTRO DE LOG  
         api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, $response['result'], $response['result_reason'], $source, $data);  
         exit;  
       }  
       $stmt = "SELECT count(*) from vicidial_users where user='$user' and vdc_agent_api_access='1' and modify_users='1' and user_level >= 8 and active='Y';";  
       $rslt = mysql_to_mysqli($stmt, $link);  
       $row = mysqli_fetch_row($rslt);  
       $allowed_user = $row[0];  
       if ($allowed_user < 1) {  
         // RESPOSTA DE ERRO  
         $response = array(  
            'result' => 'ERROR',  
            'result_reason' => 'add_user USER DOES NOT HAVE PERMISSION TO ADD USERS',  
            'details' => array(  
               'user' => $user,  
               'allowed_user' => $allowed_user  
            )  
         );  
         http_response_code(403);  
         echo json_encode($response);  
         // REGISTRO DE LOG  
         api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, $response['result'], $response['result_reason'], $source, $data);  
         exit;  
       } else {  
         if ((strlen($agent_user) < 2) || (strlen($agent_pass) < 2) || (strlen($agent_user_level) < 1) || (strlen($agent_full_name) < 1) || (strlen($agent_user_group) < 1)) {  
            // RESPOSTA DE ERRO  
            $response = array(  
               'result' => 'ERROR',  
               'result_reason' => 'add_user YOU MUST USE ALL REQUIRED FIELDS',  
               'details' => array(  
                 'agent_user' => $agent_user,  
                 'agent_pass' => $agent_pass,  
                 'agent_user_level' => $agent_user_level,  
                 'agent_full_name' => $agent_full_name,  
                 'agent_user_group' => $agent_user_group  
               )  
            );  
            http_response_code(400);  
            echo json_encode($response);  
            // REGISTRO DE LOG  
            api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, $response['result'], $response['result_reason'], $source, $data);  
            exit;  
         } else {  
            $stmt = "SELECT user_level, user_group, modify_same_user_level from vicidial_users where user='$user' and vdc_agent_api_access='1' and modify_users='1' and user_level >= 8;";  
            $rslt = mysql_to_mysqli($stmt, $link);  
            $row = mysqli_fetch_row($rslt);  
            $user_level = $row[0];  
            $LOGuser_group = $row[1];  
            $modify_same_user_level = $row[2];  
   
            $stmt = "SELECT allowed_campaigns, admin_viewable_groups from vicidial_user_groups where user_group='$LOGuser_group';";  
            $rslt = mysql_to_mysqli($stmt, $link);  
            $row = mysqli_fetch_row($rslt);  
            $LOGallowed_campaigns = $row[0];  
            $LOGadmin_viewable_groups = $row[1];  
   
            $LOGadmin_viewable_groupsSQL = '';  
            $whereLOGadmin_viewable_groupsSQL = '';  
            if ((!preg_match('/\-\-ALL\-\-/i', $LOGadmin_viewable_groups)) && (strlen($LOGadmin_viewable_groups) > 3)) {  
               $rawLOGadmin_viewable_groupsSQL = preg_replace("/ -/", '', $LOGadmin_viewable_groups);  
               $rawLOGadmin_viewable_groupsSQL = preg_replace("/ /", "','", $rawLOGadmin_viewable_groupsSQL);  
               $LOGadmin_viewable_groupsSQL = "and user_group IN('---ALL---','$rawLOGadmin_viewable_groupsSQL')";  
               $whereLOGadmin_viewable_groupsSQL = "where user_group IN('---ALL---','$rawLOGadmin_viewable_groupsSQL')";  
            }  
   
            if ((($user_level < 9) && ($user_level <= $agent_user_level)) || (($modify_same_user_level < 1) && ($user_level >= 9) && ($user_level == $agent_user_level))) {  
               // RESPOSTA DE ERRO  
               $response = array(  
                 'result' => 'ERROR',  
                 'result_reason' => 'add_user USER DOES NOT HAVE PERMISSION TO ADD USERS IN THIS USER LEVEL',  
                 'details' => array(  
                    'agent_user_level' => $agent_user_level,  
                 )
                 );

// RESPOSTA DE ERRO  
                $response = array(  
                'result' => 'ERROR',  
                'result_reason' => 'add_user USER DOES NOT HAVE PERMISSION TO ADD USERS IN THIS USER LEVEL',  
                 'details' => array(  
                'agent_user_level' => $agent_user_level,  
                'user_level' => $user_level  
                
            )

                 );

        http_response_code(403);  
        echo json_encode($response);  
     // REGISTRO DE LOG  
        api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, $response['result'], $response['result_reason'], $source, $data);  
        exit;  
    } else {  
    $stmt = "SELECT count(*) from vicidial_user_groups where user_group='$agent_user_group' $LOGadmin_viewable_groupsSQL;";  
    $rslt = mysql_to_mysqli($stmt, $link);  
    $row = mysqli_fetch_row($rslt);  
    $group_exists = $row[0];  
    if ($group_exists < 1) {  
       // RESPOSTA DE ERRO  
       $response = array(  
         'result' => 'ERROR',  
         'result_reason' => 'add_user USER GROUP DOES NOT EXIST',  
         'details' => array(  
            'agent_user_group' => $agent_user_group  
         )  
       );  
       http_response_code(404);  
       echo json_encode($response);  
       // REGISTRO DE LOG  
       api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, $response['result'], $response['result_reason'], $source, $data);  
       exit;  
    } else {  
       $stmt = "SELECT count(*) from vicidial_users where user='$agent_user';";  
       $rslt = mysql_to_mysqli($stmt, $link);  
       $row = mysqli_fetch_row($rslt);  
       $user_exists = $row[0];  
       if ($user_exists > 0) {  
         // RESPOSTA DE ERRO  
         $response = array(  
            'result' => 'ERROR',  
            'result_reason' => 'add_user USER ALREADY EXISTS',  
            'details' => array(  
               'agent_user' => $agent_user  
            )  
         );  
         http_response_code(409);  
         echo json_encode($response);  
         // REGISTRO DE LOG  
         api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, $response['result'], $response['result_reason'], $source, $data);  
         exit;  
       } else {  
         # if user value is set to autogenerate then find the next value for user  
         if (preg_match('/AUTOGENERA/', $agent_user)) {  
            $new_user = 0;  
            $auto_user_add_value = 0;  
            while ($new_user < 2) {  
               if ($new_user < 1) {  
                 $stmt = "SELECT auto_user_add_value FROM system_settings;";  
                 $rslt = mysql_to_mysqli($stmt, $link);  
                 $ss_auav_ct = mysqli_num_rows($rslt);  
                 if ($ss_auav_ct > 0) {  
                    $row = mysqli_fetch_row($rslt);  
                    $auto_user_add_value = $row[0];  
                 }  
                 $new_user++;  
               }  
               $stmt = "SELECT count(*) FROM vicidial_users where user='$auto_user_add_value';";  
               $rslt = mysql_to_mysqli($stmt, $link);  
               $row = mysqli_fetch_row($rslt);  
               if ($row[0] < 1) {  
                 $new_user++;  
               } else {  
                 # echo "<!-- AG: $auto_user_add_value -->\n";  
                 $auto_user_add_value = ($auto_user_add_value + 7);  
               }  
            }  
            $agent_user = $auto_user_add_value;  
   
            $stmt = "UPDATE system_settings SET auto_user_add_value='$agent_user';";  
            $rslt = mysql_to_mysqli($stmt, $link);  
         }  
   
         if (strlen($hotkeys_active) < 1) {  
            $hotkeys_active = '0';  
         }  
         if (strlen($wrapup_seconds_override) < 1) {  
            $wrapup_seconds_override = '-1';  
         }  
         if (strlen($agent_choose_ingroups) < 1) {  
            $agent_choose_ingroups = '1';  
         }  
         if (strlen($agent_choose_blended) < 1) {  
            $agent_choose_blended = '1';  
         }  
         if (strlen($closer_default_blended) < 1) {  
            $closer_default_blended = '0';  
         }  
   
         $pass_hash = '';  
         if (($SSpass_hash_enabled > 0) && (strlen($agent_pass) > 1)) {  
            $agent_pass = preg_replace("/\'|\"|\\\\|;| /", "", $agent_pass);  
            $pass_hash = exec("../agc/bp.pl --pass=$agent_pass");  
            $pass_hash = preg_replace("/PHASH: |\n|\r|\t| /", '', $pass_hash);  
            $agent_pass = '';  
         }  
   
         if (strlen($in_groups) > 0) {  
            $in_groups = preg_replace("/\|/", " ", $in_groups);  
            $in_groups = " " . $in_groups . " -";  
         }  
   
         // Adicionar usuário ao banco de dados  
$stmt = "INSERT INTO vicidial_users (user, pass, full_name, user_level, user_group, phone_login, phone_pass, hotkeys_active, voicemail_id, email, custom_one, custom_two, custom_three, custom_four, custom_five, pass_hash, wrapup_seconds_override, agent_choose_ingroups, agent_choose_blended, closer_default_blended, closer_campaigns) values('$agent_user', '$agent_pass', '$agent_full_name', '$agent_user_level', '$agent_user_group', '$phone_login', '$phone_pass', '$hotkeys_active', '$voicemail_id', '$email', '$custom_one', '$custom_two', '$custom_three', '$custom_four', '$custom_five', '$pass_hash', '$wrapup_seconds_override', '$agent_choose_ingroups', '$agent_choose_blended', '$closer_default_blended', '$in_groups');";  
$rslt = mysql_to_mysqli($stmt, $link);  
if (!$rslt) {  
   // RESPOSTA DE ERRO  
   $response = array(  
      'result' => 'ERROR',  
      'result_reason' => 'add_user ERRO AO ADICIONAR USUÁRIO',  
      'details' => array(  
        'agent_user' => $agent_user,  
        'agent_pass' => $agent_pass,  
        'agent_user_level' => $agent_user_level,  
        'agent_full_name' => $agent_full_name,  
        'agent_user_group' => $agent_user_group  
      )  
   );  
   http_response_code(500);  
   echo json_encode($response);  
   // REGISTRO DE LOG  
   api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, $response['result'], $response['result_reason'], $source, $data);  
   exit;  
}  
  
// Sucesso  
$response = array(  
   'result' => 'SUCCESS',  
   'result_reason' => 'add_user USER ADDED SUCCESSFULLY',  
   'details' => array(  
      'agent_user' => $agent_user,  
      'agent_pass' => $agent_pass,  
      'agent_user_level' => $agent_user_level,  
      'agent_full_name' => $agent_full_name,  
      'agent_user_group' => $agent_user_group  
   )  
);  
http_response_code(200);  
echo json_encode($response);  
// REGISTRO DE LOG  
api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, $response['result'], $response['result_reason'], $source, $data);  
exit;
  
       }

    }

}
        
    

?>
