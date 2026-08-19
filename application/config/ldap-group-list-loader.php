<?php
/*
 * LimeSurvey
 * Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 */

/*********************************************/
/*           LDAP Group list loader          */
/*********************************************/

// Additional variables needed

// domain_ldap_user_base_serach - define the ldap base used for user searches: "dc=mycompany,dc=org".

// server_group_search - the path to the "folder" from which the list of groups with their
// description will be selected: "ou=Folder_with_groups_survey_participants,dc=mycompany,dc=org"

// server_group_search_filter - the object search category, in this case groups: "(objectcategory=group)".
// There is usually no need to change the value of this variable.

// sgsfn - a variable containing the name of the LDAP attribute. If you want the information from the "Info"
// field to be displayed, then there is no need to change this line in this file. The "Info" attribute is
// used to display information about the group, the value is filled in in AD.

// server_group_search_fieldname - the group description field, which will later be displayed
// in the list on the LDAP member import page.

// Important! Only these two values need to be configured for the loader to work:
// $domain_ldap_user_base_serach and $ldap_server[$serverId]['server_group_search'].

$domain_ldap_user_base_serach = "dc=mycompany,dc=org";
$ldap_server[$serverId]['server_group_search'] = "ou=folder_with_groups_survey_participants,dc=mycompany,dc=org";

$ldap_server[$serverId]['server_group_search_filter'] = "(objectcategory=group)";
$sgsfn = "info";
$ldap_server[$serverId]['server_group_search_fieldname'] = array($sgsfn);
$ldap_group_conn = ldap_connect($ldap_server[$serverId]['server']);
ldap_set_option ($ldap_group_conn, LDAP_OPT_REFERRALS, 0);
ldap_set_option ($ldap_group_conn, LDAP_OPT_PROTOCOL_VERSION, 2);
if ($ldap_server[$serverId]['protoversion'] == "ldapv3") ldap_set_option ($ldap_group_conn, LDAP_OPT_PROTOCOL_VERSION, 3);

if($ldap_group_conn) {
    $ldap_group_bind = ldap_bind($ldap_group_conn,$ldap_server[$serverId]['binddn'],$ldap_server[$serverId]['bindpw']);
    if ($ldap_group_bind) {
        $result = ldap_list($ldap_group_conn,$ldap_server[$serverId]['server_group_search'],$ldap_server[$serverId]['server_group_search_filter'],$ldap_server[$serverId]['server_group_search_fieldname']);
        $data = ldap_get_entries($ldap_group_conn, $result);
        for ($i=0; $i<$data["count"]; $i++) {
	    $query_id++;
	    $ldap_queries[$query_id]['ldapServerId'] = 0;
	    $ldap_queries[$query_id]['name'] = $data[$i][$sgsfn][0];
	    $ldap_queries[$query_id]['userbase'] = $domain_ldap_user_base_serach;
	    $ldap_queries[$query_id]['userfilter'] = '(&(objectCategory=person)(objectClass=user)(!(userAccountControl:1.2.840.113556.1.4.803:=2))(memberOf:1.2.840.113556.1.4.1941:='.$data[$i]["dn"].'))';
	    $ldap_queries[$query_id]['userscope'] = 'sub';
	    $ldap_queries[$query_id]['firstname_attr'] = 'givenname';
	    $ldap_queries[$query_id]['lastname_attr'] = 'sn';
	    $ldap_queries[$query_id]['email_attr'] = 'mail';
	    $ldap_queries[$query_id]['token_attr'] = ''; // Leave empty for Auto Token generation bu phpsv
	    $ldap_queries[$query_id]['language'] = '';
	    $ldap_queries[$query_id]['attr1'] = '';
	    $ldap_queries[$query_id]['attr2'] = '';
		}
    }
}

ldap_close($ldap_group_conn);


//If you need to search in different LDAP directories, you need to copy the code again and make the same settings.

/*
$domain_ldap_user_base_serach = "dc=mycompany,dc=org";
$ldap_server[$serverId]['server_group_search'] = "ou=another_folder_with_groups_survey_participants,dc=mycompany,dc=org";

$ldap_server[$serverId]['server_group_search_filter'] = "(objectcategory=group)";
$sgsfn = "info";
$ldap_server[$serverId]['server_group_search_fieldname'] = array($sgsfn);
$ldap_group_conn = ldap_connect($ldap_server[$serverId]['server']);
ldap_set_option ($ldap_group_conn, LDAP_OPT_REFERRALS, 0);
ldap_set_option ($ldap_group_conn, LDAP_OPT_PROTOCOL_VERSION, 2);
if ($ldap_server[$serverId]['protoversion'] == "ldapv3") ldap_set_option ($ldap_group_conn, LDAP_OPT_PROTOCOL_VERSION, 3);

if($ldap_group_conn) {
    $ldap_group_bind = ldap_bind($ldap_group_conn,$ldap_server[$serverId]['binddn'],$ldap_server[$serverId]['bindpw']);
    if ($ldap_group_bind) {
        $result = ldap_list($ldap_group_conn,$ldap_server[$serverId]['server_group_search'],$ldap_server[$serverId]['server_group_search_filter'],$ldap_server[$serverId]['server_group_search_fieldname']);
        $data = ldap_get_entries($ldap_group_conn, $result);
        for ($i=0; $i<$data["count"]; $i++) {
	    $query_id++;
	    $ldap_queries[$query_id]['ldapServerId'] = 0;
	    $ldap_queries[$query_id]['name'] = $data[$i][$sgsfn][0];
	    $ldap_queries[$query_id]['userbase'] = $domain_ldap_user_base_serach;
	    $ldap_queries[$query_id]['userfilter'] = '(&(objectCategory=person)(objectClass=user)(!(userAccountControl:1.2.840.113556.1.4.803:=2))(memberOf:1.2.840.113556.1.4.1941:='.$data[$i]["dn"].'))';
	    $ldap_queries[$query_id]['userscope'] = 'sub';
	    $ldap_queries[$query_id]['firstname_attr'] = 'givenname';
	    $ldap_queries[$query_id]['lastname_attr'] = 'sn';
	    $ldap_queries[$query_id]['email_attr'] = 'mail';
	    $ldap_queries[$query_id]['token_attr'] = ''; // Leave empty for Auto Token generation bu phpsv
	    $ldap_queries[$query_id]['language'] = '';
	    $ldap_queries[$query_id]['attr1'] = '';
	    $ldap_queries[$query_id]['attr2'] = '';
		}
    }
}

ldap_close($ldap_group_conn);
*/