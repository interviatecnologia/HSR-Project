<?php
# lead.php
# 
# Copyright (C) 2024  Intervia Tecnologia <intervia.tecnologia@gmail.com>    LICENSE: AGPLv2
#
# This script is designed as an API(Application Programming Interface) to allow
# other programs to interact with all non-agent-screen VICIDIAL functions
# 
# required variables:
#  - $user
#  - $pass
#  - $function - ('add_lead','update_lead','version','sounds_list','moh_list','vm_list','blind_monitor','agent_ingroup_info','add_list',etc...)
#  - $source - ('vtiger','webform','adminweb')
#  - $format - ('text','debug')
# optional callback variables for add_lead/update_lead
#  - $callback -	('Y,'N','REMOVE')
#  - $callback_status -	('CALLBK','CBXYZ',...)
#  - $callback_datetime -	('YYYY-MM-DD+HH:MM:SS','NOW','273DAYS')
#  - $callback_type -	('USERONLY','ANYONE')
#  - $callback_user -	('6666','1001',...)
#  - $callback_comments - ('Comments go here',...)

# CHANGELOG:
# 240923-1722 - Ajustando Restfull
#

$version = '2.14-193';
$build = '240824-1626';
$php_script='non_agent_api.php';
$api_url_log = 0;
$camp_lead_order_random=1;

$startMS = microtime();

require("dbconnect_mysqli.php");
require("functions.php");

### If you have globals turned off uncomment these lines
if (isset($_GET["user"]))						{$user=$_GET["user"];}
	elseif (isset($_POST["user"]))				{$user=$_POST["user"];}
if (isset($_GET["pass"]))						{$pass=$_GET["pass"];}
	elseif (isset($_POST["pass"]))				{$pass=$_POST["pass"];}
if (isset($_GET["function"]))					{$function=$_GET["function"];}
	elseif (isset($_POST["function"]))			{$function=$_POST["function"];}
if (isset($_GET["format"]))						{$format=$_GET["format"];}
	elseif (isset($_POST["format"]))			{$format=$_POST["format"];}
if (isset($_GET["list_id"]))					{$list_id=$_GET["list_id"];}
	elseif (isset($_POST["list_id"]))			{$list_id=$_POST["list_id"];}
if (isset($_GET["phone_code"]))					{$phone_code=$_GET["phone_code"];}
	elseif (isset($_POST["phone_code"]))		{$phone_code=$_POST["phone_code"];}
if (isset($_GET["update_phone_number"]))		  {$update_phone_number=$_GET["update_phone_number"];}
	elseif (isset($_POST["update_phone_number"])) {$update_phone_number=$_POST["update_phone_number"];}
if (isset($_GET["phone_number"]))				{$phone_number=$_GET["phone_number"];}
	elseif (isset($_POST["phone_number"]))		{$phone_number=$_POST["phone_number"];}
if (isset($_GET["vendor_lead_code"]))			{$vendor_lead_code=$_GET["vendor_lead_code"];}
	elseif (isset($_POST["vendor_lead_code"]))	{$vendor_lead_code=$_POST["vendor_lead_code"];}
if (isset($_GET["source_id"]))					{$source_id=$_GET["source_id"];}
	elseif (isset($_POST["source_id"]))			{$source_id=$_POST["source_id"];}
if (isset($_GET["gmt_offset_now"]))				{$gmt_offset_now=$_GET["gmt_offset_now"];}
	elseif (isset($_POST["gmt_offset_now"]))	{$gmt_offset_now=$_POST["gmt_offset_now"];}
if (isset($_GET["title"]))						{$title=$_GET["title"];}
	elseif (isset($_POST["title"]))				{$title=$_POST["title"];}
if (isset($_GET["first_name"]))					{$first_name=$_GET["first_name"];}
	elseif (isset($_POST["first_name"]))		{$first_name=$_POST["first_name"];}
if (isset($_GET["middle_initial"]))				{$middle_initial=$_GET["middle_initial"];}
	elseif (isset($_POST["middle_initial"]))	{$middle_initial=$_POST["middle_initial"];}
if (isset($_GET["last_name"]))					{$last_name=$_GET["last_name"];}
	elseif (isset($_POST["last_name"]))			{$last_name=$_POST["last_name"];}
if (isset($_GET["address1"]))					{$address1=$_GET["address1"];}
	elseif (isset($_POST["address1"]))			{$address1=$_POST["address1"];}
if (isset($_GET["address2"]))					{$address2=$_GET["address2"];}
	elseif (isset($_POST["address2"]))			{$address2=$_POST["address2"];}
if (isset($_GET["address3"]))					{$address3=$_GET["address3"];}
	elseif (isset($_POST["address3"]))			{$address3=$_POST["address3"];}
if (isset($_GET["city"]))						{$city=$_GET["city"];}
	elseif (isset($_POST["city"]))				{$city=$_POST["city"];}
if (isset($_GET["state"]))						{$state=$_GET["state"];}
	elseif (isset($_POST["state"]))				{$state=$_POST["state"];}
if (isset($_GET["province"]))					{$province=$_GET["province"];}
	elseif (isset($_POST["province"]))			{$province=$_POST["province"];}
if (isset($_GET["postal_code"]))				{$postal_code=$_GET["postal_code"];}
	elseif (isset($_POST["postal_code"]))		{$postal_code=$_POST["postal_code"];}
if (isset($_GET["country_code"]))				{$country_code=$_GET["country_code"];}
	elseif (isset($_POST["country_code"]))		{$country_code=$_POST["country_code"];}
if (isset($_GET["gender"]))						{$gender=$_GET["gender"];}
	elseif (isset($_POST["gender"]))			{$gender=$_POST["gender"];}
if (isset($_GET["date_of_birth"]))				{$date_of_birth=$_GET["date_of_birth"];}
	elseif (isset($_POST["date_of_birth"]))		{$date_of_birth=$_POST["date_of_birth"];}
if (isset($_GET["alt_phone"]))					{$alt_phone=$_GET["alt_phone"];}
	elseif (isset($_POST["alt_phone"]))			{$alt_phone=$_POST["alt_phone"];}
if (isset($_GET["email"]))						{$email=$_GET["email"];}
	elseif (isset($_POST["email"]))				{$email=$_POST["email"];}
if (isset($_GET["security_phrase"]))			{$security_phrase=$_GET["security_phrase"];}
	elseif (isset($_POST["security_phrase"]))	{$security_phrase=$_POST["security_phrase"];}
if (isset($_GET["comments"]))					{$comments=$_GET["comments"];}
	elseif (isset($_POST["comments"]))			{$comments=$_POST["comments"];}
if (isset($_GET["dnc_check"]))					{$dnc_check=$_GET["dnc_check"];}
	elseif (isset($_POST["dnc_check"]))			{$dnc_check=$_POST["dnc_check"];}
if (isset($_GET["campaign_dnc_check"]))				{$campaign_dnc_check=$_GET["campaign_dnc_check"];}
	elseif (isset($_POST["campaign_dnc_check"]))	{$campaign_dnc_check=$_POST["campaign_dnc_check"];}
if (isset($_GET["add_to_hopper"]))				{$add_to_hopper=$_GET["add_to_hopper"];}
	elseif (isset($_POST["add_to_hopper"]))		{$add_to_hopper=$_POST["add_to_hopper"];}
if (isset($_GET["hopper_priority"]))			{$hopper_priority=$_GET["hopper_priority"];}
	elseif (isset($_POST["hopper_priority"]))	{$hopper_priority=$_POST["hopper_priority"];}
if (isset($_GET["hopper_local_call_time_check"]))			{$hopper_local_call_time_check=$_GET["hopper_local_call_time_check"];}
	elseif (isset($_POST["hopper_local_call_time_check"]))	{$hopper_local_call_time_check=$_POST["hopper_local_call_time_check"];}
if (isset($_GET["campaign_id"]))				{$campaign_id=$_GET["campaign_id"];}
	elseif (isset($_POST["campaign_id"]))		{$campaign_id=$_POST["campaign_id"];}
if (isset($_GET["multi_alt_phones"]))			{$multi_alt_phones=$_GET["multi_alt_phones"];}
	elseif (isset($_POST["multi_alt_phones"]))	{$multi_alt_phones=$_POST["multi_alt_phones"];}
if (isset($_GET["source"]))						{$source=$_GET["source"];}
	elseif (isset($_POST["source"]))			{$source=$_POST["source"];}
if (isset($_GET["phone_login"]))				{$phone_login=$_GET["phone_login"];}
	elseif (isset($_POST["phone_login"]))		{$phone_login=$_POST["phone_login"];}
if (isset($_GET["session_id"]))					{$session_id=$_GET["session_id"];}
	elseif (isset($_POST["session_id"]))		{$session_id=$_POST["session_id"];}
if (isset($_GET["server_ip"]))					{$server_ip=$_GET["server_ip"];}
	elseif (isset($_POST["server_ip"]))			{$server_ip=$_POST["server_ip"];}
if (isset($_GET["stage"]))						{$stage=$_GET["stage"];}
	elseif (isset($_POST["stage"]))				{$stage=$_POST["stage"];}
if (isset($_GET["DB"]))							{$DB=$_GET["DB"];}
	elseif (isset($_POST["DB"]))				{$DB=$_POST["DB"];}
if (isset($_GET["rank"]))						{$rank=$_GET["rank"];}
	elseif (isset($_POST["rank"]))				{$rank=$_POST["rank"];}
if (isset($_GET["owner"]))						{$owner=$_GET["owner"];}
	elseif (isset($_POST["owner"]))				{$owner=$_POST["owner"];}
if (isset($_GET["agent_user"]))					{$agent_user=$_GET["agent_user"];}
	elseif (isset($_POST["agent_user"]))		{$agent_user=$_POST["agent_user"];}
if (isset($_GET["duplicate_check"]))			{$duplicate_check=$_GET["duplicate_check"];}
	elseif (isset($_POST["duplicate_check"]))	{$duplicate_check=$_POST["duplicate_check"];}
if (isset($_GET["custom_fields"]))				{$custom_fields=$_GET["custom_fields"];}
	elseif (isset($_POST["custom_fields"]))		{$custom_fields=$_POST["custom_fields"];}
if (isset($_GET["search_method"]))				{$search_method=$_GET["search_method"];}
	elseif (isset($_POST["search_method"]))		{$search_method=$_POST["search_method"];}
if (isset($_GET["insert_if_not_found"]))			{$insert_if_not_found=$_GET["insert_if_not_found"];}
	elseif (isset($_POST["insert_if_not_found"]))	{$insert_if_not_found=$_POST["insert_if_not_found"];}
if (isset($_GET["records"]))					{$records=$_GET["records"];}
	elseif (isset($_POST["records"]))			{$records=$_POST["records"];}
if (isset($_GET["search_location"]))			{$search_location=$_GET["search_location"];}
	elseif (isset($_POST["search_location"]))	{$search_location=$_POST["search_location"];}
if (isset($_GET["status"]))						{$status=$_GET["status"];}
	elseif (isset($_POST["status"]))			{$status=$_POST["status"];}
if (isset($_GET["statuses"]))						{$statuses=$_GET["statuses"];}
	elseif (isset($_POST["statuses"]))			{$statuses=$_POST["statuses"];}
if (isset($_GET["categories"]))			{$categories=$_GET["categories"];}
	elseif (isset($_POST["categories"]))	{$categories=$_POST["categories"];}
if (isset($_GET["user_field"]))					{$user_field=$_GET["user_field"];}
	elseif (isset($_POST["user_field"]))		{$user_field=$_POST["user_field"];}
if (isset($_GET["list_id_field"]))				{$list_id_field=$_GET["list_id_field"];}
	elseif (isset($_POST["list_id_field"]))		{$list_id_field=$_POST["list_id_field"];}
if (isset($_GET["lead_id"]))					{$lead_id=$_GET["lead_id"];}
	elseif (isset($_POST["lead_id"]))			{$lead_id=$_POST["lead_id"];}
if (isset($_GET["no_update"]))					{$no_update=$_GET["no_update"];}
	elseif (isset($_POST["no_update"]))			{$no_update=$_POST["no_update"];}
if (isset($_GET["delete_lead"]))				{$delete_lead=$_GET["delete_lead"];}
	elseif (isset($_POST["delete_lead"]))		{$delete_lead=$_POST["delete_lead"];}
if (isset($_GET["called_count"]))				{$called_count=$_GET["called_count"];}
	elseif (isset($_POST["called_count"]))		{$called_count=$_POST["called_count"];}
if (isset($_GET["date"]))						{$date=$_GET["date"];}
	elseif (isset($_POST["date"]))				{$date=$_POST["date"];}
if (isset($_GET["query_date"]))						{$query_date=$_GET["query_date"];}
	elseif (isset($_POST["query_date"]))				{$query_date=$_POST["query_date"];}
if (isset($_GET["query_time"]))						{$query_time=$_GET["query_time"];}
	elseif (isset($_POST["query_time"]))				{$query_time=$_POST["query_time"];}
if (isset($_GET["end_date"]))						{$end_date=$_GET["end_date"];}
	elseif (isset($_POST["end_date"]))				{$end_date=$_POST["end_date"];}
if (isset($_GET["end_time"]))						{$end_time=$_GET["end_time"];}
	elseif (isset($_POST["end_time"]))				{$end_time=$_POST["end_time"];}
if (isset($_GET["header"]))						{$header=$_GET["header"];}
	elseif (isset($_POST["header"]))			{$header=$_POST["header"];}
if (isset($_GET["agent_pass"]))					{$agent_pass=$_GET["agent_pass"];}
	elseif (isset($_POST["agent_pass"]))		{$agent_pass=$_POST["agent_pass"];}
if (isset($_GET["agent_user_level"]))			{$agent_user_level=$_GET["agent_user_level"];}
	elseif (isset($_POST["agent_user_level"]))	{$agent_user_level=$_POST["agent_user_level"];}
if (isset($_GET["agent_full_name"]))			{$agent_full_name=$_GET["agent_full_name"];}
	elseif (isset($_POST["agent_full_name"]))	{$agent_full_name=$_POST["agent_full_name"];}
if (isset($_GET["agent_user_group"]))			{$agent_user_group=$_GET["agent_user_group"];}
	elseif (isset($_POST["agent_user_group"]))	{$agent_user_group=$_POST["agent_user_group"];}
if (isset($_GET["phone_pass"]))				{$phone_pass=$_GET["phone_pass"];}
	elseif (isset($_POST["phone_pass"]))	{$phone_pass=$_POST["phone_pass"];}
if (isset($_GET["hotkeys_active"]))				{$hotkeys_active=$_GET["hotkeys_active"];}
	elseif (isset($_POST["hotkeys_active"]))	{$hotkeys_active=$_POST["hotkeys_active"];}
if (isset($_GET["voicemail_id"]))			{$voicemail_id=$_GET["voicemail_id"];}
	elseif (isset($_POST["voicemail_id"]))	{$voicemail_id=$_POST["voicemail_id"];}
if (isset($_GET["email"]))					{$email=$_GET["email"];}
	elseif (isset($_POST["email"]))			{$email=$_POST["email"];}
if (isset($_GET["custom_one"]))				{$custom_one=$_GET["custom_one"];}
	elseif (isset($_POST["custom_one"]))	{$custom_one=$_POST["custom_one"];}
if (isset($_GET["custom_two"]))				{$custom_two=$_GET["custom_two"];}
	elseif (isset($_POST["custom_two"]))	{$custom_two=$_POST["custom_two"];}
if (isset($_GET["custom_three"]))			{$custom_three=$_GET["custom_three"];}
	elseif (isset($_POST["custom_three"]))	{$custom_three=$_POST["custom_three"];}
if (isset($_GET["custom_four"]))			{$custom_four=$_GET["custom_four"];}
	elseif (isset($_POST["custom_four"]))	{$custom_four=$_POST["custom_four"];}
if (isset($_GET["custom_five"]))			{$custom_five=$_GET["custom_five"];}
	elseif (isset($_POST["custom_five"]))	{$custom_five=$_POST["custom_five"];}
if (isset($_GET["extension"]))			{$extension=$_GET["extension"];}
	elseif (isset($_POST["extension"]))	{$extension=$_POST["extension"];}
if (isset($_GET["dialplan_number"]))			{$dialplan_number=$_GET["dialplan_number"];}
	elseif (isset($_POST["dialplan_number"]))	{$dialplan_number=$_POST["dialplan_number"];}
if (isset($_GET["protocol"]))			{$protocol=$_GET["protocol"];}
	elseif (isset($_POST["protocol"]))	{$protocol=$_POST["protocol"];}
if (isset($_GET["registration_password"]))			{$registration_password=$_GET["registration_password"];}
	elseif (isset($_POST["registration_password"]))	{$registration_password=$_POST["registration_password"];}
if (isset($_GET["phone_full_name"]))			{$phone_full_name=$_GET["phone_full_name"];}
	elseif (isset($_POST["phone_full_name"]))	{$phone_full_name=$_POST["phone_full_name"];}
if (isset($_GET["local_gmt"]))			{$local_gmt=$_GET["local_gmt"];}
	elseif (isset($_POST["local_gmt"]))	{$local_gmt=$_POST["local_gmt"];}
if (isset($_GET["outbound_cid"]))			{$outbound_cid=$_GET["outbound_cid"];}
	elseif (isset($_POST["outbound_cid"]))	{$outbound_cid=$_POST["outbound_cid"];}
if (isset($_GET["phone_context"]))			{$phone_context=$_GET["phone_context"];}
	elseif (isset($_POST["phone_context"]))	{$phone_context=$_POST["phone_context"];}
if (isset($_GET["list_name"]))			{$list_name=$_GET["list_name"];}
	elseif (isset($_POST["list_name"]))	{$list_name=$_POST["list_name"];}
if (isset($_GET["active"]))				{$active=$_GET["active"];}
	elseif (isset($_POST["active"]))	{$active=$_POST["active"];}
if (isset($_GET["script"]))				{$script=$_GET["script"];}
	elseif (isset($_POST["script"]))	{$script=$_POST["script"];}
if (isset($_GET["am_message"]))				{$am_message=$_GET["am_message"];}
	elseif (isset($_POST["am_message"]))	{$am_message=$_POST["am_message"];}
if (isset($_GET["drop_inbound_group"]))				{$drop_inbound_group=$_GET["drop_inbound_group"];}
	elseif (isset($_POST["drop_inbound_group"]))	{$drop_inbound_group=$_POST["drop_inbound_group"];}
if (isset($_GET["web_form_address"]))			{$web_form_address=$_GET["web_form_address"];}
	elseif (isset($_POST["web_form_address"]))	{$web_form_address=$_POST["web_form_address"];}
if (isset($_GET["web_form_address_two"]))			{$web_form_address_two=$_GET["web_form_address_two"];}
	elseif (isset($_POST["web_form_address_two"]))	{$web_form_address_two=$_POST["web_form_address_two"];}
if (isset($_GET["web_form_address_three"]))			{$web_form_address_three=$_GET["web_form_address_three"];}
	elseif (isset($_POST["web_form_address_three"]))	{$web_form_address_three=$_POST["web_form_address_three"];}
if (isset($_GET["reset_list"]))				{$reset_list=$_GET["reset_list"];}
	elseif (isset($_POST["reset_list"]))	{$reset_list=$_POST["reset_list"];}
if (isset($_GET["delete_list"]))			{$delete_list=$_GET["delete_list"];}
	elseif (isset($_POST["delete_list"]))	{$delete_list=$_POST["delete_list"];}
if (isset($_GET["delete_leads"]))			{$delete_leads=$_GET["delete_leads"];}
	elseif (isset($_POST["delete_leads"]))	{$delete_leads=$_POST["delete_leads"];}
if (isset($_GET["reset_time"]))				{$reset_time=$_GET["reset_time"];}
	elseif (isset($_POST["reset_time"]))	{$reset_time=$_POST["reset_time"];}
if (isset($_GET["uniqueid"]))			{$uniqueid=$_GET["uniqueid"];}
	elseif (isset($_POST["uniqueid"]))	{$uniqueid=$_POST["uniqueid"];}
if (isset($_GET["tz_method"]))			{$tz_method=$_GET["tz_method"];}
	elseif (isset($_POST["tz_method"]))	{$tz_method=$_POST["tz_method"];}
if (isset($_GET["reset_lead"]))				{$reset_lead=$_GET["reset_lead"];}
	elseif (isset($_POST["reset_lead"]))	{$reset_lead=$_POST["reset_lead"];}
if (isset($_GET["usacan_areacode_check"]))			{$usacan_areacode_check=$_GET["usacan_areacode_check"];}
	elseif (isset($_POST["usacan_areacode_check"]))	{$usacan_areacode_check=$_POST["usacan_areacode_check"];}
if (isset($_GET["usacan_prefix_check"]))			{$usacan_prefix_check=$_GET["usacan_prefix_check"];}
	elseif (isset($_POST["usacan_prefix_check"]))	{$usacan_prefix_check=$_POST["usacan_prefix_check"];}
if (isset($_GET["delete_phone"]))			{$delete_phone=$_GET["delete_phone"];}
	elseif (isset($_POST["delete_phone"]))	{$delete_phone=$_POST["delete_phone"];}
if (isset($_GET["alias_id"]))			{$alias_id=$_GET["alias_id"];}
	elseif (isset($_POST["alias_id"]))	{$alias_id=$_POST["alias_id"];}
if (isset($_GET["phone_logins"]))			{$phone_logins=$_GET["phone_logins"];}
	elseif (isset($_POST["phone_logins"]))	{$phone_logins=$_POST["phone_logins"];}
if (isset($_GET["alias_name"]))				{$alias_name=$_GET["alias_name"];}
	elseif (isset($_POST["alias_name"]))	{$alias_name=$_POST["alias_name"];}
if (isset($_GET["delete_alias"]))			{$delete_alias=$_GET["delete_alias"];}
	elseif (isset($_POST["delete_alias"]))	{$delete_alias=$_POST["delete_alias"];}
if (isset($_GET["callback"]))			{$callback=$_GET["callback"];}
	elseif (isset($_POST["callback"]))	{$callback=$_POST["callback"];}
if (isset($_GET["callback_status"]))			{$callback_status=$_GET["callback_status"];}
	elseif (isset($_POST["callback_status"]))	{$callback_status=$_POST["callback_status"];}
if (isset($_GET["callback_datetime"]))			{$callback_datetime=$_GET["callback_datetime"];}
	elseif (isset($_POST["callback_datetime"]))	{$callback_datetime=$_POST["callback_datetime"];}
if (isset($_GET["callback_type"]))			{$callback_type=$_GET["callback_type"];}
	elseif (isset($_POST["callback_type"]))	{$callback_type=$_POST["callback_type"];}
if (isset($_GET["callback_user"]))			{$callback_user=$_GET["callback_user"];}
	elseif (isset($_POST["callback_user"]))	{$callback_user=$_POST["callback_user"];}
if (isset($_GET["callback_comments"]))			{$callback_comments=$_GET["callback_comments"];}
	elseif (isset($_POST["callback_comments"]))	{$callback_comments=$_POST["callback_comments"];}
if (isset($_GET["admin_user_group"]))			{$admin_user_group=$_GET["admin_user_group"];}
	elseif (isset($_POST["admin_user_group"]))	{$admin_user_group=$_POST["admin_user_group"];}
if (isset($_GET["datetime_start"]))				{$datetime_start=$_GET["datetime_start"];}
	elseif (isset($_POST["datetime_start"]))	{$datetime_start=$_POST["datetime_start"];}
if (isset($_GET["datetime_end"]))			{$datetime_end=$_GET["datetime_end"];}
	elseif (isset($_POST["datetime_end"]))	{$datetime_end=$_POST["datetime_end"];}
if (isset($_GET["time_format"]))			{$time_format=$_GET["time_format"];}
	elseif (isset($_POST["time_format"]))	{$time_format=$_POST["time_format"];}
if (isset($_GET["group_alias_id"]))				{$group_alias_id=$_GET["group_alias_id"];}
	elseif (isset($_POST["group_alias_id"]))	{$group_alias_id=$_POST["group_alias_id"];}
if (isset($_GET["group_alias_name"]))			{$group_alias_name=$_GET["group_alias_name"];}
	elseif (isset($_POST["group_alias_name"]))	{$group_alias_name=$_POST["group_alias_name"];}
if (isset($_GET["caller_id_number"]))			{$caller_id_number=$_GET["caller_id_number"];}
	elseif (isset($_POST["caller_id_number"]))	{$caller_id_number=$_POST["caller_id_number"];}
if (isset($_GET["caller_id_name"]))				{$caller_id_name=$_GET["caller_id_name"];}
	elseif (isset($_POST["caller_id_name"]))	{$caller_id_name=$_POST["caller_id_name"];}
if (isset($_GET["user_groups"]))				{$user_groups=$_GET["user_groups"];}
	elseif (isset($_POST["user_groups"]))		{$user_groups=$_POST["user_groups"];}
if (isset($_GET["in_groups"]))				{$in_groups=$_GET["in_groups"];}
	elseif (isset($_POST["in_groups"]))		{$in_groups=$_POST["in_groups"];}
if (isset($_GET["did_ids"]))				{$did_ids=$_GET["did_ids"];}
	elseif (isset($_POST["did_ids"]))		{$did_ids=$_POST["did_ids"];}
if (isset($_GET["did_patterns"]))				{$did_patterns=$_GET["did_patterns"];}
	elseif (isset($_POST["did_patterns"]))		{$did_patterns=$_POST["did_patterns"];}
if (isset($_GET["call_id"]))				{$call_id=$_GET["call_id"];}
	elseif (isset($_POST["call_id"]))		{$call_id=$_POST["call_id"];}
if (isset($_GET["group"]))					{$group=$_GET["group"];}
	elseif (isset($_POST["group"]))			{$group=$_POST["group"];}
if (isset($_GET["expiration_date"]))			{$expiration_date=$_GET["expiration_date"];}
	elseif (isset($_POST["expiration_date"]))	{$expiration_date=$_POST["expiration_date"];}
if (isset($_GET["nanpa_ac_prefix_check"]))			{$nanpa_ac_prefix_check=$_GET["nanpa_ac_prefix_check"];}
	elseif (isset($_POST["nanpa_ac_prefix_check"]))	{$nanpa_ac_prefix_check=$_POST["nanpa_ac_prefix_check"];}
if (isset($_GET["detail"]))				{$detail=$_GET["detail"];}
	elseif (isset($_POST["detail"]))	{$detail=$_POST["detail"];}
if (isset($_GET["delete_user"]))			{$delete_user=$_GET["delete_user"];}
	elseif (isset($_POST["delete_user"]))	{$delete_user=$_POST["delete_user"];}
if (isset($_GET["campaign_rank"]))			{$campaign_rank=$_GET["campaign_rank"];}
	elseif (isset($_POST["campaign_rank"]))	{$campaign_rank=$_POST["campaign_rank"];}
if (isset($_GET["campaign_grade"]))				{$campaign_grade=$_GET["campaign_grade"];}
	elseif (isset($_POST["campaign_grade"]))	{$campaign_grade=$_POST["campaign_grade"];}
if (isset($_GET["local_call_time"]))				{$local_call_time=$_GET["local_call_time"];}
	elseif (isset($_POST["local_call_time"]))	{$local_call_time=$_POST["local_call_time"];}
if (isset($_GET["camp_rg_only"]))				{$camp_rg_only=$_GET["camp_rg_only"];}
	elseif (isset($_POST["camp_rg_only"]))		{$camp_rg_only=$_POST["camp_rg_only"];}
if (isset($_GET["wrapup_seconds_override"]))			{$wrapup_seconds_override=$_GET["wrapup_seconds_override"];}
	elseif (isset($_POST["wrapup_seconds_override"]))	{$wrapup_seconds_override=$_POST["wrapup_seconds_override"];}
if (isset($_GET["entry_list_id"]))			{$entry_list_id=$_GET["entry_list_id"];}
	elseif (isset($_POST["entry_list_id"]))	{$entry_list_id=$_POST["entry_list_id"];}
if (isset($_GET["show_sub_status"]))			{$show_sub_status=$_GET["show_sub_status"];}
	elseif (isset($_POST["show_sub_status"]))	{$show_sub_status=$_POST["show_sub_status"];}
if (isset($_GET["campaigns"]))			{$campaigns=$_GET["campaigns"];}
	elseif (isset($_POST["campaigns"]))	{$campaigns=$_POST["campaigns"];}
if (isset($_GET["ingroups"]))			{$ingroups=$_GET["ingroups"];}
	elseif (isset($_POST["ingroups"]))	{$ingroups=$_POST["ingroups"];}
if (isset($_GET["campaign_name"]))			{$campaign_name=$_GET["campaign_name"];}
	elseif (isset($_POST["campaign_name"]))	{$campaign_name=$_POST["campaign_name"];}
if (isset($_GET["did_ids"]))						{$did_ids=$_GET["did_ids"];}
	elseif (isset($_POST["did_ids"]))				{$did_ids=$_POST["did_ids"];}
if (isset($_GET["did_pattern"]))						{$did_pattern=$_GET["did_pattern"];}
	elseif (isset($_POST["did_pattern"]))				{$did_pattern=$_POST["did_pattern"];}
if (isset($_GET["users"]))						{$users=$_GET["users"];}
	elseif (isset($_POST["users"]))				{$users=$_POST["users"];}
if (isset($_GET["auto_dial_level"]))			{$auto_dial_level=$_GET["auto_dial_level"];}
	elseif (isset($_POST["auto_dial_level"]))	{$auto_dial_level=$_POST["auto_dial_level"];}
if (isset($_GET["adaptive_maximum_level"]))				{$adaptive_maximum_level=$_GET["adaptive_maximum_level"];}
	elseif (isset($_POST["adaptive_maximum_level"]))	{$adaptive_maximum_level=$_POST["adaptive_maximum_level"];}
if (isset($_GET["campaign_vdad_exten"]))			{$campaign_vdad_exten=$_GET["campaign_vdad_exten"];}
	elseif (isset($_POST["campaign_vdad_exten"]))	{$campaign_vdad_exten=$_POST["campaign_vdad_exten"];}
if (isset($_GET["hopper_level"]))			{$hopper_level=$_GET["hopper_level"];}
	elseif (isset($_POST["hopper_level"]))	{$hopper_level=$_POST["hopper_level"];}
if (isset($_GET["reset_hopper"]))			{$reset_hopper=$_GET["reset_hopper"];}
	elseif (isset($_POST["reset_hopper"]))	{$reset_hopper=$_POST["reset_hopper"];}
if (isset($_GET["dial_method"]))			{$dial_method=$_GET["dial_method"];}
	elseif (isset($_POST["dial_method"]))	{$dial_method=$_POST["dial_method"];}
if (isset($_GET["dial_timeout"]))			{$dial_timeout=$_GET["dial_timeout"];}
	elseif (isset($_POST["dial_timeout"]))	{$dial_timeout=$_POST["dial_timeout"];}
if (isset($_GET["field_name"]))				{$field_name=$_GET["field_name"];}
	elseif (isset($_POST["field_name"]))	{$field_name=$_POST["field_name"];}
if (isset($_GET["lookup_state"]))			{$lookup_state=$_GET["lookup_state"];}
	elseif (isset($_POST["lookup_state"]))	{$lookup_state=$_POST["lookup_state"];}
if (isset($_GET["type"]))				{$type=$_GET["type"];}
	elseif (isset($_POST["type"]))		{$type=$_POST["type"];}
if (isset($_GET["status_breakdown"]))						{$status_breakdown=$_GET["status_breakdown"];}
	elseif (isset($_POST["status_breakdown"]))				{$status_breakdown=$_POST["status_breakdown"];}
if (isset($_GET["show_percentages"]))						{$show_percentages=$_GET["show_percentages"];}
	elseif (isset($_POST["show_percentages"]))				{$show_percentages=$_POST["show_percentages"];}
if (isset($_GET["file_download"]))						{$file_download=$_GET["file_download"];}
	elseif (isset($_POST["file_download"]))				{$file_download=$_POST["file_download"];}
if (isset($_GET["force_entry_list_id"]))			{$force_entry_list_id=$_GET["force_entry_list_id"];}
	elseif (isset($_POST["force_entry_list_id"]))	{$force_entry_list_id=$_POST["force_entry_list_id"];}
if (isset($_GET["lead_filter_id"]))				{$lead_filter_id=$_GET["lead_filter_id"];}
	elseif (isset($_POST["lead_filter_id"]))	{$lead_filter_id=$_POST["lead_filter_id"];}
if (isset($_GET["agent_choose_ingroups"]))			{$agent_choose_ingroups=$_GET["agent_choose_ingroups"];}
	elseif (isset($_POST["agent_choose_ingroups"]))	{$agent_choose_ingroups=$_POST["agent_choose_ingroups"];}
if (isset($_GET["agent_choose_blended"]))			{$agent_choose_blended=$_GET["agent_choose_blended"];}
	elseif (isset($_POST["agent_choose_blended"]))	{$agent_choose_blended=$_POST["agent_choose_blended"];}
if (isset($_GET["closer_default_blended"]))				{$closer_default_blended=$_GET["closer_default_blended"];}
	elseif (isset($_POST["closer_default_blended"]))	{$closer_default_blended=$_POST["closer_default_blended"];}
if (isset($_GET["outbound_alt_cid"]))				{$outbound_alt_cid=$_GET["outbound_alt_cid"];}
	elseif (isset($_POST["outbound_alt_cid"]))		{$outbound_alt_cid=$_POST["outbound_alt_cid"];}
if (isset($_GET["phone_ring_timeout"]))				{$phone_ring_timeout=$_GET["phone_ring_timeout"];}
	elseif (isset($_POST["phone_ring_timeout"]))	{$phone_ring_timeout=$_POST["phone_ring_timeout"];}
if (isset($_GET["delete_vm_after_email"]))			{$delete_vm_after_email=$_GET["delete_vm_after_email"];}
	elseif (isset($_POST["delete_vm_after_email"]))	{$delete_vm_after_email=$_POST["delete_vm_after_email"];}
if (isset($_GET["did_description"]))			{$did_description=$_GET["did_description"];}
	elseif (isset($_POST["did_description"]))	{$did_description=$_POST["did_description"];}
if (isset($_GET["did_route"]))			{$did_route=$_GET["did_route"];}
	elseif (isset($_POST["did_route"]))	{$did_route=$_POST["did_route"];}
if (isset($_GET["record_call"]))			{$record_call=$_GET["record_call"];}
	elseif (isset($_POST["record_call"]))	{$record_call=$_POST["record_call"];}
if (isset($_GET["exten_context"]))			{$exten_context=$_GET["exten_context"];}
	elseif (isset($_POST["exten_context"]))	{$exten_context=$_POST["exten_context"];}
if (isset($_GET["voicemail_ext"]))			{$voicemail_ext=$_GET["voicemail_ext"];}
	elseif (isset($_POST["voicemail_ext"]))	{$voicemail_ext=$_POST["voicemail_ext"];}
if (isset($_GET["phone_extension"]))			{$phone_extension=$_GET["phone_extension"];}
	elseif (isset($_POST["phone_extension"]))	{$phone_extension=$_POST["phone_extension"];}
if (isset($_GET["filter_clean_cid_number"]))			{$filter_clean_cid_number=$_GET["filter_clean_cid_number"];}
	elseif (isset($_POST["filter_clean_cid_number"]))	{$filter_clean_cid_number=$_POST["filter_clean_cid_number"];}
if (isset($_GET["ignore_agentdirect"]))				{$ignore_agentdirect=$_GET["ignore_agentdirect"];}
	elseif (isset($_POST["ignore_agentdirect"]))	{$ignore_agentdirect=$_POST["ignore_agentdirect"];}
if (isset($_GET["areacode"]))				{$areacode=$_GET["areacode"];}
	elseif (isset($_POST["areacode"]))		{$areacode=$_POST["areacode"];}
if (isset($_GET["cid_group_id"]))			{$cid_group_id=$_GET["cid_group_id"];}
	elseif (isset($_POST["cid_group_id"]))	{$cid_group_id=$_POST["cid_group_id"];}
if (isset($_GET["cid_description"]))			{$cid_description=$_GET["cid_description"];}
	elseif (isset($_POST["cid_description"]))	{$cid_description=$_POST["cid_description"];}
if (isset($_GET["custom_fields_copy"]))				{$custom_fields_copy=$_GET["custom_fields_copy"];}
	elseif (isset($_POST["custom_fields_copy"]))	{$custom_fields_copy=$_POST["custom_fields_copy"];}
if (isset($_GET["list_description"]))			{$list_description=$_GET["list_description"];}
	elseif (isset($_POST["list_description"]))	{$list_description=$_POST["list_description"];}
if (isset($_GET["leads_counts"]))			{$leads_counts=$_GET["leads_counts"];}
	elseif (isset($_POST["leads_counts"]))	{$leads_counts=$_POST["leads_counts"];}
if (isset($_GET["remove_from_hopper"]))				{$remove_from_hopper=$_GET["remove_from_hopper"];}
	elseif (isset($_POST["remove_from_hopper"]))	{$remove_from_hopper=$_POST["remove_from_hopper"];}
if (isset($_GET["custom_order"]))				{$custom_order=$_GET["custom_order"];}
	elseif (isset($_POST["custom_order"]))		{$custom_order=$_POST["custom_order"];}
if (isset($_GET["custom_copy_method"]))				{$custom_copy_method=$_GET["custom_copy_method"];}
	elseif (isset($_POST["custom_copy_method"]))	{$custom_copy_method=$_POST["custom_copy_method"];}
if (isset($_GET["duration"]))			{$duration=$_GET["duration"];}
	elseif (isset($_POST["duration"]))	{$duration=$_POST["duration"];}
if (isset($_GET["is_webphone"]))			{$is_webphone=$_GET["is_webphone"];}
	elseif (isset($_POST["is_webphone"]))	{$is_webphone=$_POST["is_webphone"];}
if (isset($_GET["webphone_auto_answer"]))			{$webphone_auto_answer=$_GET["webphone_auto_answer"];}
	elseif (isset($_POST["webphone_auto_answer"]))	{$webphone_auto_answer=$_POST["webphone_auto_answer"];}
if (isset($_GET["use_external_server_ip"]))			{$use_external_server_ip=$_GET["use_external_server_ip"];}
	elseif (isset($_POST["use_external_server_ip"]))	{$use_external_server_ip=$_POST["use_external_server_ip"];}
if (isset($_GET["template_id"]))			{$template_id=$_GET["template_id"];}
	elseif (isset($_POST["template_id"]))	{$template_id=$_POST["template_id"];}
if (isset($_GET["on_hook_agent"]))			{$on_hook_agent=$_GET["on_hook_agent"];}
	elseif (isset($_POST["on_hook_agent"]))	{$on_hook_agent=$_POST["on_hook_agent"];}
if (isset($_GET["delete_did"]))				{$delete_did=$_GET["delete_did"];}
	elseif (isset($_POST["delete_did"]))	{$delete_did=$_POST["delete_did"];}
if (isset($_GET["group_by_campaign"]))			{$group_by_campaign=$_GET["group_by_campaign"];}
	elseif (isset($_POST["group_by_campaign"]))	{$group_by_campaign=$_POST["group_by_campaign"];}
if (isset($_GET["source_user"]))			{$source_user=$_GET["source_user"];}
	elseif (isset($_POST["source_user"]))	{$source_user=$_POST["source_user"];}
if (isset($_GET["list_exists_check"]))			{$list_exists_check=$_GET["list_exists_check"];}
	elseif (isset($_POST["list_exists_check"]))	{$list_exists_check=$_POST["list_exists_check"];}
if (isset($_GET["menu_id"]))			{$menu_id=$_GET["menu_id"];}
	elseif (isset($_POST["menu_id"]))	{$menu_id=$_POST["menu_id"];}
if (isset($_GET["xferconf_one"]))			{$xferconf_one=$_GET["xferconf_one"];}
	elseif (isset($_POST["xferconf_one"]))	{$xferconf_one=$_POST["xferconf_one"];}
if (isset($_GET["xferconf_two"]))			{$xferconf_two=$_GET["xferconf_two"];}
	elseif (isset($_POST["xferconf_two"]))	{$xferconf_two=$_POST["xferconf_two"];}
if (isset($_GET["xferconf_three"]))			{$xferconf_three=$_GET["xferconf_three"];}
	elseif (isset($_POST["xferconf_three"]))	{$xferconf_three=$_POST["xferconf_three"];}
if (isset($_GET["xferconf_four"]))			{$xferconf_four=$_GET["xferconf_four"];}
	elseif (isset($_POST["xferconf_four"]))	{$xferconf_four=$_POST["xferconf_four"];}
if (isset($_GET["xferconf_five"]))			{$xferconf_five=$_GET["xferconf_five"];}
	elseif (isset($_POST["xferconf_five"]))	{$xferconf_five=$_POST["xferconf_five"];}
if (isset($_GET["use_internal_webserver"]))				{$use_internal_webserver=$_GET["use_internal_webserver"];}
	elseif (isset($_POST["use_internal_webserver"]))	{$use_internal_webserver=$_POST["use_internal_webserver"];}
if (isset($_GET["field_label"]))				{$field_label=$_GET["field_label"];}
	elseif (isset($_POST["field_label"]))		{$field_label=$_POST["field_label"];}
if (isset($_GET["field_name"]))					{$field_name=$_GET["field_name"];}
	elseif (isset($_POST["field_name"]))		{$field_name=$_POST["field_name"];}
if (isset($_GET["field_description"]))			{$field_description=$_GET["field_description"];}
	elseif (isset($_POST["field_description"]))	{$field_description=$_POST["field_description"];}
if (isset($_GET["field_rank"]))					{$field_rank=$_GET["field_rank"];}
	elseif (isset($_POST["field_rank"]))		{$field_rank=$_POST["field_rank"];}
if (isset($_GET["field_help"]))					{$field_help=$_GET["field_help"];}
	elseif (isset($_POST["field_help"]))		{$field_help=$_POST["field_help"];}
if (isset($_GET["field_type"]))					{$field_type=$_GET["field_type"];}
	elseif (isset($_POST["field_type"]))		{$field_type=$_POST["field_type"];}
if (isset($_GET["field_options"]))				{$field_options=$_GET["field_options"];}
	elseif (isset($_POST["field_options"]))		{$field_options=$_POST["field_options"];}
if (isset($_GET["field_size"]))					{$field_size=$_GET["field_size"];}
	elseif (isset($_POST["field_size"]))		{$field_size=$_POST["field_size"];}
if (isset($_GET["field_max"]))					{$field_max=$_GET["field_max"];}
	elseif (isset($_POST["field_max"]))			{$field_max=$_POST["field_max"];}
if (isset($_GET["field_default"]))				{$field_default=$_GET["field_default"];}
	elseif (isset($_POST["field_default"]))		{$field_default=$_POST["field_default"];}
if (isset($_GET["field_required"]))				{$field_required=$_GET["field_required"];}
	elseif (isset($_POST["field_required"]))	{$field_required=$_POST["field_required"];}
if (isset($_GET["name_position"]))				{$name_position=$_GET["name_position"];}
	elseif (isset($_POST["name_position"]))		{$name_position=$_POST["name_position"];}
if (isset($_GET["multi_position"]))				{$multi_position=$_GET["multi_position"];}
	elseif (isset($_POST["multi_position"]))	{$multi_position=$_POST["multi_position"];}
if (isset($_GET["field_order"]))				{$field_order=$_GET["field_order"];}
	elseif (isset($_POST["field_order"]))		{$field_order=$_POST["field_order"];}
if (isset($_GET["field_encrypt"]))				{$field_encrypt=$_GET["field_encrypt"];}
	elseif (isset($_POST["field_encrypt"]))		{$field_encrypt=$_POST["field_encrypt"];}
if (isset($_GET["field_show_hide"]))			{$field_show_hide=$_GET["field_show_hide"];}
	elseif (isset($_POST["field_show_hide"]))	{$field_show_hide=$_POST["field_show_hide"];}
if (isset($_GET["field_duplicate"]))			{$field_duplicate=$_GET["field_duplicate"];}
	elseif (isset($_POST["field_duplicate"]))	{$field_duplicate=$_POST["field_duplicate"];}
if (isset($_GET["field_rerank"]))				{$field_rerank=$_GET["field_rerank"];}
	elseif (isset($_POST["field_rerank"]))		{$field_rerank=$_POST["field_rerank"];}
if (isset($_GET["custom_fields_add"]))				{$custom_fields_add=$_GET["custom_fields_add"];}
	elseif (isset($_POST["custom_fields_add"]))		{$custom_fields_add=$_POST["custom_fields_add"];}
if (isset($_GET["custom_fields_update"]))			{$custom_fields_update=$_GET["custom_fields_update"];}
	elseif (isset($_POST["custom_fields_update"]))	{$custom_fields_update=$_POST["custom_fields_update"];}
if (isset($_GET["custom_fields_delete"]))			{$custom_fields_delete=$_GET["custom_fields_delete"];}
	elseif (isset($_POST["custom_fields_delete"]))	{$custom_fields_delete=$_POST["custom_fields_delete"];}
if (isset($_GET["dialable_count"]))				{$dialable_count=$_GET["dialable_count"];}
	elseif (isset($_POST["dialable_count"]))	{$dialable_count=$_POST["dialable_count"];}
if (isset($_GET["call_handle_method"]))				{$call_handle_method=$_GET["call_handle_method"];}
	elseif (isset($_POST["call_handle_method"]))	{$call_handle_method=$_POST["call_handle_method"];}
if (isset($_GET["agent_search_method"]))			{$agent_search_method=$_GET["agent_search_method"];}
	elseif (isset($_POST["agent_search_method"]))	{$agent_search_method=$_POST["agent_search_method"];}
if (isset($_GET["ingroup_rank"]))			{$ingroup_rank=$_GET["ingroup_rank"];}
	elseif (isset($_POST["ingroup_rank"]))	{$ingroup_rank=$_POST["ingroup_rank"];}
if (isset($_GET["ingroup_grade"]))			{$ingroup_grade=$_GET["ingroup_grade"];}
	elseif (isset($_POST["ingroup_grade"]))	{$ingroup_grade=$_POST["ingroup_grade"];}
if (isset($_GET["ingrp_rg_only"]))			{$ingrp_rg_only=$_GET["ingrp_rg_only"];}
	elseif (isset($_POST["ingrp_rg_only"]))	{$ingrp_rg_only=$_POST["ingrp_rg_only"];}
if (isset($_GET["group_id"]))				{$group_id=$_GET["group_id"];}
	elseif (isset($_POST["group_id"]))		{$group_id=$_POST["group_id"];}
if (isset($_GET["lead_ids"]))				{$lead_ids=$_GET["lead_ids"];}
	elseif (isset($_POST["lead_ids"]))		{$lead_ids=$_POST["lead_ids"];}
if (isset($_GET["delete_cf_data"]))				{$delete_cf_data=$_GET["delete_cf_data"];}
	elseif (isset($_POST["delete_cf_data"]))	{$delete_cf_data=$_POST["delete_cf_data"];}
if (isset($_GET["dispo_call_url"]))				{$dispo_call_url=$_GET["dispo_call_url"];}
	elseif (isset($_POST["dispo_call_url"]))	{$dispo_call_url=$_POST["dispo_call_url"];}
if (isset($_GET["entry_type"]))				{$entry_type=$_GET["entry_type"];}
	elseif (isset($_POST["entry_type"]))	{$entry_type=$_POST["entry_type"];}
if (isset($_GET["alt_url_id"]))				{$alt_url_id=$_GET["alt_url_id"];}
	elseif (isset($_POST["alt_url_id"]))	{$alt_url_id=$_POST["alt_url_id"];}
if (isset($_GET["url_address"]))			{$url_address=$_GET["url_address"];}
	elseif (isset($_POST["url_address"]))	{$url_address=$_POST["url_address"];}
if (isset($_GET["url_type"]))				{$url_type=$_GET["url_type"];}
	elseif (isset($_POST["url_type"]))		{$url_type=$_POST["url_type"];}
if (isset($_GET["url_rank"]))				{$url_rank=$_GET["url_rank"];}
	elseif (isset($_POST["url_rank"]))		{$url_rank=$_POST["url_rank"];}
if (isset($_GET["url_statuses"]))			{$url_statuses=$_GET["url_statuses"];}
	elseif (isset($_POST["url_statuses"]))	{$url_statuses=$_POST["url_statuses"];}
if (isset($_GET["url_description"]))			{$url_description=$_GET["url_description"];}
	elseif (isset($_POST["url_description"]))	{$url_description=$_POST["url_description"];}
if (isset($_GET["url_lists"]))				{$url_lists=$_GET["url_lists"];}
	elseif (isset($_POST["url_lists"]))		{$url_lists=$_POST["url_lists"];}
if (isset($_GET["url_call_length"]))			{$url_call_length=$_GET["url_call_length"];}
	elseif (isset($_POST["url_call_length"]))	{$url_call_length=$_POST["url_call_length"];}
if (isset($_GET["preset_name"]))			{$preset_name=$_GET["preset_name"];}
	elseif (isset($_POST["preset_name"]))	{$preset_name=$_POST["preset_name"];}
if (isset($_GET["preset_number"]))			{$preset_number=$_GET["preset_number"];}
	elseif (isset($_POST["preset_number"]))	{$preset_number=$_POST["preset_number"];}
if (isset($_GET["preset_dtmf"]))			{$preset_dtmf=$_GET["preset_dtmf"];}
	elseif (isset($_POST["preset_dtmf"]))	{$preset_dtmf=$_POST["preset_dtmf"];}
if (isset($_GET["preset_hide_number"]))				{$preset_hide_number=$_GET["preset_hide_number"];}
	elseif (isset($_POST["preset_hide_number"]))	{$preset_hide_number=$_POST["preset_hide_number"];}
if (isset($_GET["action"]))				{$action=$_GET["action"];}
	elseif (isset($_POST["action"]))	{$action=$_POST["action"];}
if (isset($_GET["dial_status_add"]))			{$dial_status_add=$_GET["dial_status_add"];}
	elseif (isset($_POST["dial_status_add"]))	{$dial_status_add=$_POST["dial_status_add"];}
if (isset($_GET["dial_status_remove"]))				{$dial_status_remove=$_GET["dial_status_remove"];}
	elseif (isset($_POST["dial_status_remove"]))	{$dial_status_remove=$_POST["dial_status_remove"];}
if (isset($_GET["include_ip"]))				{$include_ip=$_GET["include_ip"];}
	elseif (isset($_POST["include_ip"]))	{$include_ip=$_POST["include_ip"];}
if (isset($_GET["reset_password"]))				{$reset_password=$_GET["reset_password"];}
	elseif (isset($_POST["reset_password"]))	{$reset_password=$_POST["reset_password"];}
if (isset($_GET["archived_lead"]))			{$archived_lead=$_GET["archived_lead"];}
	elseif (isset($_POST["archived_lead"]))	{$archived_lead=$_POST["archived_lead"];}
if (isset($_GET["list_order"]))			{$list_order=$_GET["list_order"];}
	elseif (isset($_POST["list_order"]))	{$list_order=$_POST["list_order"];}
if (isset($_GET["list_order_randomize"]))			{$list_order_randomize=$_GET["list_order_randomize"];}
	elseif (isset($_POST["list_order_randomize"]))	{$list_order_randomize=$_POST["list_order_randomize"];}
if (isset($_GET["list_order_secondary"]))			{$list_order_secondary=$_GET["list_order_secondary"];}
	elseif (isset($_POST["list_order_secondary"]))	{$list_order_secondary=$_POST["list_order_secondary"];}
if (isset($_GET["number_of_lines"]))			{$number_of_lines=$_GET["number_of_lines"];}
	elseif (isset($_POST["number_of_lines"]))	{$number_of_lines=$_POST["number_of_lines"];}
if (isset($_GET["source_did_pattern"]))				{$source_did_pattern=$_GET["source_did_pattern"];}
	elseif (isset($_POST["source_did_pattern"]))	{$source_did_pattern=$_POST["source_did_pattern"];}
if (isset($_GET["new_dids"]))			{$new_dids=$_GET["new_dids"];}
	elseif (isset($_POST["new_dids"]))	{$new_dids=$_POST["new_dids"];}

$DB=preg_replace('/[^0-9]/','',$DB);

if (file_exists('options.php'))
	{require('options.php');}

header ("Content-type: text/html; charset=utf-8");
header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
header ("Pragma: no-cache");                          // HTTP/1.0

#############################################
##### START SYSTEM_SETTINGS LOOKUP #####
$stmt = "SELECT use_non_latin,custom_fields_enabled,pass_hash_enabled,agent_whisper_enabled,active_modules,auto_dial_limit,enable_languages,language_method,admin_web_directory,sounds_web_server,allow_web_debug FROM system_settings;";
$rslt=mysql_to_mysqli($stmt, $link);
$qm_conf_ct = mysqli_num_rows($rslt);
if ($qm_conf_ct > 0)
	{
	$row=mysqli_fetch_row($rslt);
	$non_latin =				$row[0];
	$custom_fields_enabled =	$row[1];
	$SSpass_hash_enabled =		$row[2];
	$agent_whisper_enabled =	$row[3];
	$active_modules =			$row[4];
	$SSauto_dial_limit =		$row[5];
	# slightly increase limit value, because PHP somehow thinks 2.8 > 2.8
	$SSauto_dial_limit = ($SSauto_dial_limit + 0.001);
	$SSenable_languages =		$row[6];
	$SSlanguage_method =		$row[7];
	$SSadmin_web_directory =	$row[8];
	$SSsounds_web_server =		$row[9];
	$SSallow_web_debug =		$row[10];
	}
if ($SSallow_web_debug < 1) {$DB=0;}
##### END SETTINGS LOOKUP #####
###########################################

$list_id = preg_replace('/[^-_0-9a-zA-Z]/','',$list_id);
$list_id_field = preg_replace('/[^0-9]/','',$list_id_field);
$lead_id = preg_replace('/[^0-9]/','',$lead_id);
$lead_ids = preg_replace('/[^\,0-9]/','',$lead_ids);
$list_exists_check = preg_replace('/[^0-9a-zA-Z]/','',$list_exists_check);
$use_internal_webserver = preg_replace('/[^0-9a-zA-Z]/','',$use_internal_webserver);
$field_rerank = preg_replace('/[^_0-9a-zA-Z]/','',$field_rerank);
$custom_fields_add = preg_replace('/[^_0-9a-zA-Z]/','',$custom_fields_add);
$custom_fields_update = preg_replace('/[^_0-9a-zA-Z]/','',$custom_fields_update);
$custom_fields_delete = preg_replace('/[^_0-9a-zA-Z]/','',$custom_fields_delete);
$dialable_count = preg_replace('/[^_0-9a-zA-Z]/','',$dialable_count);
$call_handle_method = preg_replace('/[^_0-9a-zA-Z]/','',$call_handle_method);
$agent_search_method = preg_replace('/[^_0-9a-zA-Z]/','',$agent_search_method);
$delete_cf_data = preg_replace('/[^A-Z]/','',$delete_cf_data);
$entry_type = preg_replace('/[^_0-9a-zA-Z]/','',$entry_type);
$alt_url_id = preg_replace('/[^0-9A-Z]/','',$alt_url_id);
$url_type = preg_replace('/[^_0-9a-zA-Z]/','',$url_type);
$url_rank = preg_replace('/[^0-9]/','',$url_rank);
$url_lists = preg_replace('/[^- 0-9A-Z]/', '',$url_lists);
$url_call_length = preg_replace('/[^0-9]/','',$url_call_length);
$status_breakdown = preg_replace('/[^1Y]/','',$status_breakdown);
$show_percentages = preg_replace('/[^1Y]/','',$show_percentages);
$function = preg_replace('/[^-_0-9a-zA-Z]/', '',$function);
$format = preg_replace('/[^0-9a-zA-Z]/','',$format);
$session_id = preg_replace('/[^0-9]/','',$session_id);
$server_ip = preg_replace('/[^-\.\:\_0-9a-zA-Z]/','',$server_ip);
$stage = preg_replace('/[^-_0-9a-zA-Z]/','',$stage);
$rank = preg_replace('/[^0-9]/','',$rank);
$did_ids=preg_replace('/[^\,\+0-9a-zA-Z]/','',$did_ids);
$did_patterns = preg_replace('/[^\,\:\+\*\#\.\_0-9a-zA-Z]/','',$did_patterns);
$end_date=preg_replace('/[^-0-9]/','',$end_date);
$end_time=preg_replace('/[^:0-9]/','',$end_time);
$entry_list_id = preg_replace('/[^0-9]/','',$entry_list_id);
$phone_code = preg_replace('/[^0-9]/','',$phone_code);
$update_phone_number=preg_replace('/[^A-Z]/','',$update_phone_number);
$date_of_birth = preg_replace('/[^-0-9]/','',$date_of_birth);
$gender = preg_replace('/[^A-Z]/','',$gender);
$dnc_check = preg_replace('/[^A-Z]/','',$dnc_check);
$campaign_dnc_check = preg_replace('/[^A-Z]/','',$campaign_dnc_check);
$add_to_hopper = preg_replace('/[^A-Z]/','',$add_to_hopper);
$hopper_priority = preg_replace("/[^-0-9]/", "",$hopper_priority);
$hopper_local_call_time_check = preg_replace('/[^A-Z]/','',$hopper_local_call_time_check);
$custom_fields = preg_replace('/[^0-9a-zA-Z]/','',$custom_fields);
$search_method = preg_replace('/[^-_0-9a-zA-Z]/','',$search_method);
$duplicate_check = preg_replace('/[^-_0-9a-zA-Z]/','',$duplicate_check);
$insert_if_not_found = preg_replace('/[^A-Z]/','',$insert_if_not_found);
$records = preg_replace('/[^0-9]/','',$records);
$search_location = preg_replace('/[^A-Z]/','',$search_location);
$no_update = preg_replace('/[^A-Z]/','',$no_update);
$delete_lead = preg_replace('/[^A-Z]/','',$delete_lead);
$called_count=preg_replace('/[^0-9]/','',$called_count);
$agent_user_level=preg_replace('/[^0-9]/','',$agent_user_level);
$hotkeys_active=preg_replace('/[^0-9]/','',$hotkeys_active);
$protocol=preg_replace('/[^0-9a-zA-Z]/','',$protocol);
$local_gmt=preg_replace('/[^-\.0-9]/','',$local_gmt);
$active=preg_replace('/[^A-Z]/','',$active);
$reset_list=preg_replace('/[^A-Z]/','',$reset_list);
$delete_list=preg_replace('/[^A-Z]/','',$delete_list);
$delete_leads=preg_replace('/[^A-Z]/','',$delete_leads);
$reset_time=preg_replace('/[^-_0-9]/', '',$reset_time);
$tz_method = preg_replace('/[^-\_0-9a-zA-Z]/', '',$tz_method);
$reset_lead = preg_replace('/[^A-Z]/','',$reset_lead);
$usacan_areacode_check = preg_replace('/[^A-Z]/','',$usacan_areacode_check);
$usacan_prefix_check = preg_replace('/[^A-Z]/','',$usacan_prefix_check);
$delete_phone = preg_replace('/[^A-Z]/','',$delete_phone);
$callback_datetime = preg_replace('/[^- \+\.\:\/\@\_0-9a-zA-Z]/','',$callback_datetime);
$callback = preg_replace('/[^A-Z]/','',$callback);
$callback_type = preg_replace('/[^A-Z]/','',$callback_type);
$datetime_start = preg_replace('/[^- \+\:\_0-9]/','',$datetime_start);
$datetime_end = preg_replace('/[^- \+\:\_0-9]/','',$datetime_end);
$time_format = preg_replace('/[^A-Z]/','',$time_format);
$nanpa_ac_prefix_check = preg_replace('/[^A-Z]/','',$nanpa_ac_prefix_check);
$delete_user = preg_replace('/[^A-Z]/','',$delete_user);
$campaign_rank = preg_replace('/[^-_0-9]/','',$campaign_rank);
$campaign_grade = preg_replace('/[^0-9]/','',$campaign_grade);
$camp_rg_only = preg_replace('/[^0-9]/','',$camp_rg_only);
$wrapup_seconds_override = preg_replace('/[^-0-9]/','',$wrapup_seconds_override);
$show_sub_status = preg_replace('/[^A-Z]/','',$show_sub_status);
$auto_dial_level = preg_replace('/[^\.0-9]/','',$auto_dial_level);
$adaptive_maximum_level = preg_replace('/[^\.0-9]/','',$adaptive_maximum_level);
$campaign_vdad_exten = preg_replace('/[^0-9]/','',$campaign_vdad_exten);
$hopper_level = preg_replace('/[^0-9]/','',$hopper_level);
$reset_hopper = preg_replace('/[^NY]/','',$reset_hopper);
$dial_method = preg_replace('/[^-_0-9a-zA-Z]/','',$dial_method);
$dial_timeout = preg_replace('/[^0-9]/','',$dial_timeout);
$lookup_state = preg_replace('/[^A-Z]/','',$lookup_state);
$detail = preg_replace('/[^A-Z]/','',$detail);
$type = preg_replace('/[^-_0-9a-zA-Z]/','',$type);
$force_entry_list_id = preg_replace('/[^0-9]/','',$force_entry_list_id);
$file_download = preg_replace('/[^0-9]/','',$file_download);
$agent_choose_ingroups = preg_replace('/[^0-9]/','',$agent_choose_ingroups);
$agent_choose_blended = preg_replace('/[^0-9]/','',$agent_choose_blended);
$closer_default_blended = preg_replace('/[^0-9]/','',$closer_default_blended);
$phone_ring_timeout = preg_replace('/[^0-9]/','',$phone_ring_timeout);
$delete_vm_after_email = preg_replace('/[^a-zA-Z]/','',$delete_vm_after_email);
$field_rank = preg_replace('/[^0-9]/','',$field_rank);
$field_size = preg_replace('/[^0-9]/','',$field_size);
$field_max = preg_replace('/[^0-9]/','',$field_max);
$field_order = preg_replace('/[^0-9]/','',$field_order);
$field_required = preg_replace('/[^_A-Z]/','',$field_required);
$field_encrypt = preg_replace('/[^NY]/','',$field_encrypt);
$field_duplicate = preg_replace('/[^_A-Z]/','',$field_duplicate);
$field_type = preg_replace('/[^0-9a-zA-Z]/','',$field_type);
$name_position = preg_replace('/[^0-9a-zA-Z]/','',$name_position);
$multi_position = preg_replace('/[^0-9a-zA-Z]/','',$multi_position);
$ingroup_rank = preg_replace('/[^-_0-9]/','',$ingroup_rank);
$ingroup_grade = preg_replace('/[^0-9]/','',$ingroup_grade);
$ingrp_rg_only = preg_replace('/[^0-9]/','',$ingrp_rg_only);
$query_date=preg_replace('/[^-0-9]/','',$query_date);
$query_time=preg_replace('/[^:0-9]/','',$query_time);
$gmt_offset_now = preg_replace('/[^-\_\.0-9]/','',$gmt_offset_now);
$date=preg_replace('/[^-0-9]/','',$date);
$header = preg_replace('/[^0-9a-zA-Z]/','',$header);
$preset_hide_number = preg_replace('/[^0-9a-zA-Z]/','',$preset_hide_number);
$preset_number = preg_replace('/[^\*\#\.\_0-9a-zA-Z]/','',$preset_number);
$preset_dtmf = preg_replace('/[^- \,\*\#0-9a-zA-Z]/','',$preset_dtmf);
$action = preg_replace('/[^0-9a-zA-Z]/','',$action);
$include_ip = preg_replace('/[^0-9a-zA-Z]/','',$include_ip);
$reset_password = preg_replace('/[^0-9]/','',$reset_password);
$archived_lead = preg_replace('/[^0-9a-zA-Z]/','',$archived_lead);
$list_order = preg_replace('/[^ 0-9a-zA-Z]/','',$list_order);
$list_order_randomize = preg_replace('/[^-_0-9a-zA-Z]/','',$list_order_randomize);
$list_order_secondary = preg_replace('/[^-_0-9a-zA-Z]/','',$list_order_secondary);
$number_of_lines = preg_replace('/[^0-9]/','',$number_of_lines);

if ($non_latin < 1)
	{
	$status = preg_replace('/[^-_0-9a-zA-Z]/','',$status);
	$ingroups = preg_replace('/[^-_0-9a-zA-Z]/','',$ingroups);
	$phone_extension = preg_replace('/[^-_0-9a-zA-Z]/','',$phone_extension);
	$users=preg_replace('/[^-\,\_0-9a-zA-Z]/','',$users);
	$statuses = preg_replace('/[^- \.\,\_0-9a-zA-Z]/','',$statuses);
	$categories = preg_replace('/[^-\,\_0-9a-zA-Z]/','',$categories);
	$user=preg_replace('/[^-_0-9a-zA-Z]/','',$user);
	$pass=preg_replace('/[^-_0-9a-zA-Z]/','',$pass);
	$agent_user=preg_replace('/[^-_0-9a-zA-Z]/','',$agent_user);
	$phone_number = preg_replace('/[^\,0-9]/','',$phone_number);
	$vendor_lead_code = preg_replace('/;|#|\"/','',$vendor_lead_code);
		$vendor_lead_code = preg_replace('/\+/',' ',$vendor_lead_code);
	$source_id = preg_replace('/;|#|\"/','',$source_id);
		$source_id = preg_replace('/\+/',' ',$source_id);
	$title = preg_replace('/[^- \'\_\.0-9a-zA-Z]/','',$title);
	$first_name = preg_replace('/[^- \'\+\_\.0-9a-zA-Z]/','',$first_name);
		$first_name = preg_replace('/\+/',' ',$first_name);
	$middle_initial = preg_replace('/[^-_0-9a-zA-Z]/','',$middle_initial);
	$last_name = preg_replace('/[^- \'\+\_\.0-9a-zA-Z]/','',$last_name);
		$last_name = preg_replace('/\+/',' ',$last_name);
	$address1 = preg_replace('/[^- \'\+\.\:\/\@\_0-9a-zA-Z]/','',$address1);
	$address2 = preg_replace('/[^- \'\+\.\:\/\@\_0-9a-zA-Z]/','',$address2);
	$address3 = preg_replace('/[^- \'\+\.\:\/\@\_0-9a-zA-Z]/','',$address3);
		$address1 = preg_replace('/\+/',' ',$address1);
		$address2 = preg_replace('/\+/',' ',$address2);
		$address3 = preg_replace('/\+/',' ',$address3);
	$city = preg_replace('/[^- \'\+\.\:\/\@\_0-9a-zA-Z]/','',$city);
		$city = preg_replace('/\+/',' ',$city);
	$state = preg_replace('/[^- 0-9a-zA-Z]/','',$state);
	$province = preg_replace('/[^- \'\+\.\_0-9a-zA-Z]/','',$province);
		$province = preg_replace('/\+/',' ',$province);
	$postal_code = preg_replace('/[^- \'\+0-9a-zA-Z]/','',$postal_code);
		$postal_code = preg_replace('/\+/',' ',$postal_code);
	$country_code = preg_replace('/[^-_0-9a-zA-Z]/','',$country_code);
	$alt_phone = preg_replace('/[^- \'\+\_\.0-9a-zA-Z]/','',$alt_phone);
		$alt_phone = preg_replace('/\+/',' ',$alt_phone);
	$email = preg_replace('/[^- \'\+\.\:\/\@\%\_0-9a-zA-Z]/','',$email);
		$email = preg_replace('/\+/',' ',$email);
	$security_phrase = preg_replace('/[^- \'\+\.\:\/\@\_0-9a-zA-Z]/','',$security_phrase);
		$security_phrase = preg_replace('/\+/',' ',$security_phrase);
	$comments = preg_replace('/;|#|\"/','',$comments);
		$comments = preg_replace('/\+/',' ',$comments);
	$campaign_id = preg_replace('/[^-\_0-9a-zA-Z]/', '',$campaign_id);
	$multi_alt_phones = preg_replace('/[^- \+\!\:\_0-9a-zA-Z]/','',$multi_alt_phones);
		$multi_alt_phones = preg_replace('/\+/',' ',$multi_alt_phones);
	$source = preg_replace('/[^0-9a-zA-Z]/','',$source);
	$phone_login = preg_replace('/[^-\_0-9a-zA-Z]/', '',$phone_login);
	$owner = preg_replace('/[^- \'\+\.\:\/\@\_0-9a-zA-Z]/','',$owner);
		$owner = preg_replace('/\+/',' ',$owner);
	$user_field = preg_replace('/[^-_0-9a-zA-Z]/','',$user_field);
	$voicemail_id=preg_replace('/[^0-9a-zA-Z]/','',$voicemail_id);
	$agent_pass=preg_replace('/[^-_0-9a-zA-Z]/','',$agent_pass);
	$agent_full_name=preg_replace('/[^- \+\.\:\/\@\_0-9a-zA-Z]/','',$agent_full_name);
	$agent_user_group=preg_replace('/[^-_0-9a-zA-Z]/','',$agent_user_group);
	$phone_pass=preg_replace('/[^-_0-9a-zA-Z]/','',$phone_pass);
	$custom_one=preg_replace('/[^- \+\.\:\/\@\_0-9a-zA-Z]/','',$custom_one);
	$custom_two=preg_replace('/[^- \+\.\:\/\@\_0-9a-zA-Z]/','',$custom_two);
	$custom_three=preg_replace('/[^- \+\.\:\/\@\_0-9a-zA-Z]/','',$custom_three);
	$custom_four=preg_replace('/[^- \+\.\:\/\@\_0-9a-zA-Z]/','',$custom_four);
	$custom_five=preg_replace('/[^- \+\.\:\/\@\_0-9a-zA-Z]/','',$custom_five);
	$extension=preg_replace('/[^-_0-9a-zA-Z]/','',$extension);
	$dialplan_number=preg_replace('/[^\*\#0-9a-zA-Z]/','',$dialplan_number);
	$registration_password=preg_replace('/[^-_0-9a-zA-Z]/','',$registration_password);
	$phone_full_name=preg_replace('/[^- \+\.\_0-9a-zA-Z]/','',$phone_full_name);
	$outbound_cid=preg_replace('/[^-_0-9a-zA-Z]/','',$outbound_cid);
	$phone_context=preg_replace('/[^-_0-9a-zA-Z]/','',$phone_context);
	$list_name=preg_replace('/[^- \+\.\:\/\@\?\&\_0-9a-zA-Z]/','',$list_name);
	$script=preg_replace('/[^-_0-9a-zA-Z]/','',$script);
	$am_message=preg_replace('/[^-_0-9a-zA-Z]/','',$am_message);
	$drop_inbound_group=preg_replace('/[^-_0-9a-zA-Z]/','',$drop_inbound_group);
	$web_form_address=preg_replace('/[^- %=\+\.\:\/\@\?\&\_0-9a-zA-Z]/','',$web_form_address);
	$web_form_address_two=preg_replace('/[^- %=\+\.\:\/\@\?\&\_0-9a-zA-Z]/','',$web_form_address_two);
	$web_form_address_three=preg_replace('/[^- %=\+\.\:\/\@\?\&\_0-9a-zA-Z]/','',$web_form_address_three);
	$dispo_call_url=preg_replace('/[^- %=\+\.\:\/\@\?\&\_0-9a-zA-Z]/','',$dispo_call_url);
	$url_address=preg_replace('/[^- %=\+\.\:\/\@\?\&\_0-9a-zA-Z]/','',$url_address);
	$uniqueid=preg_replace('/[^- \.\_0-9a-zA-Z]/','',$uniqueid);
	$alias_id = preg_replace('/[^-\_0-9a-zA-Z]/', '',$alias_id);
	$phone_logins = preg_replace('/[^-\,\_0-9a-zA-Z]/','',$phone_logins);
	$alias_name = preg_replace('/[^- \+\.\:\/\@\_0-9a-zA-Z]/','',$alias_name);
	$delete_alias = preg_replace('/[^A-Z]/','',$delete_alias);
	$callback_status = preg_replace('/[^-\_0-9a-zA-Z]/', '',$callback_status);
	$callback_user = preg_replace('/[^-\_0-9a-zA-Z]/', '',$callback_user);
	$callback_comments = preg_replace('/[^- \+\.\:\/\@\_0-9a-zA-Z]/','',$callback_comments);
	$admin_user_group = preg_replace('/[^-\_0-9a-zA-Z]/', '',$admin_user_group);
	$group_alias_id = preg_replace('/[^\_0-9a-zA-Z]/','',$group_alias_id);
	$group_alias_name = preg_replace('/[^- \+\_0-9a-zA-Z]/','',$group_alias_name);
	$caller_id_number = preg_replace('/[^0-9]/','',$caller_id_number);
	$caller_id_name = preg_replace('/[^- \+\_0-9a-zA-Z]/','',$caller_id_name);
	$user_groups = preg_replace('/[^-\|\,\_0-9a-zA-Z]/','',$user_groups); #JCJ
	$in_groups = preg_replace('/[^-\|\,\_0-9a-zA-Z]/','',$in_groups); #JCJ
	$group = preg_replace('/[^-\|\_0-9a-zA-Z]/','',$group);
	$call_id = preg_replace('/[^0-9a-zA-Z]/','',$call_id);
	$expiration_date = preg_replace('/[^-_0-9a-zA-Z]/','',$expiration_date);
	$local_call_time = preg_replace('/[^-_0-9a-zA-Z]/','',$local_call_time);
	$campaigns = preg_replace('/[^-\,\|\_0-9a-zA-Z]/','',$campaigns); # JCJ
	$campaign_name = preg_replace('/[^- \.\,\_0-9a-zA-Z]/','',$campaign_name);
	$field_name = preg_replace('/[^-_0-9a-zA-Z]/','',$field_name);
	$lead_filter_id = preg_replace('/[^-_0-9a-zA-Z]/','',$lead_filter_id);
	$outbound_alt_cid = preg_replace('/[^0-9a-zA-Z]/','',$outbound_alt_cid);
	$did_pattern = preg_replace('/[^:\+\*\#\.\_0-9a-zA-Z]/','',$did_pattern);
	$source_did_pattern = preg_replace('/[^:\+\*\#\.\_0-9a-zA-Z]/','',$source_did_pattern);
	$new_dids = preg_replace('/[^:\+\,\*\#\.\_0-9a-zA-Z]/','',$new_dids);
	$did_description = preg_replace('/[^- \.\,\_0-9a-zA-Z]/','',$did_description);
	$did_route = preg_replace('/[^-_0-9a-zA-Z]/','',$did_route);
	$record_call = preg_replace('/[^-_0-9a-zA-Z]/','',$record_call);
	$exten_context = preg_replace('/[^-_0-9a-zA-Z]/','',$exten_context);
	$voicemail_ext = preg_replace('/[^\*\#\.\_0-9a-zA-Z]/','',$voicemail_ext);
	$extension = preg_replace('/[^-\*\#\.\:\/\@\_0-9a-zA-Z]/','',$extension);
	$filter_clean_cid_number = preg_replace('/[^- \.\,\_0-9a-zA-Z]/','',$filter_clean_cid_number);
	$ignore_agentdirect = preg_replace('/[^A-Z]/','',$ignore_agentdirect);
	$areacode = preg_replace('/[^-_0-9a-zA-Z]/','',$areacode);
	$cid_group_id = preg_replace('/[^-_0-9a-zA-Z]/','',$cid_group_id);
	$cid_description = preg_replace('/[^- \.\,\_0-9a-zA-Z]/','',$cid_description);
	$custom_fields_copy = preg_replace('/[^0-9]/','',$custom_fields_copy);
	if ($outbound_cid != '---ALL---')
		{$outbound_cid=preg_replace('/[^0-9]/','',$outbound_cid);}
	if ($areacode != '---ALL---')
		{$areacode=preg_replace('/[^0-9a-zA-Z]/','',$areacode);}
	$leads_counts = preg_replace('/[^-_0-9a-zA-Z]/','',$leads_counts);
	$remove_from_hopper=preg_replace('/[^0-9a-zA-Z]/','',$remove_from_hopper);
	$list_description=preg_replace('/[^- \+\.\:\/\@\?\&\_0-9a-zA-Z]/','',$list_description);
	$custom_order = preg_replace('/[^-_0-9a-zA-Z]/','',$custom_order);
	$custom_copy_method = preg_replace('/[^-_0-9a-zA-Z]/','',$custom_copy_method);
	$duration = preg_replace('/[^-_0-9a-zA-Z]/','',$duration);
	$is_webphone = preg_replace('/[^-_0-9a-zA-Z]/','',$is_webphone);
	$webphone_auto_answer = preg_replace('/[^-_0-9a-zA-Z]/','',$webphone_auto_answer);
	$use_external_server_ip = preg_replace('/[^-_0-9a-zA-Z]/','',$use_external_server_ip);
	$template_id = preg_replace('/[^-_0-9a-zA-Z]/','',$template_id);
	$on_hook_agent = preg_replace('/[^-_0-9a-zA-Z]/','',$on_hook_agent);
	$delete_did = preg_replace('/[^0-9a-zA-Z]/','',$delete_did);
	$group_by_campaign = preg_replace('/[^0-9a-zA-Z]/','',$group_by_campaign);
	$source_user = preg_replace('/[^-_0-9a-zA-Z]/','',$source_user);
	$menu_id = preg_replace('/[^-_0-9a-zA-Z]/','',$menu_id);
	$xferconf_one=preg_replace('/[^-_0-9a-zA-Z]/','',$xferconf_one);
	$xferconf_two=preg_replace('/[^-_0-9a-zA-Z]/','',$xferconf_two);
	$xferconf_three=preg_replace('/[^-_0-9a-zA-Z]/','',$xferconf_three);
	$xferconf_four=preg_replace('/[^-_0-9a-zA-Z]/','',$xferconf_four);
	$xferconf_five=preg_replace('/[^-_0-9a-zA-Z]/','',$xferconf_five);
	$field_label = preg_replace('/[^_0-9a-zA-Z]/','',$field_label);
	$field_show_hide = preg_replace('/[^_0-9a-zA-Z]/','',$field_show_hide);
	$field_name = preg_replace('/[^ \.\,-\_0-9a-zA-Z]/','',$field_name);
	$field_description = preg_replace('/[^ \.\,-\_0-9a-zA-Z]/','',$field_description);
	$field_options = preg_replace('/[^ \'\&\.\n\|\,-\_0-9a-zA-Z]/', '',$field_options);
	if ($field_type != 'SCRIPT')
		{$field_options = preg_replace('/[^ \.\n\|\,-\_0-9a-zA-Z]/', '',$field_options);}
	$field_help = preg_replace('/[^ \'\&\.\n\|\,-\_0-9a-zA-Z]/', '',$field_help);
	$field_default = preg_replace('/[^ \.\n\,-\_0-9a-zA-Z]/', '',$field_default);
	$group_id = preg_replace('/[^_0-9a-zA-Z]/','',$group_id);
	$url_statuses = preg_replace('/[^- 0-9a-zA-Z]/', '',$url_statuses);
	$url_description = preg_replace('/[^ \.\,-\_0-9a-zA-Z]/','',$url_description);
	$preset_name = preg_replace('/[^- \_0-9a-zA-Z]/','',$preset_name);
	$dial_status_add=preg_replace('/[^-_0-9a-zA-Z]/','',$dial_status_add);
	$dial_status_remove=preg_replace('/[^-_0-9a-zA-Z]/','',$dial_status_remove);
	}
else
	{
	$status = preg_replace('/[^-_0-9\p{L}]/u','',$status);
	$ingroups = preg_replace('/[^-_0-9\p{L}]/u','',$ingroups);
	$phone_extension = preg_replace('/[^-_0-9\p{L}]/u','',$phone_extension);
	$users=preg_replace('/[^-\,\_0-9\p{L}]/u','',$users);
	$statuses = preg_replace('/[^- \.\,\_0-9\p{L}]/u','',$statuses);
	$categories = preg_replace('/[^-\,\_0-9\p{L}]/u','',$categories);
	$user=preg_replace('/[^-_0-9\p{L}]/u','',$user);
	$pass=preg_replace('/[^-_0-9\p{L}]/u','',$pass);
	$agent_user=preg_replace('/[^-_0-9\p{L}]/u','',$agent_user);
	$phone_number = preg_replace('/[^\,0-9]/','',$phone_number);
	$vendor_lead_code = preg_replace('/;|#|\"/','',$vendor_lead_code);
		$vendor_lead_code = preg_replace('/\+/',' ',$vendor_lead_code);
	$source_id = preg_replace('/;|#|\"/','',$source_id);
		$source_id = preg_replace('/\+/',' ',$source_id);
	$title = preg_replace('/[^- \'\_\.0-9\p{L}]/u','',$title);
	$first_name = preg_replace('/[^- \'\+\_\.0-9\p{L}]/u','',$first_name);
		$first_name = preg_replace('/\+/',' ',$first_name);
	$middle_initial = preg_replace('/[^-_0-9\p{L}]/u','',$middle_initial);
	$last_name = preg_replace('/[^- \'\+\_\.0-9\p{L}]/u','',$last_name);
		$last_name = preg_replace('/\+/',' ',$last_name);
	$address1 = preg_replace('/[^- \'\+\.\:\/\@\_0-9\p{L}]/u','',$address1);
	$address2 = preg_replace('/[^- \'\+\.\:\/\@\_0-9\p{L}]/u','',$address2);
	$address3 = preg_replace('/[^- \'\+\.\:\/\@\_0-9\p{L}]/u','',$address3);
		$address1 = preg_replace('/\+/',' ',$address1);
		$address2 = preg_replace('/\+/',' ',$address2);
		$address3 = preg_replace('/\+/',' ',$address3);
	$city = preg_replace('/[^- \'\+\.\:\/\@\_0-9\p{L}]/u','',$city);
		$city = preg_replace('/\+/',' ',$city);
	$state = preg_replace('/[^- 0-9\p{L}]/u','',$state);
	$province = preg_replace('/[^- \'\+\.\_0-9\p{L}]/u','',$province);
		$province = preg_replace('/\+/',' ',$province);
	$postal_code = preg_replace('/[^- \'\+0-9\p{L}]/u','',$postal_code);
		$postal_code = preg_replace('/\+/',' ',$postal_code);
	$country_code = preg_replace('/[^-_0-9\p{L}]/u','',$country_code);
	$alt_phone = preg_replace('/[^- \'\+\_\.0-9\p{L}]/u','',$alt_phone);
		$alt_phone = preg_replace('/\+/',' ',$alt_phone);
	$email = preg_replace('/[^- \'\+\.\:\/\@\%\_0-9\p{L}]/u','',$email);
		$email = preg_replace('/\+/',' ',$email);
	$security_phrase = preg_replace('/[^- \'\+\.\:\/\@\_0-9\p{L}]/u','',$security_phrase);
		$security_phrase = preg_replace('/\+/',' ',$security_phrase);
	$comments = preg_replace('/;|#|\"/','',$comments);
		$comments = preg_replace('/\+/',' ',$comments);
	$campaign_id = preg_replace('/[^-\_0-9\p{L}]/u', '',$campaign_id);
	$multi_alt_phones = preg_replace('/[^- \+\!\:\_0-9\p{L}]/u','',$multi_alt_phones);
		$multi_alt_phones = preg_replace('/\+/',' ',$multi_alt_phones);
	$source = preg_replace('/[^0-9\p{L}]/u','',$source);
	$phone_login = preg_replace('/[^-\_0-9\p{L}]/u', '',$phone_login);
	$owner = preg_replace('/[^- \'\+\.\:\/\@\_0-9\p{L}]/u','',$owner);
		$owner = preg_replace('/\+/',' ',$owner);
	$user_field = preg_replace('/[^-_0-9\p{L}]/u','',$user_field);
	$voicemail_id=preg_replace('/[^0-9\p{L}]/u','',$voicemail_id);
	$agent_pass=preg_replace('/[^-_0-9\p{L}]/u','',$agent_pass);
	$agent_full_name=preg_replace('/[^- \+\.\:\/\@\_0-9\p{L}]/u','',$agent_full_name);
	$agent_user_group=preg_replace('/[^-_0-9\p{L}]/u','',$agent_user_group);
	$phone_pass=preg_replace('/[^-_0-9\p{L}]/u','',$phone_pass);
	$custom_one=preg_replace('/[^- \+\.\:\/\@\_0-9\p{L}]/u','',$custom_one);
	$custom_two=preg_replace('/[^- \+\.\:\/\@\_0-9\p{L}]/u','',$custom_two);
	$custom_three=preg_replace('/[^- \+\.\:\/\@\_0-9\p{L}]/u','',$custom_three);
	$custom_four=preg_replace('/[^- \+\.\:\/\@\_0-9\p{L}]/u','',$custom_four);
	$custom_five=preg_replace('/[^- \+\.\:\/\@\_0-9\p{L}]/u','',$custom_five);
	$extension=preg_replace('/[^-_0-9\p{L}]/u','',$extension);
	$dialplan_number=preg_replace('/[^\*\#0-9\p{L}]/u','',$dialplan_number);
	$registration_password=preg_replace('/[^-_0-9\p{L}]/u','',$registration_password);
	$phone_full_name=preg_replace('/[^- \+\.\_0-9\p{L}]/u','',$phone_full_name);
	$outbound_cid=preg_replace('/[^-_0-9\p{L}]/u','',$outbound_cid);
	$phone_context=preg_replace('/[^-_0-9\p{L}]/u','',$phone_context);
	$list_name=preg_replace('/[^- \+\.\:\/\@\?\&\_0-9\p{L}]/u','',$list_name);
	$script=preg_replace('/[^-_0-9\p{L}]/u','',$script);
	$am_message=preg_replace('/[^-_0-9\p{L}]/u','',$am_message);
	$drop_inbound_group=preg_replace('/[^-_0-9\p{L}]/u','',$drop_inbound_group);
	$web_form_address=preg_replace('/[^- %=\+\.\:\/\@\?\&\_0-9\p{L}]/u','',$web_form_address);
	$web_form_address_two=preg_replace('/[^- %=\+\.\:\/\@\?\&\_0-9\p{L}]/u','',$web_form_address_two);
	$web_form_address_three=preg_replace('/[^- %=\+\.\:\/\@\?\&\_0-9\p{L}]/u','',$web_form_address_three);
	$dispo_call_url=preg_replace('/[^- %=\+\.\:\/\@\?\&\_0-9\p{L}]/u','',$dispo_call_url);
	$url_address=preg_replace('/[^- %=\+\.\:\/\@\?\&\_0-9\p{L}]/u','',$url_address);
	$uniqueid=preg_replace('/[^- \.\_0-9\p{L}]/u','',$uniqueid);
	$alias_id = preg_replace('/[^-\_0-9\p{L}]/u', '',$alias_id);
	$phone_logins = preg_replace('/[^-\,\_0-9\p{L}]/u','',$phone_logins);
	$alias_name = preg_replace('/[^- \+\.\:\/\@\_0-9\p{L}]/u','',$alias_name);
	$delete_alias = preg_replace('/[^A-Z]/','',$delete_alias);
	$callback_status = preg_replace('/[^-\_0-9\p{L}]/u', '',$callback_status);
	$callback_user = preg_replace('/[^-\_0-9\p{L}]/u', '',$callback_user);
	$callback_comments = preg_replace('/[^- \+\.\:\/\@\_0-9\p{L}]/u','',$callback_comments);
	$admin_user_group = preg_replace('/[^-\_0-9\p{L}]/u', '',$admin_user_group);
	$group_alias_id = preg_replace('/[^\_0-9\p{L}]/u','',$group_alias_id);
	$group_alias_name = preg_replace('/[^- \+\_0-9\p{L}]/u','',$group_alias_name);
	$caller_id_number = preg_replace('/[^0-9]/','',$caller_id_number);
	$caller_id_name = preg_replace('/[^- \+\_0-9\p{L}]/u','',$caller_id_name);
	$user_groups = preg_replace('/[^-\|\,\_0-9\p{L}]/u','',$user_groups); #JCJ
	$in_groups = preg_replace('/[^-\|\,\_0-9\p{L}]/u','',$in_groups); #JCJ
	$group = preg_replace('/[^-\|\_0-9\p{L}]/u','',$group);
	$call_id = preg_replace('/[^0-9\p{L}]/u','',$call_id);
	$expiration_date = preg_replace('/[^-_0-9\p{L}]/u','',$expiration_date);
	$local_call_time = preg_replace('/[^-_0-9\p{L}]/u','',$local_call_time);
	$campaigns = preg_replace('/[^-\,\|\_0-9\p{L}]/u','',$campaigns); # JCJ
	$campaign_name = preg_replace('/[^- \.\,\_0-9\p{L}]/u','',$campaign_name);
	$field_name = preg_replace('/[^-_0-9\p{L}]/u','',$field_name);
	$lead_filter_id = preg_replace('/[^-_0-9\p{L}]/u','',$lead_filter_id);
	$outbound_alt_cid = preg_replace('/[^0-9\p{L}]/u','',$outbound_alt_cid);
	$did_pattern = preg_replace('/[^:\+\*\#\.\_0-9\p{L}]/u','',$did_pattern);
	$source_did_pattern = preg_replace('/[^:\+\*\#\.\_0-9\p{L}]/u','',$source_did_pattern);
	$new_dids = preg_replace('/[^:\+\,\*\#\.\_0-9\p{L}]/u','',$new_dids);
	$did_description = preg_replace('/[^- \.\,\_0-9\p{L}]/u','',$did_description);
	$did_route = preg_replace('/[^-_0-9\p{L}]/u','',$did_route);
	$record_call = preg_replace('/[^-_0-9\p{L}]/u','',$record_call);
	$exten_context = preg_replace('/[^-_0-9\p{L}]/u','',$exten_context);
	$voicemail_ext = preg_replace('/[^\*\#\.\_0-9\p{L}]/u','',$voicemail_ext);
	$extension = preg_replace('/[^-\*\#\.\:\/\@\_0-9\p{L}]/u','',$extension);
	$filter_clean_cid_number = preg_replace('/[^- \.\,\_0-9\p{L}]/u','',$filter_clean_cid_number);
	$ignore_agentdirect = preg_replace('/[^A-Z]/','',$ignore_agentdirect);
	$areacode = preg_replace('/[^-_0-9\p{L}]/u','',$areacode);
	$cid_group_id = preg_replace('/[^-_0-9\p{L}]/u','',$cid_group_id);
	$cid_description = preg_replace('/[^- \.\,\_0-9\p{L}]/u','',$cid_description);
	$custom_fields_copy = preg_replace('/[^0-9]/','',$custom_fields_copy);
	if ($outbound_cid != '---ALL---')
		{$outbound_cid=preg_replace('/[^0-9]/','',$outbound_cid);}
	if ($areacode != '---ALL---')
		{$areacode=preg_replace('/[^0-9\p{L}]/u','',$areacode);}
	$leads_counts = preg_replace('/[^-_0-9\p{L}]/u','',$leads_counts);
	$remove_from_hopper=preg_replace('/[^0-9\p{L}]/u','',$remove_from_hopper);
	$list_description=preg_replace('/[^- \+\.\:\/\@\?\&\_0-9\p{L}]/u','',$list_description);
	$custom_order = preg_replace('/[^-_0-9\p{L}]/u','',$custom_order);
	$custom_copy_method = preg_replace('/[^-_0-9\p{L}]/u','',$custom_copy_method);
	$duration = preg_replace('/[^-_0-9\p{L}]/u','',$duration);
	$is_webphone = preg_replace('/[^-_0-9\p{L}]/u','',$is_webphone);
	$webphone_auto_answer = preg_replace('/[^-_0-9\p{L}]/u','',$webphone_auto_answer);
	$use_external_server_ip = preg_replace('/[^-_0-9\p{L}]/u','',$use_external_server_ip);
	$template_id = preg_replace('/[^-_0-9\p{L}]/u','',$template_id);
	$on_hook_agent = preg_replace('/[^-_0-9\p{L}]/u','',$on_hook_agent);
	$delete_did = preg_replace('/[^0-9\p{L}]/u','',$delete_did);
	$group_by_campaign = preg_replace('/[^0-9\p{L}]/u','',$group_by_campaign);
	$source_user = preg_replace('/[^-_0-9\p{L}]/u','',$source_user);
	$menu_id = preg_replace('/[^-_0-9\p{L}]/u','',$menu_id);
	$xferconf_one=preg_replace('/[^-_0-9\p{L}]/u','',$xferconf_one);
	$xferconf_two=preg_replace('/[^-_0-9\p{L}]/u','',$xferconf_two);
	$xferconf_three=preg_replace('/[^-_0-9\p{L}]/u','',$xferconf_three);
	$xferconf_four=preg_replace('/[^-_0-9\p{L}]/u','',$xferconf_four);
	$xferconf_five=preg_replace('/[^-_0-9\p{L}]/u','',$xferconf_five);
	$field_label = preg_replace('/[^_0-9\p{L}]/u','',$field_label);
	$field_show_hide = preg_replace('/[^_0-9\p{L}]/u','',$field_show_hide);
	$field_name = preg_replace('/[^ \.\,-\_0-9\p{L}]/u','',$field_name);
	$field_description = preg_replace('/[^ \.\,-\_0-9\p{L}]/u','',$field_description);
	$field_options = preg_replace('/[^ \'\&\.\n\|\,-\_0-9\p{L}]/u', '',$field_options);
	if ($field_type != 'SCRIPT')
		{$field_options = preg_replace('/[^ \.\n\|\,-\_0-9\p{L}]/u', '',$field_options);}
	$field_help = preg_replace('/[^ \'\&\.\n\|\,-\_0-9\p{L}]/u', '',$field_help);
	$field_default = preg_replace('/[^ \.\n\,-\_0-9\p{L}]/u', '',$field_default);
	$group_id = preg_replace('/[^_0-9\p{L}]/u','',$group_id);
	$url_statuses = preg_replace('/[^- 0-9\p{L}]/u', '',$url_statuses);
	$url_description = preg_replace('/[^ \.\,-\_0-9\p{L}]/u','',$url_description);
	$preset_name = preg_replace('/[^- \_0-9\p{L}]/u','',$preset_name);
	$dial_status_add=preg_replace('/[^-_0-9\p{L}]/u','',$dial_status_add);
	$dial_status_remove=preg_replace('/[^-_0-9\p{L}]/u','',$dial_status_remove);
	}

$USarea = 			substr($phone_number, 0, 3);
$USprefix = 		substr($phone_number, 3, 3);
if (strlen($hopper_priority)<1) {$hopper_priority=0;}
if ($hopper_priority < -99) {$hopper_priority=-99;}
if ($hopper_priority > 99) {$hopper_priority=99;}
if (preg_match("/^Y$/",$remove_from_hopper)) {$add_to_hopper='N';}

$StarTtime = date("U");
$NOW_DATE = date("Y-m-d");
$NOW_TIME = date("Y-m-d H:i:s");
$CIDdate = date("mdHis");
$ENTRYdate = date("YmdHis");
$ip = getenv("REMOTE_ADDR");
$PHP_SELF=$_SERVER['PHP_SELF'];
$PHP_SELF = preg_replace('/\.php.*/i','.php',$PHP_SELF);
$query_string = getenv("QUERY_STRING");
$REQUEST_URI = getenv("REQUEST_URI");
$POST_URI = '';
foreach($_POST as $key=>$value)
	{$POST_URI .= '&'.$key.'='.$value;}
if (strlen($POST_URI)>1)
	{$POST_URI = preg_replace("/^&/",'',$POST_URI);}
$REQUEST_URI = preg_replace("/'|\"|\\\\|;/","",$REQUEST_URI);
$POST_URI = preg_replace("/'|\"|\\\\|;/","",$POST_URI);
if ( (strlen($query_string) < 1) and (strlen($POST_URI) > 2) )
	{$query_string = $POST_URI;}
if ( (strlen($query_string) > 0) and (strlen($POST_URI) > 2) )
	{$query_string .= "&GET-AND-POST=Y&".$POST_URI;}
$barge_prefix='';

$MT[0]='';
$api_script = 'non-agent';
$api_logging = 1;

$vicidial_list_fields = '|lead_id|vendor_lead_code|source_id|list_id|gmt_offset_now|called_since_last_reset|phone_code|phone_number|title|first_name|middle_initial|last_name|address1|address2|address3|city|state|province|postal_code|country_code|gender|date_of_birth|alt_phone|email|security_phrase|comments|called_count|last_local_call_time|rank|owner|';

$secX = date("U");
$hour = date("H");
$min = date("i");
$sec = date("s");
$mon = date("m");
$mday = date("d");
$year = date("Y");
$isdst = date("I");
$Shour = date("H");
$Smin = date("i");
$Ssec = date("s");
$Smon = date("m");
$Smday = date("d");
$Syear = date("Y");
$pulldate0 = "$year-$mon-$mday $hour:$min:$sec";
$inSD = $pulldate0;
$dsec = ( ( ($hour * 3600) + ($min * 60) ) + $sec );

### Grab Server system settings from the database
$stmt="SELECT local_gmt FROM servers where active='Y' limit 1;";
if ($non_latin > 0) {$rslt=mysql_to_mysqli("SET NAMES 'UTF8'", $link);}
$rslt=mysql_to_mysqli($stmt, $link);
$gmt_recs = mysqli_num_rows($rslt);
if ($gmt_recs > 0)
	{
	$row=mysqli_fetch_row($rslt);
	$DBSERVER_GMT =			$row[0];
	if (strlen($DBSERVER_GMT)>0)	{$SERVER_GMT = $DBSERVER_GMT;}
	if ($isdst) {$SERVER_GMT++;} 
	}
else
	{
	$SERVER_GMT = date("O");
	$SERVER_GMT = preg_replace('/\+/i', '',$SERVER_GMT);
	$SERVER_GMT = ($SERVER_GMT + 0);
	$SERVER_GMT = ($SERVER_GMT / 100);
	}

$LOCAL_GMT_OFF = $SERVER_GMT;
$LOCAL_GMT_OFF_STD = $SERVER_GMT;

if ($archived_lead=="Y") {$vicidial_list_table="vicidial_list_archive";} 
else {$vicidial_list_table="vicidial_list"; $archived_lead="N";}





################################################################################
### version - show version, date, time and time zone information for the API
################################################################################
if ($function == 'version')
	{
	$data = "VERSION: $version|BUILD: $build|DATE: $NOW_TIME|EPOCH: $StarTtime|DST: $isdst|TZ: $DBSERVER_GMT|TZNOW: $SERVER_GMT|";
	$result = 'SUCCESS';
	echo "$data\n";
	api_log($link,$api_logging,$api_script,$user,$agent_user,$function,$value,$result,$result_reason,$source,$data);
	exit;
	}
################################################################################
### END version
################################################################################




################################################################################
### BEGIN - coffee/teapot 418 - reject coffee requests
################################################################################
if ( ($function == 'coffee') or ($function == 'start_coffee') or ($function == 'make_coffee') or ($function == 'brew_coffee') )
	{
	$data = _QXZ("Coffee").": $function|Error 418 I'm a teapot";
	$result = _QXZ("ERROR");
	Header("HTTP/1.0 418 I'm a teapot");
	echo "$data";
	api_log($link,$api_logging,$api_script,$user,$agent_user,$function,$value,$result,$result_reason,$source,$data);
	exit;
	}
################################################################################
### END - coffee/teapot
################################################################################




##### BEGIN user authentication for all functions below #####
$auth=0;
$auth_message = user_authorization($user,$pass,'REPORTS',1,1);
if ($auth_message == 'GOOD')
	{$auth=1;}

if ($auth < 1)
	{
	if ( ($function == 'blind_monitor') and ($source == 'queuemetrics') and ($stage == 'MONITOR') )
		{
		$stmt="SELECT count(*) from vicidial_auto_calls where callerid='$pass';";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {echo "$stmt\n";}
		$monauth_to_check = mysqli_num_rows($rslt);
		if ($monauth_to_check > 0)
			{
			$rowvac=mysqli_fetch_row($rslt);
			$auth =	$rowvac[0];
			}
		}
	}
if ($auth < 1)
	{
	$VDdisplayMESSAGE = "ERROR: Login incorrect, please try again";
	if ($auth_message == 'LOCK')
		{
		$VDdisplayMESSAGE = "ERROR: Too many login attempts, try again in 15 minutes";
		Header ("Content-type: text/html; charset=utf-8");
		echo "$VDdisplayMESSAGE: |$user|$auth_message|\n";
		exit;
		}
	if ($auth_message == 'IPBLOCK')
		{
		$VDdisplayMESSAGE = "ERROR: Your IP Address is not allowed: $ip";
		Header ("Content-type: text/html; charset=utf-8");
		echo "$VDdisplayMESSAGE: |$PHP_AUTH_USER|$auth_message|\n";
		exit;
		}
	Header ("Content-type: text/html; charset=utf-8");
	echo "$VDdisplayMESSAGE: |$user|$pass|$auth_message|\n";
	exit;
	}

$stmt="SELECT api_list_restrict,api_allowed_functions,user_group,selected_language,delete_inbound_dids,download_invalid_files,user_level from vicidial_users where user='$user' and active='Y';";
if ($DB>0) {echo "DEBUG: auth query - $stmt\n";}
$rslt=mysql_to_mysqli($stmt, $link);
$row=mysqli_fetch_row($rslt);
$api_list_restrict =		$row[0];
$api_allowed_functions =	$row[1];
$LOGuser_group =			$row[2];
$VUselected_language =		$row[3];
$VUdelete_inbound_dids =	$row[4];
$VUdownload_invalid_files = $row[5];
$VUuser_level =				$row[6];

if ( ($api_list_restrict > 0) and ( ($function == 'add_lead') or ($function == 'update_lead') or ($function == 'batch_update_lead') or ($function == 'update_list') or ($function == 'list_info') or ($function == 'list_custom_fields') or ($function == 'lead_search') ) )
	{
	$stmt="SELECT allowed_campaigns from vicidial_user_groups where user_group='$LOGuser_group';";
	if ($DB>0) {echo "|$stmt|\n";}
	$rslt=mysql_to_mysqli($stmt, $link);
	$ss_conf_ct = mysqli_num_rows($rslt);
	if ($ss_conf_ct > 0)
		{
		$row=mysqli_fetch_row($rslt);
		$LOGallowed_campaigns =			$row[0];
		$LOGallowed_campaignsSQL='';
		$whereLOGallowed_campaignsSQL='';
		if ( (!preg_match('/\-ALL/i', $LOGallowed_campaigns)) )
			{
			$rawLOGallowed_campaignsSQL = preg_replace("/ -/",'',$LOGallowed_campaigns);
			$rawLOGallowed_campaignsSQL = preg_replace("/ /","','",$rawLOGallowed_campaignsSQL);
			$LOGallowed_campaignsSQL = "and campaign_id IN('$rawLOGallowed_campaignsSQL')";
			$whereLOGallowed_campaignsSQL = "where campaign_id IN('$rawLOGallowed_campaignsSQL')";
			}
		$stmt="SELECT list_id from vicidial_lists $whereLOGallowed_campaignsSQL order by list_id;";
		if ($DB>0) {echo "|$stmt|\n";}
		$rslt=mysql_to_mysqli($stmt, $link);
		$lists_to_print = mysqli_num_rows($rslt);
		$i=0;
		$allowed_lists=' ';
		$allowed_listsSQL='';
		while ($i < $lists_to_print)
			{
			$row=mysqli_fetch_row($rslt);
			$allowed_lists .=		"$row[0] ";
			$allowed_listsSQL .=	"'$row[0]',";
			$i++;
			}
		$allowed_listsSQL = preg_replace("/,$/",'',$allowed_listsSQL);
		if ($DB>0) {echo "Allowed lists:|$allowed_lists|$allowed_listsSQL|\n";}
		}
	else
		{
		$result = 'ERROR';
		$result_reason = "user_group DOES NOT EXIST";
		echo "$result: $result_reason: |$user|$LOGuser_group|\n";
		$data = "$allowed_user";
		api_log($link,$api_logging,$api_script,$user,$agent_user,$function,$value,$result,$result_reason,$source,$data);
		exit;
		}
	}
##### END user authentication for all functions below #####


################################################################################
### add_lead - inserts a lead into the vicidial_list table
################################################################################
if ($function == 'add_lead')
	{
	$list_id = preg_replace('/[^0-9]/','',$list_id);
	if(strlen($source)<2)
		{
		$result = 'ERROR';
		$result_reason = "Invalid Source";
		echo "$result: $result_reason - $source\n";
		api_log($link,$api_logging,$api_script,$user,$agent_user,$function,$value,$result,$result_reason,$source,$data);
		echo "ERROR: Invalid Source: |$source|\n";
		exit;
		}
	else
		{
		if ( (!preg_match("/ $function /",$api_allowed_functions)) and (!preg_match("/ALL_FUNCTIONS/",$api_allowed_functions)) )
			{
			$result = 'ERROR';
			$result_reason = "auth USER DOES NOT HAVE PERMISSION TO USE THIS FUNCTION";
			echo "$result: $result_reason: |$user|$function|\n";
			$data = "$allowed_user";
			api_log($link,$api_logging,$api_script,$user,$agent_user,$function,$value,$result,$result_reason,$source,$data);
			exit;
			}
		$stmt="SELECT count(*) from vicidial_users where user='$user' and vdc_agent_api_access='1' and modify_leads IN('1','2','3','4') and user_level > 7 and active='Y';";
		$rslt=mysql_to_mysqli($stmt, $link);
		$row=mysqli_fetch_row($rslt);
		$modify_leads=$row[0];

		if ($modify_leads < 1)
			{
			$result = 'ERROR';
			$result_reason = "add_lead USER DOES NOT HAVE PERMISSION TO ADD LEADS TO THE SYSTEM";
			echo "$result: $result_reason: |$user|$modify_leads|\n";
			$data = "$modify_leads";
			api_log($link,$api_logging,$api_script,$user,$agent_user,$function,$value,$result,$result_reason,$source,$data);
			exit;
			}
		else
			{
			if ($api_list_restrict > 0)
				{
				if (!preg_match("/ $list_id /",$allowed_lists))
					{
					$result = 'ERROR';
					$result_reason = "add_lead NOT AN ALLOWED LIST ID";
					$data = "$phone_number|$list_id";
					echo "$result: $result_reason - $data\n";
					api_log($link,$api_logging,$api_script,$user,$agent_user,$function,$value,$result,$result_reason,$source,$data);
					exit;
					}
				}
			if (preg_match("/Y/i",$list_exists_check))
				{
				$stmt="SELECT count(*) from vicidial_lists where list_id='$list_id';";
				$rslt=mysql_to_mysqli($stmt, $link);
				$row=mysqli_fetch_row($rslt);
				if ($DB>0) {echo "DEBUG: add_lead list_exists_check query - $row[0]|$stmt\n";}
				$list_exists_count = $row[0];
				if ($list_exists_count < 1)
					{
					$result = 'ERROR';
					$result_reason = "add_lead NOT A DEFINED LIST ID, LIST EXISTS CHECK ENABLED";
					$data = "$phone_number|$list_id";
					echo "$result: $result_reason - $data\n";
					api_log($link,$api_logging,$api_script,$user,$agent_user,$function,$value,$result,$result_reason,$source,$data);
					exit;
					}
				}
			if (strlen($gender)<1) {$gender='U';}
			if (strlen($rank)<1) {$rank='0';}
			if (strlen($list_id)<3) {$list_id='999';}
			if (strlen($phone_code)<1) {$phone_code='1';}
			if ( ($nanpa_ac_prefix_check == 'Y') or (preg_match("/NANPA/i",$tz_method)) )
				{
				$stmt="SELECT count(*) from vicidial_nanpa_prefix_codes;";
				$rslt=mysql_to_mysqli($stmt, $link);
				$row=mysqli_fetch_row($rslt);
				$vicidial_nanpa_prefix_codes_count = $row[0];
				if ($vicidial_nanpa_prefix_codes_count < 10)
					{
					$nanpa_ac_prefix_check='N';
					$tz_method = preg_replace("/NANPA/",'',$tz_method);

					$result = 'NOTICE';
					$result_reason = "add_lead NANPA options disabled, NANPA prefix data not loaded";
					echo "$result: $result_reason - $vicidial_nanpa_prefix_codes_count|$user\n";
					$data = "$inserted_alt_phones|$lead_id";
					api_log($link,$api_logging,$api_script,$user,$agent_user,$function,$value,$result,$result_reason,$source,$data);
					}
				}

			$valid_number=1;
			if ( (strlen($phone_number)<6) || (strlen($phone_number)>16) )
				{
				$valid_number=0;
				$result_reason = "add_lead INVALID PHONE NUMBER LENGTH";
				}
			if ( ($usacan_prefix_check=='Y') and ($valid_number > 0) )
				{
				$USprefix = 	substr($phone_number, 3, 1);
				if ($DB>0) {echo "DEBUG: add_lead prefix check - $USprefix|$phone_number\n";}
				if ($USprefix < 2)
					{
					$valid_number=0;
					$result_reason = "add_lead INVALID PHONE NUMBER PREFIX";
					}
				}
			if ( ($usacan_areacode_check=='Y') and ($valid_number > 0) )
				{
				$phone_areacode = substr($phone_number, 0, 3);
				$stmt = "select count(*) from vicidial_phone_codes where areacode='$phone_areacode' and country_code='1';";
				if ($DB>0) {echo "DEBUG: add_lead areacode check query - $stmt\n";}
				$rslt=mysql_to_mysqli($stmt, $link);
				$row=mysqli_fetch_row($rslt);
				$valid_number=$row[0];
				if ( ($valid_number < 1) || (strlen($phone_number)>10) || (strlen($phone_number)<10) )
					{
					$result_reason = "add_lead INVALID PHONE NUMBER AREACODE";
					}
				}
			if ( ($nanpa_ac_prefix_check=='Y') and ($valid_number > 0) )
				{
				$phone_areacode = substr($phone_number, 0, 3);
				$phone_prefix = substr($phone_number, 3, 3);
				$stmt = "SELECT count(*) from vicidial_nanpa_prefix_codes where areacode='$phone_areacode' and prefix='$phone_prefix';";
				if ($DB>0) {echo "DEBUG: add_lead areacode check query - $stmt\n";}
				$rslt=mysql_to_mysqli($stmt, $link);
				$row=mysqli_fetch_row($rslt);
				$valid_number=$row[0];
				if ($valid_number < 1)
					{
					$result_reason = "add_lead INVALID PHONE NUMBER NANPA AREACODE PREFIX";
					}
				}
			if ($valid_number < 1)
				{
				$result = 'ERROR';
				echo "$result: $result_reason - $phone_number|$user\n";
				$data = "$phone_number";
				api_log($link,$api_logging,$api_script,$user,$agent_user,$function,$value,$result,$result_reason,$source,$data);
				exit;
				}
			else
				{
				### state lookup if enabled
				if ( ($lookup_state == 'Y') and (strlen($state) < 1) )
					{
					$phone_areacode = substr($phone_number, 0, 3);
					$stmt="SELECT state from vicidial_phone_codes where country_code='$phone_code' and areacode='$phone_areacode';";
					$rslt=mysql_to_mysqli($stmt, $link);
					$vpc_recs = mysqli_num_rows($rslt);
					if ($vpc_recs > 0)
						{
						$row=mysqli_fetch_row($rslt);
						$state =	$row[0];
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

					### BEGIN custom fields insert section ###
					if ($custom_fields == 'Y')
						{
						if ($custom_fields_enabled > 0)
							{
							$stmt="SHOW TABLES LIKE \"custom_$list_id\";";
							if ($DB>0) {echo "$stmt\n";}
							$rslt=mysql_to_mysqli($stmt, $link);
							$tablecount_to_print = mysqli_num_rows($rslt);
							if ($tablecount_to_print > 0) 
								{
								$CFinsert_SQL='';
								$stmt="SELECT field_id,field_label,field_name,field_description,field_rank,field_help,field_type,field_options,field_size,field_max,field_default,field_cost,field_required,multi_position,name_position,field_order,field_encrypt from vicidial_lists_fields where list_id='$list_id' and field_duplicate!='Y' order by field_rank,field_order,field_label;";
								if ($DB>0) {echo "$stmt\n";}
								$rslt=mysql_to_mysqli($stmt, $link);
								$fields_to_print = mysqli_num_rows($rslt);
								$fields_list='';
								$o=0;
								while ($fields_to_print > $o) 
									{
									$new_field_value='';
									$form_field_value='';
									$rowx=mysqli_fetch_row($rslt);
									$A_field_id[$o] =			$rowx[0];
									$A_field_label[$o] =		$rowx[1];
									$A_field_name[$o] =			$rowx[2];
									$A_field_type[$o] =			$rowx[6];
									$A_field_size[$o] =			$rowx[8];
									$A_field_max[$o] =			$rowx[9];
									$A_field_required[$o] =		$rowx[12];
									$A_field_encrypt[$o] =		$rowx[16];
									$A_field_value[$o] =		'';
									$field_name_id =			$A_field_label[$o];

									if (isset($_GET["$field_name_id"]))				{$form_field_value=$_GET["$field_name_id"];}
										elseif (isset($_POST["$field_name_id"]))	{$form_field_value=$_POST["$field_name_id"];}

									$form_field_value = preg_replace("/\+/"," ",$form_field_value);
									$form_field_value = preg_replace("/;|\"/","",$form_field_value);
									$form_field_value = preg_replace("/\\b/","",$form_field_value);
									$form_field_value = preg_replace("/\\\\$/","",$form_field_value);
									$A_field_value[$o] = $form_field_value;

									if ( ($A_field_type[$o]=='DISPLAY') or ($A_field_type[$o]=='SCRIPT') )
										{
										$A_field_value[$o]='----IGNORE----';
										}
									else
										{
										if (!preg_match("/\|$A_field_label[$o]\|/",$vicidial_list_fields))
											{
											if ( (preg_match("/cf_encrypt/",$active_modules)) and ($A_field_encrypt[$o] == 'Y') and (strlen($A_field_value[$o]) > 0) )
												{
												$field_enc=$MT;
												$A_field_valueSQL[$o] = base64_encode($A_field_value[$o]);
												exec("../agc/aes.pl --encrypt --text=$A_field_valueSQL[$o]", $field_enc);
												$field_enc_ct = count($field_enc);
												$k=0;
												$field_enc_all='';
												while ($field_enc_ct > $k)
													{
													$field_enc_all .= $field_enc[$k];
													$k++;
													}
												$A_field_valueSQL[$o] = preg_replace("/CRYPT: |\n|\r|\t/",'',$field_enc_all);
												}
											else
												{$A_field_valueSQL[$o] = $A_field_value[$o];}

											$CFinsert_SQL .= "$A_field_label[$o]=\"$A_field_valueSQL[$o]\",";
											}
										}
									$o++;
									}

								if (strlen($CFinsert_SQL)>3)
									{
									$CFinsert_SQL = preg_replace("/,$/","",$CFinsert_SQL);
									$CFinsert_SQL = preg_replace("/\"--BLANK--\"/",'""',$CFinsert_SQL);
									$custom_table_update_SQL = "INSERT INTO custom_$list_id SET lead_id='$lead_id',$CFinsert_SQL;";
									if ($DB>0) {echo "$custom_table_update_SQL\n";}
									$rslt=mysql_to_mysqli($custom_table_update_SQL, $link);
									$custom_insert_count = mysqli_affected_rows($link);
									if ($custom_insert_count > 0) 
										{
										# Update vicidial_list entry to put list_id as entry_list_id 
										$vl_table_entry_update_SQL = "UPDATE vicidial_list SET entry_list_id='$list_id' where lead_id='$lead_id';";
										$rslt=mysql_to_mysqli($vl_table_entry_update_SQL, $link);
										$vl_table_entry_update_count = mysqli_affected_rows($link);

										$result = 'NOTICE';
										$result_reason = "add_lead CUSTOM FIELDS VALUES ADDED";
										echo "$result: $result_reason - $phone_number|$lead_id|$list_id|$vl_table_entry_update_count\n";
										$data = "$phone_number|$lead_id|$list_id";
										api_log($link,$api_logging,$api_script,$user,$agent_user,$function,$value,$result,$result_reason,$source,$data);
										}
									else
										{
										$result = 'NOTICE';
										$result_reason = "add_lead CUSTOM FIELDS NOT ADDED, NO FIELDS TO UPDATE DEFINED";
										echo "$result: $result_reason - $phone_number|$lead_id|$list_id\n";
										$data = "$phone_number|$lead_id|$list_id";
										api_log($link,$api_logging,$api_script,$user,$agent_user,$function,$value,$result,$result_reason,$source,$data);
										}
									}
								else
									{
									$result = 'NOTICE';
									$result_reason = "add_lead CUSTOM FIELDS NOT ADDED, NO FIELDS DEFINED";
									echo "$result: $result_reason - $phone_number|$lead_id|$list_id\n";
									$data = "$phone_number|$lead_id|$list_id";
									api_log($link,$api_logging,$api_script,$user,$agent_user,$function,$value,$result,$result_reason,$source,$data);
									}
								}
							else
								{
								$result = 'NOTICE';
								$result_reason = "add_lead CUSTOM FIELDS NOT ADDED, NO CUSTOM FIELDS DEFINED FOR THIS LIST";
								echo "$result: $result_reason - $phone_number|$lead_id|$list_id\n";
								$data = "$phone_number|$lead_id|$list_id";
								api_log($link,$api_logging,$api_script,$user,$agent_user,$function,$value,$result,$result_reason,$source,$data);
								}
							}
						else
							{
							$result = 'NOTICE';
							$result_reason = "add_lead CUSTOM FIELDS NOT ADDED, CUSTOM FIELDS DISABLED";
							echo "$result: $result_reason - $phone_number|$lead_id|$custom_fields|$custom_fields_enabled\n";
							$data = "$phone_number|$lead_id|$custom_fields|$custom_fields_enabled";
							api_log($link,$api_logging,$api_script,$user,$agent_user,$function,$value,$result,$result_reason,$source,$data);
							}
						}
					### END custom fields insert section ###

					### BEGIN add to hopper section ###
					if ($add_to_hopper == 'Y')
						{
						$dialable=1;

						$stmt="SELECT vicidial_campaigns.local_call_time,vicidial_lists.local_call_time,vicidial_campaigns.campaign_id from vicidial_campaigns,vicidial_lists where list_id='$list_id' and vicidial_campaigns.campaign_id=vicidial_lists.campaign_id;";
						$rslt=mysql_to_mysqli($stmt, $link);
						$row=mysqli_fetch_row($rslt);
						$local_call_time =		$row[0];
						$list_local_call_time = $row[1];
						$VD_campaign_id =		$row[2];

						if ($DB > 0) {echo "DEBUG call time: |$local_call_time|$list_local_call_time|$VD_campaign_id|";}
						if ( ($list_local_call_time!='') and (!preg_match("/^campaign$/i",$list_local_call_time)) )
							{$local_call_time = $list_local_call_time;}

						if ($hopper_local_call_time_check == 'Y')
							{
							### call function to determine if lead is dialable
							$dialable = dialable_gmt($DB,$link,$local_call_time,$gmt_offset,$state);
							}
						if ($dialable < 1) 
							{
							$result = 'NOTICE';
							$result_reason = "add_lead NOT ADDED TO HOPPER, OUTSIDE OF LOCAL TIME";
							echo "$result: $result_reason - $phone_number|$lead_id|$gmt_offset|$dialable|$user\n";
							$data = "$phone_number|$lead_id|$gmt_offset|$dialable|$state";
							api_log($link,$api_logging,$api_script,$user,$agent_user,$function,$value,$result,$result_reason,$source,$data);
							}
						else
							{
							### insert record into vicidial_hopper for alt_phone call attempt
							$stmt = "INSERT INTO vicidial_hopper SET lead_id='$lead_id',campaign_id='$VD_campaign_id',status='READY',list_id='$list_id',gmt_offset_now='$gmt_offset',state='$state',user='',priority='$hopper_priority',source='P',vendor_lead_code=\"$vendor_lead_code\";";
							if ($DB>0) {echo "DEBUG: add_lead query - $stmt\n";}
							$rslt=mysql_to_mysqli($stmt, $link);
							$Haffected_rows = mysqli_affected_rows($link);
							if ($Haffected_rows > 0)
								{
								$hopper_id = mysqli_insert_id($link);

								$result = 'NOTICE';
								$result_reason = "add_lead ADDED TO HOPPER";
								echo "$result: $result_reason - $phone_number|$lead_id|$hopper_id|$user\n";
								$data = "$phone_number|$lead_id|$hopper_id";
								api_log($link,$api_logging,$api_script,$user,$agent_user,$function,$value,$result,$result_reason,$source,$data);
								}
							else
								{
								$result = 'NOTICE';
								$result_reason = "add_lead NOT ADDED TO HOPPER";
								echo "$result: $result_reason - $phone_number|$lead_id|$stmt|$user\n";
								$data = "$phone_number|$lead_id|$stmt";
								api_log($link,$api_logging,$api_script,$user,$agent_user,$function,$value,$result,$result_reason,$source,$data);
								}
							}
						}
					### END add to hopper section ###

					### BEGIN scheduled callback section ###
					if ($callback == 'Y')
						{
						$stmt="SELECT count(*) from vicidial_campaigns where campaign_id='$campaign_id';";
						$rslt=mysql_to_mysqli($stmt, $link);
						$camp_recs = mysqli_num_rows($rslt);
						if ($camp_recs > 0)
							{
							$row=mysqli_fetch_row($rslt);
							$camp_count =	$row[0];
							}
						if ($camp_count > 0)
							{
							$valid_callback=0;
							$user_group='';
							if ($callback_type == 'USERONLY')
								{
								$stmt="SELECT user_group from vicidial_users where user='$callback_user';";
								$rslt=mysql_to_mysqli($stmt, $link);
								$user_recs = mysqli_num_rows($rslt);
								if ($user_recs > 0)
									{
									$row=mysqli_fetch_row($rslt);
									$user_group =	$row[0];
									$valid_callback++;
									}
								else
									{
									$result = 'NOTICE';
									$result_reason = "add_lead SCHEDULED CALLBACK NOT ADDED, USER NOT VALID";
									$data = "$lead_id|$campaign_id|$callback_user|$callback_type";
									echo "$result: $result_reason - $data\n";
									api_log($link,$api_logging,$api_script,$user,$agent_user,$function,$value,$result,$result_reason,$source,$data);
									}
								}
							else
								{
								$callback_type='ANYONE';
								$callback_user='';
								$valid_callback++;
								}
							if ($valid_callback > 0)
								{
								if ($callback_datetime == 'NOW') 
									{$callback_datetime=$NOW_TIME;}
								if (preg_match("/\dDAYS$/i",$callback_datetime)) 
									{
									$callback_days = preg_replace('/[^0-9]/','',$callback_datetime);
									$callback_datetime = date("Y-m-d H:i:s", mktime(date("H"),date("i"),date("s"),date("m"),date("d")+$callback_days,date("Y")));
									}
								if (strlen($callback_status)<1) 
									{$callback_status='CALLBK';}

								$stmt="INSERT INTO vicidial_callbacks (lead_id,list_id,campaign_id,status,entry_time,callback_time,user,recipient,comments,user_group,lead_status) values('$lead_id','$list_id','$campaign_id','ACTIVE','$NOW_TIME','$callback_datetime','$callback_user','$callback_type','$callback_comments','$user_group','$callback_status');";
								if ($DB>0) {echo "DEBUG: add_lead query - $stmt\n";}
								$rslt=mysql_to_mysqli($stmt, $link);
								$CBaffected_rows = mysqli_affected_rows($link);

								$result = 'NOTICE';
								$result_reason = "add_lead SCHEDULED CALLBACK ADDED";
								$data = "$lead_id|$campaign_id|$callback_datetime|$callback_type|$callback_user|$callback_status";
								echo "$result: $result_reason - $data\n";
								api_log($link,$api_logging,$api_script,$user,$agent_user,$function,$value,$result,$result_reason,$source,$data);
								}
							}
						else
							{
							$result = 'NOTICE';
							$result_reason = "add_lead SCHEDULED CALLBACK NOT ADDED, CAMPAIGN NOT VALID";
							$data = "$lead_id|$campaign_id";
							echo "$result: $result_reason - $data\n";
							api_log($link,$api_logging,$api_script,$user,$agent_user,$function,$value,$result,$result_reason,$source,$data);
							}
						}
					### END scheduled callback section ###
					}
				else
					{
					$result = 'ERROR';
					$result_reason = "add_lead LEAD HAS NOT BEEN ADDED";
					echo "$result: $result_reason - $phone_number|$list_id|$stmt|$user\n";
					$data = "$phone_number|$list_id|$stmt";
					api_log($link,$api_logging,$api_script,$user,$agent_user,$function,$value,$result,$result_reason,$source,$data);
					}
				}
			}
		exit;
		}
	}
################################################################################
### END add_lead
################################################################################


$result = 'ERROR';
$result_reason = "NO FUNCTION SPECIFIED";
echo "$result: $result_reason\n";
api_log($link,$api_logging,$api_script,$user,$agent_user,$function,$value,$result,$result_reason,$source,$data);




if ($format=='debug') 
	{
	$ENDtime = date("U");
	$RUNtime = ($ENDtime - $StarTtime);
	echo "\n<!-- script runtime: $RUNtime seconds -->";
	echo "\n</body>\n</html>\n";
	}
		
exit; 

##### Logging #####
function api_log($link,$api_logging,$api_script,$user,$agent_user,$function,$value,$result,$result_reason,$source,$data)
	{
	if ($api_logging > 0)
		{
		global $startMS, $query_string, $ip;

		$CL=':';
		$script_name = getenv("SCRIPT_NAME");
		$server_name = getenv("SERVER_NAME");
		$server_port = getenv("SERVER_PORT");
		if (preg_match("/443/i",$server_port)) {$HTTPprotocol = 'https://';}
		  else {$HTTPprotocol = 'http://';}
		if (($server_port == '80') or ($server_port == '443') ) {$server_port='';}
		else {$server_port = "$CL$server_port";}
		$apiPAGE = "$HTTPprotocol$server_name$server_port$script_name";
		$apiURL = $apiPAGE . '?' . $query_string;

		$endMS = microtime();
		$startMSary = explode(" ",$startMS);
		$endMSary = explode(" ",$endMS);
		$runS = ($endMSary[0] - $startMSary[0]);
		$runM = ($endMSary[1] - $startMSary[1]);
		$TOTALrun = ($runS + $runM);

		$VULhostname = php_uname('n');
		$VULservername = $_SERVER['SERVER_NAME'];
		if (strlen($VULhostname)<1) {$VULhostname='X';}
		if (strlen($VULservername)<1) {$VULservername='X';}

		$stmt="SELECT webserver_id FROM vicidial_webservers where webserver='$VULservername' and hostname='$VULhostname' LIMIT 1;";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {echo "$stmt\n";}
		$webserver_id_ct = mysqli_num_rows($rslt);
		if ($webserver_id_ct > 0)
			{
			$row=mysqli_fetch_row($rslt);
			$webserver_id = $row[0];
			}
		else
			{
			##### insert webserver entry
			$stmt="INSERT INTO vicidial_webservers (webserver,hostname) values('$VULservername','$VULhostname');";
			if ($DB) {echo "$stmt\n";}
			$rslt=mysql_to_mysqli($stmt, $link);
			$affected_rows = mysqli_affected_rows($link);
			$webserver_id = mysqli_insert_id($link);
			}

		$stmt="SELECT url_id FROM vicidial_urls where url='$apiPAGE' LIMIT 1;";
		$rslt=mysql_to_mysqli($stmt, $link);
		if ($DB) {echo "$stmt\n";}
		$url_id_ct = mysqli_num_rows($rslt);
		if ($url_id_ct > 0)
			{
			$row=mysqli_fetch_row($rslt);
			$url_id = $row[0];
			}
		else
			{
			##### insert url entry
			$stmt="INSERT INTO vicidial_urls (url) values('$apiPAGE');";
			if ($DB) {echo "$stmt\n";}
			$rslt=mysql_to_mysqli($stmt, $link);
			$affected_rows = mysqli_affected_rows($link);
			$url_id = mysqli_insert_id($link);
			}

		$NOW_TIME = date("Y-m-d H:i:s");
		$data = preg_replace("/\"/","'",$data);
		$stmt="INSERT INTO vicidial_api_log set user='$user',agent_user='$agent_user',function='$function',value='$value',result=\"$result\",result_reason='$result_reason',source='$source',data=\"$data\",api_date='$NOW_TIME',api_script='$api_script',run_time='$TOTALrun',webserver='$webserver_id',api_url='$url_id';";
		$rslt=mysql_to_mysqli($stmt, $link);
		$ALaffected_rows = mysqli_affected_rows($link);
		$api_id = mysqli_insert_id($link);

		if ($ALaffected_rows > 0)
			{
			$stmt="INSERT INTO vicidial_api_urls set api_id='$api_id',api_date=NOW(),remote_ip='$ip',url='$apiURL';";
			$rslt=mysql_to_mysqli($stmt, $link);
			}
		}
	return 1;
	}

?>
