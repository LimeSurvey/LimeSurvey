<?php
/*
* LimeSurvey
* Copyright (C) 2007 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
* 
* $Id$
*/

// Security Checked: POST, GET, SESSION, REQUEST, returnglobal, DB  

/*************** LDAP Functions *************/
/*					    */
/*********************************************/

function ldap_getCnx($server_id = null) {
	global $ldap_server;
	
	if ( is_null($server_id) ) {
		return False;
	}

	else {
		if ($ldap_server[$server_id]['protoversion'] == 'ldapv3' && $ldap_server[$server_id]['encrypt'] != 'ldaps') {
			$ds = ldap_connect($ldap_server[$server_id]['server'], $ldap_server[$server_id]['port']);
			ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);

			if (! $ldap_server[$server_id]['referrals']) {
				ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);
			}

			if ($ldap_server[$server_id]['encrypt'] == 'start-tls' ) {
				ldap_start_tls ($ds);
			}
		}
		elseif ($ldap_server[$server_id]['protoversion'] == 'ldapv2') {
			if ($ldap_server[$server_id]['encrypt'] == 'ldaps') {
				$ds = ldap_connect("ldaps://".$ldap_server[$server_id]['server'], $ldap_server[$server_id]['port']);
			}
			else {
				$ds = ldap_connect($ldap_server[$server_id]['server'], $ldap_server[$server_id]['port']);
			}

			if (! $ldap_server[$server_id]['referrals']) {
				ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);
			}
		}
		return $ds;		
	}
}


function ldap_bindCnx($ds, $server_id = null) {
	global $ldap_server;
	$resbind=0;

	if ( !$ds || is_null($server_id) ) {
		return 0;	
	}

	if ( isset($ldap_server[$server_id]['binddn']) && isset($ldap_server[$server_id]['bindpw']) ) {
		$resbind=@ldap_bind($ds,
				    $ldap_server[$server_id]['binddn'],
				    $ldap_server[$server_id]['bindpw']);
	}
	else {
		$resbind=@ldap_bind($ds);
	}
	return $resbind;
}


function ldap_readattr($attr) {

	if (is_array($attr)) { 
		return trim($attr[0]);
	}
	else {
		return trim($attr);
	}
}


function ldap_search_withScope($ds, $basedn, $filter, $attrlist, $scope) {
	if ( $scope == "base" ) {
		$search = ldap_read($ds, $basedn, $filter, $attrlist);
	}
	elseif ( $scope == "one" ) {
		$search = ldap_list($ds, $basedn, $filter, $attrlist);
	}
	elseif ( $scope == "sub" ) {
		$search = ldap_search($ds, $basedn, $filter, $attrlist);
	}
	return $search;
}


function ldap_doTokenSearch($ds, $ldapq, &$ResArray) {
	global $ldap_queries;
	$totalrescount=0;
	$userattrs=array();

	// Retrieve the ldap user attribute-list to read
	$userparams = array('firstname_attr','lastname_attr',
			'email_attr','token_attr', 'language',
			'attr1', 'attr2');
	foreach ($userparams as $id => $attr) {
		if (array_key_exists($attr,$ldap_queries[$ldapq]) &&
		  $ldap_queries[$ldapq][$attr] != '') {
			$userattrs[]=$ldap_queries[$ldapq][$attr];
		}
	}

	// If ldap group filtering is required
	if (isset($ldap_queries[$ldapq]['groupfilter']) &&
	    $ldap_queries[$ldapq]['groupfilter'] != '') {

		$userCandidates=array(); // list of candidates

		$groupscope='sub'; // subtree search unless specified
		if (isset($ldap_queries[$ldapq]['groupscope']) &&
		    $ldap_queries[$ldapq]['groupscope'] != '') {
			$groupscope=$ldap_queries[$ldapq]['groupscope'];
		}

		$groupmemberattr='member'; //use 'member' attribute unless specified
		if (isset($ldap_queries[$ldapq]['groupmemberattr']) &&
		    $ldap_queries[$ldapq]['groupmemberattr'] != '') {
			$groupmemberattr=$ldap_queries[$ldapq]['groupmemberattr'];
		}
		
		// Search for group candidates
		$search_groups=ldap_search_withScope($ds,
					$ldap_queries[$ldapq]['groupbase'],
					$ldap_queries[$ldapq]['groupfilter'],
					array($groupmemberattr),
					$groupscope);
		$rescount=@ldap_count_entries($ds,$search_groups);

		if ($rescount >= 1) { // at least 1 group was selected
			$group_info=ldap_get_entries($ds, $search_groups);
			// For each group candidate add members's id to $userCandidates[]
			for ($i=0;$i<$group_info["count"];$i++) {
				for ($j=0;$j<$group_info[$i][$groupmemberattr]["count"];$j++) {
					// Only add the user's id if not already listed
					// (avoids duplicates if this user is in several groups)
					if (! in_array($group_info[$i][$groupmemberattr][$j], 
					               $userCandidates)) {
						$userCandidates[]=$group_info[$i][$groupmemberattr][$j];
					} 
				}
			}

			// For each user, apply userfilter if defined
			// and get user attrs 
			foreach ($userCandidates as $key => $user) {

				$user_is_dn=TRUE; // Suppose group members are DNs by default
				if (isset($ldap_queries[$ldapq]['groupmemberisdn']) &&
		    		    $ldap_queries[$ldapq]['groupmemberisdn'] == False) {
					$user_is_dn=False;
				}

				if ($user_is_dn) {
					// If group members are DNs

					// Set userfilter (no filter by default)
					$userfilter='(objectclass=*)';
					if (isset($ldap_queries[$ldapq]['userfilter']) &&
		    		    	  $ldap_queries[$ldapq]['userfilter'] != '') {
						$userfilter=$ldap_queries[$ldapq]['userfilter'];
					}

					$userscope='sub'; // subtree search unless specified
					if (isset($ldap_queries[$ldapq]['userscope']) &&
		    			    $ldap_queries[$ldapq]['userscope'] != '') {
						$userscope=$ldap_queries[$ldapq]['userscope'];
					}

					// If a userbase is defined, then get user's RND
					// and do a user search based on this RDN
					// Note: User's RDN is supposed to be made
					//	 of only ONE attribute by this function
					if (isset($ldap_queries[$ldapq]['userbase']) &&
					    $ldap_queries[$ldapq]['userbase'] != '') {
						// get user's rdn
						$user_dn_tab=explode(",", $user);
						$user_rdn=$user_dn_tab[0];
						$userfilter_rdn="(&("
								.$user_rdn.")".$userfilter.")";

						$search_users=ldap_search_withScope($ds,
							$ldap_queries[$ldapq]['userbase'],
							$userfilter_rdn,
							$userattrs,
							$userscope);

						$rescount=@ldap_count_entries($ds,$search_users);
						if ($rescount >= 1) { 
							// DN match criteria
							// add to result array
							$user_info=@ldap_get_entries($ds, $search_users);

							for ($i=0;$i<$rescount;$i++) {
								if ($user_info[$i]['dn'] == $user) {
									$ResArray[]=$user_info;
									$totalrescount++;
								}
							}
						}
					} // End of Member is DN and a userbase is defined
					else { 
						// There is no userbase defined
						// Only apply userfilter to the user's DN
						$search_users=ldap_search_withScope($ds,
							$user,
							$userfilter,
							$userattrs,
							'base');
						$rescount=@ldap_count_entries($ds,$search_users);

						if ($rescount >= 1) { 
							// DN match criteria, add result to the result Array
							$userentry=ldap_get_entries($ds, $search_users);
							$ResArray[]=$userentry;
							$totalrescount++;
						}
					} // End of Member is DN and a userbase is NOT defined
				} // End of the member are DNs case

				else { 
					//$user is the user ID, not a DN
					// Search given userid combined with userfilter

					// Set userfilter ('open filter' by default)
					$userfilter='(objectclass=*)';
					if (isset($ldap_queries[$ldapq]['userfilter']) &&
		    		    	  $ldap_queries[$ldapq]['userfilter'] != '') {
						$userfilter=$ldap_queries[$ldapq]['userfilter'];
					}

					// Build the user filter from the RDN
					$userfilter_notdn="(&("
						.$ldap_queries[$ldapq]['useridattr']."=".$user.")"
						.$userfilter.")";
						
					$search_users=ldap_search_withScope($ds,
							$ldap_queries[$ldapq]['userbase'],
							$userfilter_notdn,
							$userattrs,
							$ldap_queries[$ldapq]['userscope']);

					$rescount=@ldap_count_entries($ds,$search_users);
					if ($rescount >= 1) { 
						// user matches criteria, add result to the result Array
						$user_info=ldap_get_entries($ds, $search_users);
						$ResArray[]=$user_info;
						$totalrescount+=$rescount;
					}
				} // End of the members are not DN case
			} // End of foreach user member in the group
		} // End of foreach group
	} // End of GroupSearches	

	else { 
		// No groupfilter is defined 
		// Apply a simple userfilter then

		$userscope='sub'; // default to subtree search
		if (isset($ldap_queries[$ldapq]['userscope']) &&
 		    $ldap_queries[$ldapq]['userscope'] != '') {
			$userscope=$ldap_queries[$ldapq]['userscope'];
		}
		
		 $search_result = ldap_search_withScope($ds,
					$ldap_queries[$ldapq]['userbase'],
					$ldap_queries[$ldapq]['userfilter'],
					$userattrs,
					$userscope);

		$rescount=ldap_count_entries($ds,$search_result);
		if ( $rescount >= 1) {
			$user_info = ldap_get_entries($ds, $search_result);
			$ResArray[]=$user_info;
			$totalrescount+=$rescount;
		}
	} // End of no group filtering

	return $totalrescount;
}

?>
