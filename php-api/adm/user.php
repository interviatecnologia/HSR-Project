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
           $stmt = "SELECT user_level,user_group,modify_same_user_level from vicidial_users where user='$user' and vdc_agent_api_access='1' and modify_users='1' and user_level >= 8;";  
           $rslt = mysql_to_mysqli($stmt, $link);  
           $row = mysqli_fetch_row($rslt);  
           $user_level = $row[0];  
           $LOGuser_group = $row[1];  
           $modify_same_user_level = $row[2];  
  
           $stmt = "SELECT allowed_campaigns,admin_viewable_groups from vicidial_user_groups where user_group='$LOGuser_group';";  
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
                   'user_level' => $user_level  
                )  
              );  
              
              ?>