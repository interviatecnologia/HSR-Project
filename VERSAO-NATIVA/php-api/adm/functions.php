<?php
######################ADJUST INTERVIA - RESTFULL+JSON##########################
################################################################################
### BEGIN - add_lead - inserts a lead into the vicidial_list table
################################################################################

  
// FUNÇÃO DE ADD LEAD
if ($function == 'add_lead') {
    // VALIDAR DADOS
    if (strlen($source) < 2) {
        // RESPOSTA DE ERRO
        $response = array(
            'result' => 'ERROR',
            'result_reason' => 'Invalid Source',
            'details' => array(
                'source' => $source
            )
        );
        header('Content-Type: application/json');
        header('HTTP/1.1 400 Bad Request');
        echo json_encode($response);
        // REGISTRO DE LOG
        api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, $response['result'], $response['result_reason'], $source, $data);
        exit;
    } else {
        // VERIFICAR PERMISSÃO
        if ((!preg_match("/ $function /", $api_allowed_functions)) && (!preg_match("/ALL_FUNCTIONS/", $api_allowed_functions))) {
            // RESPOSTA DE ERRO
            $response = array(
                'result' => 'ERROR',
                'result_reason' => 'auth USER DOES NOT HAVE PERMISSION TO USE THIS FUNCTION',
                'details' => array(
                    'user' => $user,
                    'function' => $function,
                    'api_allowed_functions' => $api_allowed_functions
                )
            );
            header('Content-Type: application/json');
            header('HTTP/1.1 403 Forbidden');
            echo json_encode($response);
            // REGISTRO DE LOG
            api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, $response['result'], $response['result_reason'], $source, $data);
            exit;
        } 
        ##################################################################

        else {
            // VERIFICAR USUÁRIO
            $stmt = "SELECT count(*) from vicidial_users where user='$user' and vdc_agent_api_access='1' and modify_leads IN('1','2','3','4') and user_level > 7 and active='Y';";
            $rslt = mysql_to_mysqli($stmt, $link);
            $row = mysqli_fetch_row($rslt);
            $modify_leads = $row[0];

            if ($modify_leads < 1) {
                // RESPOSTA DE ERRO
                $response = array(
                    'result' => 'ERROR',
                    'result_reason' => 'add_lead USER DOES NOT HAVE PERMISSION TO ADD LEADS TO THE SYSTEM',
                    'details' => array(
                        'user' => $user,
                        'modify_leads' => $modify_leads
                    )
                );
                header('Content-Type: application/json');
                header('HTTP/1.1 403 Forbidden');
                echo json_encode($response);
                // REGISTRO DE LOG
                api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, $response['result'], $response['result_reason'], $source, $data);
                exit;

                 
            } 
            ##################################################################
            else {
                // CONTINUAR COM OUTRAS VERIFICAÇÕES E LÓGICA...
                // Exemplo de verificação de lista permitida
                if ($api_list_restrict > 0) {
                    if (!preg_match("/ $list_id /", $allowed_lists)) {
                        $response = array(
                            'result' => 'ERROR',
                            'result_reason' => 'add_lead NOT AN ALLOWED LIST ID',
                            'details' => array(
                                'data' => "$phone_number|$list_id"
                            )
                        );
                        header('Content-Type: application/json');
                        header('HTTP/1.1 400 Bad Request');
                        echo json_encode($response);
                        api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, $response['result'], $response['result_reason'], $source, "$phone_number|$list_id");
                        exit;
                    }
                }
                // Exemplo de verificação de existência de lista
                if (preg_match("/Y/i", $list_exists_check)) {
                    $stmt = "SELECT count(*) from vicidial_lists where list_id='$list_id';";
                    $rslt = mysql_to_mysqli($stmt, $link);
                    $row = mysqli_fetch_row($rslt);
                    $list_exists_count = $row[0];
                    if ($list_exists_count < 1) {
                        $response = array(
                            'result' => 'ERROR',
                            'result_reason' => 'add_lead NOT A DEFINED LIST ID, LIST EXISTS CHECK ENABLED',
                            'details' => array(
                                'data' => "$phone_number|$list_id"
                            )
                        );
                        header('Content-Type: application/json');
                        header('HTTP/1.1 400 Bad Request');
                        echo json_encode($response);
                        api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, $response['result'], $response['result_reason'], $source, "$phone_number|$list_id");
                        exit;
                    }
                }
                // CONTINUAR COM OUTRAS VERIFICAÇÕES E LÓGICA...
            }
        }
    }
}
// Verificar e definir valores padrão
if (strlen($gender) < 1) {
    $gender = 'U';
}
if (strlen($rank) < 1) {
    $rank = '0';
}
if (strlen($list_id) < 3) {
    $list_id = '999';
}
if (strlen($phone_code) < 1) {
    $phone_code = '1';
}

// Verificação de prefixo NANPA
if (($nanpa_ac_prefix_check == 'Y') || (preg_match("/NANPA/i", $tz_method))) {
    $stmt = "SELECT count(*) from vicidial_nanpa_prefix_codes;";
    $rslt = mysql_to_mysqli($stmt, $link);
    $row = mysqli_fetch_row($rslt);
    $vicidial_nanpa_prefix_codes_count = $row[0];
    if ($vicidial_nanpa_prefix_codes_count < 10) {
        $nanpa_ac_prefix_check = 'N';
        $tz_method = preg_replace("/NANPA/", '', $tz_method);

        $response = array(
            'result' => 'NOTICE',
            'result_reason' => 'add_lead NANPA options disabled, NANPA prefix data not loaded',
            'details' => array(
                'vicidial_nanpa_prefix_codes_count' => $vicidial_nanpa_prefix_codes_count,
                'user' => $user
            )
        );
        header('Content-Type: application/json');
        header('HTTP/1.1 200 OK');
        echo json_encode($response);
        $data = "$inserted_alt_phones|$lead_id";
        api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, 'NOTICE', 'add_lead NANPA options disabled, NANPA prefix data not loaded', $source, $data);
    }
}

// Verificação de número de telefone
$valid_number = 1;
if ((strlen($phone_number) < 6) || (strlen($phone_number) > 16)) {
    $valid_number = 0;
    $result_reason = "add_lead INVALID PHONE NUMBER LENGTH";
}
if (($usacan_prefix_check == 'Y') && ($valid_number > 0)) {
    $USprefix = substr($phone_number, 3, 1);
    if ($DB > 0) {
        echo json_encode(['DEBUG' => "add_lead prefix check - $USprefix|$phone_number"]);
    }
    if ($USprefix < 2) {
        $valid_number = 0;
        $result_reason = "add_lead INVALID PHONE NUMBER PREFIX";
    }
}
if (($usacan_areacode_check == 'Y') && ($valid_number > 0)) {
    $phone_areacode = substr($phone_number, 0, 3);
    $stmt = "SELECT count(*) from vicidial_phone_codes where areacode='$phone_areacode' and country_code='1';";
    if ($DB > 0) {
        echo json_encode(['DEBUG' => "add_lead areacode check query - $stmt"]);
    }
    $rslt = mysql_to_mysqli($stmt, $link);
    $row = mysqli_fetch_row($rslt);
    $valid_number = $row[0];
    if (($valid_number < 1) || (strlen($phone_number) > 10) || (strlen($phone_number) < 10)) {
        $result_reason = "add_lead INVALID PHONE NUMBER AREACODE";
    }
}
if (($nanpa_ac_prefix_check == 'Y') && ($valid_number > 0)) {
    $phone_areacode = substr($phone_number, 0, 3);
    $phone_prefix = substr($phone_number, 3, 3);
    $stmt = "SELECT count(*) from vicidial_nanpa_prefix_codes where areacode='$phone_areacode' and prefix='$phone_prefix';";
    if ($DB > 0) {
        echo json_encode(['DEBUG' => "add_lead areacode check query - $stmt"]);
    }
    $rslt = mysql_to_mysqli($stmt, $link);
    $row = mysqli_fetch_row($rslt);
    $valid_number = $row[0];
    if ($valid_number < 1) {
        $result_reason = "add_lead INVALID PHONE NUMBER NANPA AREACODE PREFIX";
    }
}
if ($valid_number < 1) {
    $response = array(
        'result' => 'ERROR',
        'result_reason' => $result_reason,
        'details' => array(
            'phone_number' => $phone_number,
            'user' => $user
        )
    );
    header('Content-Type: application/json');
    header('HTTP/1.1 400 Bad Request');
    echo json_encode($response);
    $data = "$phone_number";
    api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, 'ERROR', $result_reason, $source, $data);
    exit;
} else {
    // Verificação de estado se habilitada
    if (($lookup_state == 'Y') && (strlen($state) < 1)) {
        $phone_areacode = substr($phone_number, 0, 3);
        $stmt = "SELECT state from vicidial_phone_codes where country_code='$phone_code' and areacode='$phone_areacode';";
        $rslt = mysql_to_mysqli($stmt, $link);
        $vpc_recs = mysqli_num_rows($rslt);
        if ($vpc_recs > 0) {
            $row = mysqli_fetch_row($rslt);
            $state = $row[0];
        }
    }
}
### START checking for DNC if defined ###
if ( ($dnc_check == 'Y') or ($dnc_check == 'AREACODE') )
{
if ($DB>0) {echo "DEBUG: Checking for system DNC\n";}
if ($dnc_check == 'AREACODE')
    {
    $phone_areacode = substr($phone_number, 0, 3);
    $phone_areacode .= "XXXXXXX";
    $stmt="SELECT count(*) from vicidial_dnc where phone_number IN('$phone_number','$phone_areacode');";
    }
else
    {$stmt="SELECT count(*) from vicidial_dnc where phone_number='$phone_number';";}
if ($DB>0) {echo "DEBUG: add_lead query - $stmt\n";}
$rslt=mysql_to_mysqli($stmt, $link);
$row=mysqli_fetch_row($rslt);
$dnc_found=$row[0];

if ($dnc_found > 0) 
    {
    $result = 'ERROR';
    $result_reason = "add_lead PHONE NUMBER IN DNC";
    echo "$result: $result_reason - $phone_number|$user\n";
    $data = "$phone_number";
    api_log($link,$api_logging,$api_script,$user,$agent_user,$function,$value,$result,$result_reason,$source,$data);
    exit;
    }
}
if ( ($campaign_dnc_check == 'Y') or ($campaign_dnc_check == 'AREACODE') )
{
if ($DB>0) {echo "DEBUG: Checking for campaign DNC\n";}

$stmt="SELECT use_other_campaign_dnc from vicidial_campaigns where campaign_id='$campaign_id';";
$rslt=mysql_to_mysqli($stmt, $link);
$row=mysqli_fetch_row($rslt);
$use_other_campaign_dnc =	$row[0];
$temp_campaign_id = $campaign_id;
if (strlen($use_other_campaign_dnc) > 0) {$temp_campaign_id = $use_other_campaign_dnc;}

if ($campaign_dnc_check == 'AREACODE')
    {
    $phone_areacode = substr($phone_number, 0, 3);
    $phone_areacode .= "XXXXXXX";
    $stmt="SELECT count(*) from vicidial_campaign_dnc where phone_number IN('$phone_number','$phone_areacode') and campaign_id='$temp_campaign_id';";
    }
else
    {$stmt="SELECT count(*) from vicidial_campaign_dnc where phone_number='$phone_number' and campaign_id='$temp_campaign_id';";}
if ($DB>0) {echo "DEBUG: add_lead query - $stmt\n";}
$rslt=mysql_to_mysqli($stmt, $link);
$row=mysqli_fetch_row($rslt);
$dnc_found=$row[0];

if ($dnc_found > 0) 
    {
    $result = 'ERROR';
    $result_reason = "add_lead PHONE NUMBER IN CAMPAIGN DNC";
    echo "$result: $result_reason - $phone_number|$campaign_id|$user\n";
    $data = "$phone_number|$campaign_id";
    api_log($link,$api_logging,$api_script,$user,$agent_user,$function,$value,$result,$result_reason,$source,$data);
    exit;
    }
}
### END checking for DNC if defined ###

### START checking for duplicate if defined ###
$multidaySQL='';
if (preg_match("/1DAY|2DAY|3DAY|7DAY|14DAY|15DAY|21DAY|28DAY|30DAY|60DAY|90DAY|180DAY|360DAY/i",$duplicate_check))
    {
    $day_val=30;
    if (preg_match("/1DAY/i",$duplicate_check)) {$day_val=1;}
    if (preg_match("/2DAY/i",$duplicate_check)) {$day_val=2;}
    if (preg_match("/3DAY/i",$duplicate_check)) {$day_val=3;}
    if (preg_match("/7DAY/i",$duplicate_check)) {$day_val=7;}
    if (preg_match("/14DAY/i",$duplicate_check)) {$day_val=14;}
    if (preg_match("/15DAY/i",$duplicate_check)) {$day_val=15;}
    if (preg_match("/21DAY/i",$duplicate_check)) {$day_val=21;}
    if (preg_match("/28DAY/i",$duplicate_check)) {$day_val=28;}
    if (preg_match("/30DAY/i",$duplicate_check)) {$day_val=30;}
    if (preg_match("/60DAY/i",$duplicate_check)) {$day_val=60;}
    if (preg_match("/90DAY/i",$duplicate_check)) {$day_val=90;}
    if (preg_match("/180DAY/i",$duplicate_check)) {$day_val=180;}
    if (preg_match("/360DAY/i",$duplicate_check)) {$day_val=360;}
    $multiday = date("Y-m-d H:i:s", mktime(date("H"),date("i"),date("s"),date("m"),date("d")-$day_val,date("Y")));
    $multidaySQL = "and entry_date > \"$multiday\"";
    if ($DB > 0) {echo "DEBUG: $day_val day SQL: |$multidaySQL|";}
    }

### find list of list_ids in this campaign for the CAMP duplicate check options
if (preg_match("/CAMP/i",$duplicate_check)) # find lists within campaign
    {
    $stmt="SELECT campaign_id from vicidial_lists where list_id='$list_id';";
    $rslt=mysql_to_mysqli($stmt, $link);
    $ci_recs = mysqli_num_rows($rslt);
    if ($ci_recs > 0)
        {
        $row=mysqli_fetch_row($rslt);
        $duplicate_camp =	$row[0];

        $stmt="select list_id from vicidial_lists where campaign_id='$duplicate_camp';";
        $rslt=mysql_to_mysqli($stmt, $link);
        $li_recs = mysqli_num_rows($rslt);
        if ($li_recs > 0)
            {
            $L=0;
            while ($li_recs > $L)
                {
                $row=mysqli_fetch_row($rslt);
                $duplicate_lists .=	"'$row[0]',";
                $L++;
                }
            $duplicate_lists = preg_replace('/,$/i', '',$duplicate_lists);
            }
        }
    }

if (preg_match("/DUPLIST/i",$duplicate_check)) # duplicate check within list
    {
    if ($DB>0) {echo "DEBUG: Checking for duplicates - DUPLIST\n";}
    $duplicate_found=0;
    $stmt="SELECT lead_id,list_id from vicidial_list where phone_number='$phone_number' and list_id='$list_id' $multidaySQL limit 1;";
    $rslt=mysql_to_mysqli($stmt, $link);
    $pc_recs = mysqli_num_rows($rslt);
    if ($pc_recs > 0)
        {
        $duplicate_found=1;
        $row=mysqli_fetch_row($rslt);
        $duplicate_lead_id =	$row[0];
        $duplicate_lead_list =	$row[1];
        }

    if ($duplicate_found > 0) 
        {
        $result = 'ERROR';
        $result_reason = "add_lead DUPLICATE PHONE NUMBER IN LIST";
        $data = "$phone_number|$list_id|$duplicate_lead_id";
        echo "$result: $result_reason - $data\n";
        api_log($link,$api_logging,$api_script,$user,$agent_user,$function,$value,$result,$result_reason,$source,$data);
        exit;
        }
    }
if (preg_match("/DUPCAMP/i",$duplicate_check)) # duplicate check within campaign lists
    {
    if ($DB>0) {echo "DEBUG: Checking for duplicates - DUPCAMP - $duplicate_lists\n";}
    $duplicate_found=0;
    $stmt="SELECT lead_id,list_id from vicidial_list where phone_number='$phone_number' and list_id IN($duplicate_lists) $multidaySQL limit 1;";
    $rslt=mysql_to_mysqli($stmt, $link);
    $pc_recs = mysqli_num_rows($rslt);
    if ($pc_recs > 0)
        {
        $duplicate_found=1;
        $row=mysqli_fetch_row($rslt);
        $duplicate_lead_id =	$row[0];
        $duplicate_lead_list =	$row[1];
        }

    if ($duplicate_found > 0) 
        {
        $result = 'ERROR';
        $result_reason = "add_lead DUPLICATE PHONE NUMBER IN CAMPAIGN LISTS";
        $data = "$phone_number|$list_id|$duplicate_lead_id|$duplicate_lead_list";
        echo "$result: $result_reason - $data\n";
        api_log($link,$api_logging,$api_script,$user,$agent_user,$function,$value,$result,$result_reason,$source,$data);
        exit;
        }
    }
if (preg_match("/DUPSYS/i",$duplicate_check)) # duplicate check within entire system
    {
    if ($DB>0) {echo "DEBUG: Checking for duplicates - DUPSYS\n";}
    $duplicate_found=0;
    $stmt="SELECT lead_id,list_id from vicidial_list where phone_number='$phone_number' $multidaySQL limit 1;";
    $rslt=mysql_to_mysqli($stmt, $link);
    $pc_recs = mysqli_num_rows($rslt);
    if ($pc_recs > 0)
        {
        $duplicate_found=1;
        $row=mysqli_fetch_row($rslt);
        $duplicate_lead_id =	$row[0];
        $duplicate_lead_list =	$row[1];
        }

    if ($duplicate_found > 0) 
        {
        $result = 'ERROR';
        $result_reason = "add_lead DUPLICATE PHONE NUMBER IN SYSTEM";
        $data = "$phone_number|$list_id|$duplicate_lead_id|$duplicate_lead_list";
        echo "$result: $result_reason - $data\n";
        api_log($link,$api_logging,$api_script,$user,$agent_user,$function,$value,$result,$result_reason,$source,$data);
        exit;
        }
    }

if (preg_match("/DUPPHONEALTLIST/i",$duplicate_check)) # duplicate check for phone against phone_number and alt_phone within list
    {
    if ($DB>0) {echo "DEBUG: Checking for duplicates - DUPPHONEALTLIST\n";}
    $duplicate_found=0;
    $stmt="SELECT lead_id,list_id,phone_number,alt_phone from vicidial_list where ( (phone_number='$phone_number') or (alt_phone='$phone_number') ) and list_id='$list_id' $multidaySQL limit 1;";
    $rslt=mysql_to_mysqli($stmt, $link);
    $pc_recs = mysqli_num_rows($rslt);
    if ($DB>0) {echo "DEBUG 2: |$pc_recs|$stmt|\n";}
    if ($pc_recs > 0)
        {
        $duplicate_found=1;
        $row=mysqli_fetch_row($rslt);
        $duplicate_lead_id =		$row[0];
        $duplicate_lead_list =		$row[1];
        $duplicate_phone_number =	$row[2];
        $duplicate_alt_phone =		$row[3];
        if ($phone_number == $duplicate_phone_number) {$duplicate_type='PHONE';}
        else {$duplicate_type='ALT';}
        }

    if ($duplicate_found > 0) 
        {
        $result = 'ERROR';
        $result_reason = "add_lead DUPLICATE PHONE NUMBER IN LIST";
        $data = "$phone_number|$list_id|$duplicate_lead_id|$duplicate_type";
        echo "$result: $result_reason - $data\n";
        api_log($link,$api_logging,$api_script,$user,$agent_user,$function,$value,$result,$result_reason,$source,$data);
        exit;
        }
    }
if (preg_match("/DUPPHONEALTCAMP/i",$duplicate_check)) # duplicate check for phone against phone_number and alt_phone within campaign lists
    {
    if ($DB>0) {echo "DEBUG: Checking for duplicates - DUPPHONEALTCAMP - $duplicate_lists\n";}
    $duplicate_found=0;
    $stmt="SELECT lead_id,list_id,phone_number,alt_phone from vicidial_list where ( (phone_number='$phone_number') or (alt_phone='$phone_number') ) and list_id IN($duplicate_lists) $multidaySQL limit 1;";
    $rslt=mysql_to_mysqli($stmt, $link);
    $pc_recs = mysqli_num_rows($rslt);
    if ($DB>0) {echo "DEBUG 2: |$pc_recs|$stmt|\n";}
    if ($pc_recs > 0)
        {
        $duplicate_found=1;
        $row=mysqli_fetch_row($rslt);
        $duplicate_lead_id =		$row[0];
        $duplicate_lead_list =		$row[1];
        $duplicate_phone_number =	$row[2];
        $duplicate_alt_phone =		$row[3];
        if ($phone_number == $duplicate_phone_number) {$duplicate_type='PHONE';}
        else {$duplicate_type='ALT';}
        }

    if ($duplicate_found > 0) 
        {
        $result = 'ERROR';
        $result_reason = "add_lead DUPLICATE PHONE NUMBER IN CAMPAIGN LISTS";
        $data = "$phone_number|$list_id|$duplicate_lead_id|$duplicate_lead_list|$duplicate_type";
        echo "$result: $result_reason - $data\n";
        api_log($link,$api_logging,$api_script,$user,$agent_user,$function,$value,$result,$result_reason,$source,$data);
        exit;
        }
    }
if (preg_match("/DUPPHONEALTSYS/i",$duplicate_check)) # duplicate check for phone against phone_number and alt_phone within entire system
    {
    if ($DB>0) {echo "DEBUG: Checking for duplicates - DUPPHONEALTSYS\n";}
    $duplicate_found=0;
    $stmt="SELECT lead_id,list_id,phone_number,alt_phone from vicidial_list where ( (phone_number='$phone_number') or (alt_phone='$phone_number') ) $multidaySQL limit 1;";
    $rslt=mysql_to_mysqli($stmt, $link);
    $pc_recs = mysqli_num_rows($rslt);
    if ($DB>0) {echo "DEBUG 2: |$pc_recs|$stmt|\n";}
    if ($pc_recs > 0)
        {
        $duplicate_found=1;
        $row=mysqli_fetch_row($rslt);
        $duplicate_lead_id =		$row[0];
        $duplicate_lead_list =		$row[1];
        $duplicate_phone_number =	$row[2];
        $duplicate_alt_phone =		$row[3];
        if ($phone_number == $duplicate_phone_number) {$duplicate_type='PHONE';}
        else {$duplicate_type='ALT';}
        }

    if ($duplicate_found > 0) 
        {
        $result = 'ERROR';
        $result_reason = "add_lead DUPLICATE PHONE NUMBER IN SYSTEM";
        $data = "$phone_number|$list_id|$duplicate_lead_id|$duplicate_lead_list|$duplicate_type";
        echo "$result: $result_reason - $data\n";
        api_log($link,$api_logging,$api_script,$user,$agent_user,$function,$value,$result,$result_reason,$source,$data);
        exit;
        }
    }

if (preg_match("/DUPTITLEALTPHONELIST/i",$duplicate_check)) # duplicate title/alt_phone check within list
    {
    if ($DB>0) {echo "DEBUG: Checking for duplicates - DUPTITLEALTPHONELIST\n";}
    $duplicate_found=0;
    $stmt="SELECT lead_id,list_id from vicidial_list where title='$title' and alt_phone='$alt_phone' and list_id='$list_id' $multidaySQL limit 1;";
    $rslt=mysql_to_mysqli($stmt, $link);
    $pc_recs = mysqli_num_rows($rslt);
    if ($pc_recs > 0)
        {
        $duplicate_found=1;
        $row=mysqli_fetch_row($rslt);
        $duplicate_lead_id =	$row[0];
        $duplicate_lead_list =	$row[1];
        }

    if ($duplicate_found > 0) 
        {
        $result = 'ERROR';
        $result_reason = "add_lead DUPLICATE TITLE ALT_PHONE IN LIST";
        $data = "$title|$alt_phone|$list_id|$duplicate_lead_id";
        echo "$result: $result_reason - $data\n";
        api_log($link,$api_logging,$api_script,$user,$agent_user,$function,$value,$result,$result_reason,$source,$data);
        exit;
        }
    }
if (preg_match("/DUPTITLEALTPHONECAMP/i",$duplicate_check)) # duplicate title/alt_phone check within campaign lists
    {
    if ($DB>0) {echo "DEBUG: Checking for duplicates - DUPTITLEALTPHONECAMP\n";}
    $duplicate_found=0;
    $stmt="SELECT lead_id,list_id from vicidial_list where title='$title' and alt_phone='$alt_phone' and list_id IN($duplicate_lists) $multidaySQL limit 1;";
    $rslt=mysql_to_mysqli($stmt, $link);
    $pc_recs = mysqli_num_rows($rslt);
    if ($pc_recs > 0)
        {
        $duplicate_found=1;
        $row=mysqli_fetch_row($rslt);
        $duplicate_lead_id =	$row[0];
        $duplicate_lead_list =	$row[1];
        }

    if ($duplicate_found > 0) 
        {
        $result = 'ERROR';
        $result_reason = "add_lead DUPLICATE TITLE ALT_PHONE IN CAMPAIGN LISTS";
        $data = "$title|$alt_phone|$list_id|$duplicate_lead_id|$duplicate_lead_list";
        echo "$result: $result_reason - $data\n";
        api_log($link,$api_logging,$api_script,$user,$agent_user,$function,$value,$result,$result_reason,$source,$data);
        exit;
        }
    }
if (preg_match("/DUPTITLEALTPHONESYS/i",$duplicate_check)) # duplicate title/alt_phone check within entire system
    {
    if ($DB>0) {echo "DEBUG: Checking for duplicates - DUPTITLEALTPHONESYS\n";}
    $duplicate_found=0;
    $stmt="SELECT lead_id,list_id from vicidial_list where title='$title' and alt_phone='$alt_phone' $multidaySQL limit 1;";
    $rslt=mysql_to_mysqli($stmt, $link);
    $pc_recs = mysqli_num_rows($rslt);
    if ($pc_recs > 0)
        {
        $duplicate_found=1;
        $row=mysqli_fetch_row($rslt);
        $duplicate_lead_id =	$row[0];
        $duplicate_lead_list =	$row[1];
        }

    if ($duplicate_found > 0) 
        {
        $result = 'ERROR';
        $result_reason = "add_lead DUPLICATE TITLE ALT_PHONE IN SYSTEM";
        $data = "$title|$alt_phone|$list_id|$duplicate_lead_id|$duplicate_lead_list";
        echo "$result: $result_reason - $data\n";
        api_log($link,$api_logging,$api_script,$user,$agent_user,$function,$value,$result,$result_reason,$source,$data);
        exit;
        }
    }
if (preg_match("/DUPNAMEPHONELIST/i",$duplicate_check)) # duplicate name/phone check within list
    {
    if ($DB>0) {echo "DEBUG: Checking for duplicates - DUPNAMEPHONELIST\n";}
    $duplicate_found=0;
    $stmt="SELECT lead_id,list_id from vicidial_list where first_name='$first_name' and last_name='$last_name' and phone_number='$phone_number' $multidaySQL and list_id='$list_id' limit 1;";
    $rslt=mysql_to_mysqli($stmt, $link);
    $pc_recs = mysqli_num_rows($rslt);
    if ($pc_recs > 0)
        {
        $duplicate_found=1;
        $row=mysqli_fetch_row($rslt);
        $duplicate_lead_id =	$row[0];
        $duplicate_lead_list =	$row[1];
        }

    if ($duplicate_found > 0) 
        {
        $result = 'ERROR';
        $result_reason = "add_lead DUPLICATE NAME PHONE IN LIST";
        $data = "$first_name|$last_name|$phone_number|$list_id|$duplicate_lead_id";
        echo "$result: $result_reason - $data\n";
        api_log($link,$api_logging,$api_script,$user,$agent_user,$function,$value,$result,$result_reason,$source,$data);
        exit;
        }
    }
if (preg_match("/DUPNAMEPHONECAMP/i",$duplicate_check)) # duplicate name/phone check within campaign lists
    {
    if ($DB>0) {echo "DEBUG: Checking for duplicates - DUPNAMEPHONECAMP\n";}
    $duplicate_found=0;
    $stmt="SELECT lead_id,list_id from vicidial_list where first_name='$first_name' and last_name='$last_name' and phone_number='$phone_number' and list_id IN($duplicate_lists) $multidaySQL limit 1;";
    $rslt=mysql_to_mysqli($stmt, $link);
    $pc_recs = mysqli_num_rows($rslt);
    if ($pc_recs > 0)
        {
        $duplicate_found=1;
        $row=mysqli_fetch_row($rslt);
        $duplicate_lead_id =	$row[0];
        $duplicate_lead_list =	$row[1];
        }

    if ($duplicate_found > 0) 
        {
        $result = 'ERROR';
        $result_reason = "add_lead DUPLICATE NAME PHONE IN CAMPAIGN LISTS";
        $data = "$first_name|$last_name|$phone_number|$list_id|$duplicate_lead_id|$duplicate_lead_list";
        echo "$result: $result_reason - $data\n";
        api_log($link,$api_logging,$api_script,$user,$agent_user,$function,$value,$result,$result_reason,$source,$data);
        exit;
        }
    }
if (preg_match("/DUPNAMEPHONESYS/i",$duplicate_check)) # duplicate name/phone check within entire system
    {
    if ($DB>0) {echo "DEBUG: Checking for duplicates - DUPNAMEPHONESYS\n";}
    $duplicate_found=0;
    $stmt="SELECT lead_id,list_id from vicidial_list where first_name='$first_name' and last_name='$last_name' and phone_number='$phone_number' $multidaySQL limit 1;";
    $rslt=mysql_to_mysqli($stmt, $link);
    $pc_recs = mysqli_num_rows($rslt);
    if ($pc_recs > 0)
        {
        $duplicate_found=1;
        $row=mysqli_fetch_row($rslt);
        $duplicate_lead_id =	$row[0];
        $duplicate_lead_list =	$row[1];
        }

    if ($duplicate_found > 0) 
        {
        $result = 'ERROR';
        $result_reason = "add_lead DUPLICATE NAME PHONE IN SYSTEM";
        $data = "$first_name|$last_name|$phone_number|$list_id|$duplicate_lead_id|$duplicate_lead_list";
        echo "$result: $result_reason - $data\n";
        api_log($link,$api_logging,$api_script,$user,$agent_user,$function,$value,$result,$result_reason,$source,$data);
        exit;
        }
    }
### END checking for duplicate if defined ###


### get current gmt_offset of the phone_number
$gmt_offset = lookup_gmt_api($phone_code,$USarea,$state,$LOCAL_GMT_OFF_STD,$Shour,$Smin,$Ssec,$Smon,$Smday,$Syear,$tz_method,$postal_code,$owner,$USprefix);

$new_status='NEW';
if ($callback == 'Y')
    {$new_status='CBHOLD';}
$entry_list_idSQL = ",entry_list_id='0'";
if (strlen($entry_list_id) > 0)
    {$entry_list_idSQL = ",entry_list_id='$entry_list_id'";}

### insert a new lead in the system with this phone number
$stmt = "INSERT INTO vicidial_list SET phone_code=\"$phone_code\",phone_number=\"$phone_number\",list_id=\"$list_id\",status=\"$new_status\",user=\"$user\",vendor_lead_code=\"$vendor_lead_code\",source_id=\"$source_id\",gmt_offset_now=\"$gmt_offset\",title=\"$title\",first_name=\"$first_name\",middle_initial=\"$middle_initial\",last_name=\"$last_name\",address1=\"$address1\",address2=\"$address2\",address3=\"$address3\",city=\"$city\",state=\"$state\",province=\"$province\",postal_code=\"$postal_code\",country_code=\"$country_code\",gender=\"$gender\",date_of_birth=\"$date_of_birth\",alt_phone=\"$alt_phone\",email=\"$email\",security_phrase=\"$security_phrase\",comments=\"$comments\",called_since_last_reset=\"N\",entry_date=\"$ENTRYdate\",last_local_call_time=\"$NOW_TIME\",rank=\"$rank\",owner=\"$owner\" $entry_list_idSQL;";
if ($DB>0) {echo "DEBUG: add_lead query - $stmt\n";}
$rslt=mysql_to_mysqli($stmt, $link);
$affected_rows = mysqli_affected_rows($link);
if ($affected_rows > 0)
    {
    $lead_id = mysqli_insert_id($link);

    $result = 'SUCCESS';
    $result_reason = "add_lead LEAD HAS BEEN ADDED";
    echo "$result: $result_reason - $phone_number|$list_id|$lead_id|$gmt_offset|$user\n";
    $data = "$phone_number|$list_id|$lead_id|$gmt_offset";
    api_log($link,$api_logging,$api_script,$user,$agent_user,$function,$value,$result,$result_reason,$source,$data);

    if (strlen($multi_alt_phones) > 5)
        {
        $map=$MT;  $ALTm_phone_code=$MT;  $ALTm_phone_number=$MT;  $ALTm_phone_note=$MT;
        $map = explode('!', $multi_alt_phones);
        $map_count = count($map);
        if ($DB>0) {echo "DEBUG: add_lead multi-al-entry - $a|$map_count|$multi_alt_phones\n";}
        $g++;
        $r=0;   $s=0;   $inserted_alt_phones=0;
        while ($r < $map_count)
            {
            $s++;
            $ncn=$MT;
            $ncn = explode('_', $map[$r]);
            print "$ncn[0]|$ncn[1]|$ncn[2]";

            if (strlen($forcephonecode) > 0)
                {$ALTm_phone_code[$r] =	$forcephonecode;}
            else
                {$ALTm_phone_code[$r] =		$ncn[1];}
            if (strlen($ALTm_phone_code[$r]) < 1)
                {$ALTm_phone_code[$r]='1';}
            $ALTm_phone_number[$r] =	$ncn[0];
            $ALTm_phone_note[$r] =		$ncn[2];
            $stmt = "INSERT INTO vicidial_list_alt_phones (lead_id,phone_code,phone_number,alt_phone_note,alt_phone_count) values('$lead_id','$ALTm_phone_code[$r]','$ALTm_phone_number[$r]','$ALTm_phone_note[$r]','$s');";
            if ($DB>0) {echo "DEBUG: add_lead query - $stmt\n";}
            $rslt=mysql_to_mysqli($stmt, $link);
            $Zaffected_rows = mysqli_affected_rows($link);
            $inserted_alt_phones = ($inserted_alt_phones + $Zaffected_rows);
            $r++;
            }
        $result = 'NOTICE';
        $result_reason = "add_lead MULTI-ALT-PHONE NUMBERS LOADED";
        echo "$result: $result_reason - $inserted_alt_phones|$lead_id|$user\n";
        $data = "$inserted_alt_phones|$lead_id";
        api_log($link,$api_logging,$api_script,$user,$agent_user,$function,$value,$result,$result_reason,$source,$data);
        }


        ### get current gmt_offset of the phone_number
				$gmt_offset = lookup_gmt_api($phone_code,$USarea,$state,$LOCAL_GMT_OFF_STD,$Shour,$Smin,$Ssec,$Smon,$Smday,$Syear,$tz_method,$postal_code,$owner,$USprefix);

				$new_status='NEW';
				if ($callback == 'Y')
					{$new_status='CBHOLD';}
				$entry_list_idSQL = ",entry_list_id='0'";
				if (strlen($entry_list_id) > 0)
					{$entry_list_idSQL = ",entry_list_id='$entry_list_id'";}


    
                    header('Content-Type: application/json');
                    
                    // Conexão com o banco de dados
                    $link = new mysqli("host", "user", "password", "database");
                    
                    if ($link->connect_error) {
                        http_response_code(500);
                        echo json_encode(["result" => "ERROR", "message" => "Connection failed: " . $link->connect_error]);
                        exit();
                    }
                    
                    // Dados recebidos via POST
                    $data = json_decode(file_get_contents('php://input'), true);
                    
                    $phone_code = $data['phone_code'];
                    $phone_number = $data['phone_number'];
                    $list_id = $data['list_id'];
                    $new_status = $data['status'];
                    $user = $data['user'];
                    $vendor_lead_code = $data['vendor_lead_code'];
                    $source_id = $data['source_id'];
                    $gmt_offset = $data['gmt_offset'];
                    $title = $data['title'];
                    $first_name = $data['first_name'];
                    $middle_initial = $data['middle_initial'];
                    $last_name = $data['last_name'];
                    $address1 = $data['address1'];
                    $address2 = $data['address2'];
                    $address3 = $data['address3'];
                    $city = $data['city'];
                    $state = $data['state'];
                    $province = $data['province'];
                    $postal_code = $data['postal_code'];
                    $country_code = $data['country_code'];
                    $gender = $data['gender'];
                    $date_of_birth = $data['date_of_birth'];
                    $alt_phone = $data['alt_phone'];
                    $email = $data['email'];
                    $security_phrase = $data['security_phrase'];
                    $comments = $data['comments'];
                    $entry_date = date('Y-m-d H:i:s');
                    $now_time = date('Y-m-d H:i:s');
                    $rank = $data['rank'];
                    $owner = $data['owner'];
                    
                    // Inserir novo lead
                    $stmt = $link->prepare("INSERT INTO vicidial_list (phone_code, phone_number, list_id, status, user, vendor_lead_code, source_id, gmt_offset_now, title, first_name, middle_initial, last_name, address1, address2, address3, city, state, province, postal_code, country_code, gender, date_of_birth, alt_phone, email, security_phrase, comments, called_since_last_reset, entry_date, last_local_call_time, rank, owner) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'N', ?, ?, ?, ?)");
                    $stmt->bind_param("sssssssssssssssssssssssssssssss", $phone_code, $phone_number, $list_id, $new_status, $user, $vendor_lead_code, $source_id, $gmt_offset, $title, $first_name, $middle_initial, $last_name, $address1, $address2, $address3, $city, $state, $province, $postal_code, $country_code, $gender, $date_of_birth, $alt_phone, $email, $security_phrase, $comments, $entry_date, $now_time, $rank, $owner);
                    
                    if ($stmt->execute()) {
                        $lead_id = $stmt->insert_id;
                        http_response_code(201); // Created
                        $response = [
                            "result" => "SUCCESS",
                            "message" => "Lead has been added",
                            "lead_id" => $lead_id,
                            "phone_number" => $phone_number,
                            "list_id" => $list_id,
                            "gmt_offset" => $gmt_offset,
                            "user" => $user
                        ];
                    } else {
                        http_response_code(500); // Internal Server Error
                        $response = [
                            "result" => "ERROR",
                            "message" => "Failed to add lead: " . $stmt->error
                        ];
                    }
                    
                    $stmt->close();
                    $link->close();
                    
                    echo json_encode($response);
                    

                    
header('Content-Type: application/json');

// Conexão com o banco de dados
$link = new mysqli("host", "user", "password", "database");

if ($link->connect_error) {
    http_response_code(500);
    echo json_encode(["result" => "ERROR", "message" => "Connection failed: " . $link->connect_error]);
    exit();
}

// Dados recebidos via POST
$data = json_decode(file_get_contents('php://input'), true);

$phone_code = $data['phone_code'];
$phone_number = $data['phone_number'];
$list_id = $data['list_id'];
$new_status = $data['status'];
$user = $data['user'];
$vendor_lead_code = $data['vendor_lead_code'];
$source_id = $data['source_id'];
$gmt_offset = $data['gmt_offset'];
$title = $data['title'];
$first_name = $data['first_name'];
$middle_initial = $data['middle_initial'];
$last_name = $data['last_name'];
$address1 = $data['address1'];
$address2 = $data['address2'];
$address3 = $data['address3'];
$city = $data['city'];
$state = $data['state'];
$province = $data['province'];
$postal_code = $data['postal_code'];
$country_code = $data['country_code'];
$gender = $data['gender'];
$date_of_birth = $data['date_of_birth'];
$alt_phone = $data['alt_phone'];
$email = $data['email'];
$security_phrase = $data['security_phrase'];
$comments = $data['comments'];
$entry_date = date('Y-m-d H:i:s');
$now_time = date('Y-m-d H:i:s');
$rank = $data['rank'];
$owner = $data['owner'];
$custom_fields = $data['custom_fields'];
$custom_fields_enabled = $data['custom_fields_enabled'];

// Inserir novo lead
$stmt = $link->prepare("INSERT INTO vicidial_list (phone_code, phone_number, list_id, status, user, vendor_lead_code, source_id, gmt_offset_now, title, first_name, middle_initial, last_name, address1, address2, address3, city, state, province, postal_code, country_code, gender, date_of_birth, alt_phone, email, security_phrase, comments, called_since_last_reset, entry_date, last_local_call_time, rank, owner) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'N', ?, ?, ?, ?)");
$stmt->bind_param("sssssssssssssssssssssssssssssss", $phone_code, $phone_number, $list_id, $new_status, $user, $vendor_lead_code, $source_id, $gmt_offset, $title, $first_name, $middle_initial, $last_name, $address1, $address2, $address3, $city, $state, $province, $postal_code, $country_code, $gender, $date_of_birth, $alt_phone, $email, $security_phrase, $comments, $entry_date, $now_time, $rank, $owner);

if ($stmt->execute()) {
    $lead_id = $stmt->insert_id;

    // Inserir campos personalizados
    if ($custom_fields == 'Y' && $custom_fields_enabled > 0) {
        $stmt = $link->prepare("SHOW TABLES LIKE ?");
        $custom_table = "custom_" . $list_id;
        $stmt->bind_param("s", $custom_table);
        $stmt->execute();
        $stmt->store_result();
        $tablecount_to_print = $stmt->num_rows;

        if ($tablecount_to_print > 0) {
            $stmt = $link->prepare("SELECT field_id, field_label, field_name, field_type, field_size, field_max, field_required, field_encrypt FROM vicidial_lists_fields WHERE list_id = ? AND field_duplicate != 'Y' ORDER BY field_rank, field_order, field_label");
            $stmt->bind_param("s", $list_id);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $field_name_id = $row['field_label'];
                $form_field_value = $data[$field_name_id] ?? '';

                $form_field_value = preg_replace("/\+/"," ",$form_field_value);
                $form_field_value = preg_replace("/;|\"/","",$form_field_value);
                $form_field_value = preg_replace("/\\b/","",$form_field_value);
                $form_field_value = preg_replace("/\\\\$/","",$form_field_value);

                $stmt_insert = $link->prepare("INSERT INTO custom_$list_id (lead_id, field_id, field_value) VALUES (?, ?, ?)");
                $stmt_insert->bind_param("iis", $lead_id, $row['field_id'], $form_field_value);
                $stmt_insert->execute();
            }
        }
    }

    http_response_code(201); // Created
    $response = [
        "result" => "SUCCESS",
        "message" => "Lead has been added",
        "lead_id" => $lead_id,
        "phone_number" => $phone_number,
        "list_id" => $list_id,
        "gmt_offset" => $gmt_offset,
        "user" => $user
    ];
} else {
    http_response_code(500); // Internal Server Error
    $response = [
        "result" => "ERROR",
        "message" => "Failed to add lead: " . $stmt->error
    ];
}

$stmt->close();
$link->close();

echo json_encode($response);

header('Content-Type: application/json');

// Conexão com o banco de dados
$link = new mysqli("host", "user", "password", "database");

if ($link->connect_error) {
    http_response_code(500);
    echo json_encode(["result" => "ERROR", "message" => "Connection failed: " . $link->connect_error]);
    exit();
}

// Dados recebidos via POST
$data = json_decode(file_get_contents('php://input'), true);

$phone_code = $data['phone_code'];
$phone_number = $data['phone_number'];
$list_id = $data['list_id'];
$new_status = $data['status'];
$user = $data['user'];
$vendor_lead_code = $data['vendor_lead_code'];
$source_id = $data['source_id'];
$gmt_offset = $data['gmt_offset'];
$title = $data['title'];
$first_name = $data['first_name'];
$middle_initial = $data['middle_initial'];
$last_name = $data['last_name'];
$address1 = $data['address1'];
$address2 = $data['address2'];
$address3 = $data['address3'];
$city = $data['city'];
$state = $data['state'];
$province = $data['province'];
$postal_code = $data['postal_code'];
$country_code = $data['country_code'];
$gender = $data['gender'];
$date_of_birth = $data['date_of_birth'];
$alt_phone = $data['alt_phone'];
$email = $data['email'];
$security_phrase = $data['security_phrase'];
$comments = $data['comments'];
$entry_date = date('Y-m-d H:i:s');
$now_time = date('Y-m-d H:i:s');
$rank = $data['rank'];
$owner = $data['owner'];
$custom_fields = $data['custom_fields'];
$custom_fields_enabled = $data['custom_fields_enabled'];
$add_to_hopper = $data['add_to_hopper'];
$hopper_local_call_time_check = $data['hopper_local_call_time_check'];
$hopper_priority = $data['hopper_priority'];

// Inserir novo lead
$stmt = $link->prepare("INSERT INTO vicidial_list (phone_code, phone_number, list_id, status, user, vendor_lead_code, source_id, gmt_offset_now, title, first_name, middle_initial, last_name, address1, address2, address3, city, state, province, postal_code, country_code, gender, date_of_birth, alt_phone, email, security_phrase, comments, called_since_last_reset, entry_date, last_local_call_time, rank, owner) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'N', ?, ?, ?, ?)");
$stmt->bind_param("sssssssssssssssssssssssssssssss", $phone_code, $phone_number, $list_id, $new_status, $user, $vendor_lead_code, $source_id, $gmt_offset, $title, $first_name, $middle_initial, $last_name, $address1, $address2, $address3, $city, $state, $province, $postal_code, $country_code, $gender, $date_of_birth, $alt_phone, $email, $security_phrase, $comments, $entry_date, $now_time, $rank, $owner);

if ($stmt->execute()) {
    $lead_id = $stmt->insert_id;

    // Inserir campos personalizados
    if ($custom_fields == 'Y' && $custom_fields_enabled > 0) {
        $stmt = $link->prepare("SHOW TABLES LIKE ?");
        $custom_table = "custom_" . $list_id;
        $stmt->bind_param("s", $custom_table);
        $stmt->execute();
        $stmt->store_result();
        $tablecount_to_print = $stmt->num_rows;

        if ($tablecount_to_print > 0) {
            $stmt = $link->prepare("SELECT field_id, field_label, field_name, field_type, field_size, field_max, field_required, field_encrypt FROM vicidial_lists_fields WHERE list_id = ? AND field_duplicate != 'Y' ORDER BY field_rank, field_order, field_label");
            $stmt->bind_param("s", $list_id);
            $stmt->execute();
            $result = $stmt->get_result();

            $CFinsert_SQL = '';
            while ($row = $result->fetch_assoc()) {
                $field_name_id = $row['field_label'];
                $form_field_value = $data[$field_name_id] ?? '';

                if ($row['field_type'] == 'DISPLAY' || $row['field_type'] == 'SCRIPT') {
                    $form_field_value = '----IGNORE----';
                } else {
                    if (!preg_match("/\|$field_name_id\|/", $vicidial_list_fields)) {
                        if (preg_match("/cf_encrypt/", $active_modules) && $row['field_encrypt'] == 'Y' && strlen($form_field_value) > 0) {
                            $form_field_value = base64_encode($form_field_value);
                            exec("../agc/aes.pl --encrypt --text=$form_field_value", $field_enc);
                            $form_field_value = preg_replace("/CRYPT: |\n|\r|\t/", '', implode('', $field_enc));
                        }
                        $CFinsert_SQL .= "$field_name_id=\"$form_field_value\",";
                    }
                }
            }

            if (strlen($CFinsert_SQL) > 3) {
                $CFinsert_SQL = rtrim($CFinsert_SQL, ',');
                $CFinsert_SQL = str_replace('"--BLANK--"', '""', $CFinsert_SQL);
                $custom_table_update_SQL = "INSERT INTO custom_$list_id SET lead_id='$lead_id',$CFinsert_SQL;";
                $stmt = $link->prepare($custom_table_update_SQL);
                $stmt->execute();
                $custom_insert_count = $stmt->affected_rows;

                if ($custom_insert_count > 0) {
                    $vl_table_entry_update_SQL = "UPDATE vicidial_list SET entry_list_id='$list_id' WHERE lead_id='$lead_id';";
                    $stmt = $link->prepare($vl_table_entry_update_SQL);
                    $stmt->execute();
                    $vl_table_entry_update_count = $stmt->affected_rows;

                    $result = 'NOTICE';
                    $result_reason = "add_lead CUSTOM FIELDS VALUES ADDED";
                    $data = "$phone_number|$lead_id|$list_id";
                    api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, $result, $result_reason, $source, $data);
                } else {
                    $result = 'NOTICE';
                    $result_reason = "add_lead CUSTOM FIELDS NOT ADDED, NO FIELDS TO UPDATE DEFINED";
                    $data = "$phone_number|$lead_id|$list_id";
                    api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, $result, $result_reason, $source, $data);
                }
            } else {
                $result = 'NOTICE';
                $result_reason = "add_lead CUSTOM FIELDS NOT ADDED, NO FIELDS DEFINED";
                $data = "$phone_number|$lead_id|$list_id";
                api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, $result, $result_reason, $source, $data);
            }
        } else {
            $result = 'NOTICE';
            $result_reason = "add_lead CUSTOM FIELDS NOT ADDED, NO CUSTOM FIELDS DEFINED FOR THIS LIST";
            $data = "$phone_number|$lead_id|$list_id";
            api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, $result, $result_reason, $source, $data);
        }
    } else {
        $result = 'NOTICE';
        $result_reason = "add_lead CUSTOM FIELDS NOT ADDED, CUSTOM FIELDS DISABLED";
        $data = "$phone_number|$lead_id|$custom_fields|$custom_fields_enabled";
        api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, $result, $result_reason, $source, $data);
    }

    // BEGIN add to hopper section  
if ($add_to_hopper == 'Y') {  
    $dialable = 1;  
   
    $stmt = "SELECT vicidial_campaigns.local_call_time, vicidial_lists.local_call_time, vicidial_campaigns.campaign_id FROM vicidial_campaigns, vicidial_lists WHERE list_id = '$list_id' AND vicidial_campaigns.campaign_id = vicidial_lists.campaign_id;";  
    $rslt = mysql_to_mysqli($stmt, $link);  
    $row = mysqli_fetch_row($rslt);  
    $local_call_time = $row[0];  
    $list_local_call_time = $row[1];  
    $VD_campaign_id = $row[2];  
   
    if ($DB > 0) {  
       echo "DEBUG call time: |$local_call_time|$list_local_call_time|$VD_campaign_id|";  
    }  
    if (($list_local_call_time != '') && (!preg_match("/^campaign$/i", $list_local_call_time))) {  
       $local_call_time = $list_local_call_time;  
    }  
   
    if ($hopper_local_call_time_check == 'Y') {  
       // Chamar função para determinar se o lead é dialável  
       $dialable = dialable_gmt($DB, $link, $local_call_time, $gmt_offset, $state);  
    }  
    if ($dialable < 1) {  
       // Retornar erro 400 (Bad Request) se o lead não for dialável  
       http_response_code(400);  
       echo json_encode([  
         'result' => 'ERROR',  
         'result_reason' => "add_lead NOT ADDED TO HOPPER, OUTSIDE OF LOCAL TIME",  
         'data' => "$phone_number|$lead_id|$gmt_offset|$dialable|$state"  
       ]);  
       api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, 'ERROR', "add_lead NOT ADDED TO HOPPER, OUTSIDE OF LOCAL TIME", $source, "$phone_number|$lead_id|$gmt_offset|$dialable|$state");  
       exit;  
    } else {  
       // Inserir registro no vicidial_hopper para tentativa de chamada do alt_phone  
       $stmt = "INSERT INTO vicidial_hopper SET lead_id = '$lead_id', campaign_id = '$VD_campaign_id', status = 'READY', list_id = '$list_id', gmt_offset_now = '$gmt_offset', state = '$state', user = '', priority = '$hopper_priority', source = 'P', vendor_lead_code = \"$vendor_lead_code\";";  
       if ($DB > 0) {  
         echo "DEBUG: add_lead query - $stmt\n";  
       }  
       $rslt = mysql_to_mysqli($stmt, $link);  
       $Haffected_rows = mysqli_affected_rows($link);  
       if ($Haffected_rows > 0) {  
         $hopper_id = mysqli_insert_id($link);  
   
         // Retornar sucesso 201 (Created)  
         http_response_code(201);  
         echo json_encode([  
            'result' => 'NOTICE',  
            'result_reason' => "add_lead ADDED TO HOPPER",  
            'data' => "$phone_number|$lead_id|$hopper_id"  
         ]);  
         api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, 'NOTICE', "add_lead ADDED TO HOPPER", $source, "$phone_number|$lead_id|$hopper_id");  
       } else {  
         // Retornar erro 500 (Internal Server Error) se o registro não for inserido  
         http_response_code(500);  
         echo json_encode([  
            'result' => 'ERROR',  
            'result_reason' => "add_lead NOT ADDED TO HOPPER",  
            'data' => "$phone_number|$lead_id|$stmt"  
         ]);  
         api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, 'ERROR', "add_lead NOT ADDED TO HOPPER", $source, "$phone_number|$lead_id|$stmt");  
       }  
    }  
 }  
 // END add to hopper section

 // BEGIN scheduled callback section  
if ($callback == 'Y') {  
    // Verificar se a campanha é válida  
    $stmt = "SELECT count(*) FROM vicidial_campaigns WHERE campaign_id = '$campaign_id';";  
    $rslt = mysql_to_mysqli($stmt, $link);  
    $camp_recs = mysqli_num_rows($rslt);  
    if ($camp_recs > 0) {  
       $row = mysqli_fetch_row($rslt);  
       $camp_count = $row[0];  
    }  
   
    if ($camp_count > 0) {  
       // Verificar se o usuário é válido  
       $valid_callback = 0;  
       $user_group = '';  
       if ($callback_type == 'USERONLY') {  
         $stmt = "SELECT user_group FROM vicidial_users WHERE user = '$callback_user';";  
         $rslt = mysql_to_mysqli($stmt, $link);  
         $user_recs = mysqli_num_rows($rslt);  
         if ($user_recs > 0) {  
            $row = mysqli_fetch_row($rslt);  
            $user_group = $row[0];  
            $valid_callback++;  
         } else {  
            // Retornar erro 400 (Bad Request) se o usuário não for válido  
            http_response_code(400);  
            echo json_encode([  
               'result' => 'ERROR',  
               'result_reason' => "add_lead SCHEDULED CALLBACK NOT ADDED, USER NOT VALID",  
               'data' => "$lead_id|$campaign_id|$callback_user|$callback_type"  
            ]);  
            api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, 'ERROR', "add_lead SCHEDULED CALLBACK NOT ADDED, USER NOT VALID", $source, "$lead_id|$campaign_id|$callback_user|$callback_type");  
            exit;  
         }  
       } else {  
         $callback_type = 'ANYONE';  
         $callback_user = '';  
         $valid_callback++;  
       }  
   
       if ($valid_callback > 0) {  
         // Adicionar callback  
         if ($callback_datetime == 'NOW') {  
            $callback_datetime = $NOW_TIME;  
         }  
         if (preg_match("/\dDAYS$/i", $callback_datetime)) {  
            $callback_days = preg_replace('/[^0-9]/', '', $callback_datetime);  
            $callback_datetime = date("Y-m-d H:i:s", mktime(date("H"), date("i"), date("s"), date("m"), date("d") + $callback_days, date("Y")));  
         }  
         if (strlen($callback_status) < 1) {  
            $callback_status = 'CALLBK';  
         }  
   
         $stmt = "INSERT INTO vicidial_callbacks (lead_id, list_id, campaign_id, status, entry_time, callback_time, user, recipient, comments, user_group, lead_status) VALUES ('$lead_id', '$list_id', '$campaign_id', 'ACTIVE', '$NOW_TIME', '$callback_datetime', '$callback_user', '$callback_type', '$callback_comments', '$user_group', '$callback_status');";  
         if ($DB > 0) {  
            echo "DEBUG: add_lead query - $stmt\n";  
         }  
         $rslt = mysql_to_mysqli($stmt, $link);  
         $CBaffected_rows = mysqli_affected_rows($link);  
   
         // Retornar sucesso 200 (OK)  
         http_response_code(200);  
         echo json_encode([  
            'result' => 'NOTICE',  
            'result_reason' => "add_lead SCHEDULED CALLBACK ADDED",  
            'data' => "$lead_id|$campaign_id|$callback_datetime|$callback_type|$callback_user|$callback_status"  
         ]);  
         api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, 'NOTICE', "add_lead SCHEDULED CALLBACK ADDED", $source, "$lead_id|$campaign_id|$callback_datetime|$callback_type|$callback_user|$callback_status");  
       }  
    } else {  
       // Retornar erro 404 (Not Found) se a campanha não for válida  
       http_response_code(404);  
       echo json_encode([  
         'result' => 'ERROR',  
         'result_reason' => "add_lead SCHEDULED CALLBACK NOT ADDED, CAMPAIGN NOT VALID",  
         'data' => "$lead_id|$campaign_id"  
       ]);  
       api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, 'ERROR', "add_lead SCHEDULED CALLBACK NOT ADDED, CAMPAIGN NOT VALID", $source, "$lead_id|$campaign_id");  
       exit;  
    }  
 }  
 // END scheduled callback section

 else {  
    // Retornar erro 500 (Internal Server Error) se o lead não for adicionado  
    http_response_code(500);  
    echo json_encode([  
       'result' => 'ERROR',  
       'result_reason' => "add_lead LEAD HAS NOT BEEN ADDED",  
       'data' => "$phone_number|$list_id|$stmt"  
    ]);  
    api_log($link, $api_logging, $api_script, $user, $agent_user, $function, $value, 'ERROR', "add_lead LEAD HAS NOT BEEN ADDED", $source, "$phone_number|$list_id|$stmt");  

}
?>

