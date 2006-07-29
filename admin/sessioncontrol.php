<?php
/*
	#############################################################
	# >>> PHPSurveyor  								    		#
	#############################################################
	# > Author:  Jason Cleeland									#
	# > E-mail:  jason@cleeland.org								#
	# > Mail:    Box 99, Trades Hall, 54 Victoria St,			#
	# >          CARLTON SOUTH 3053, AUSTRALIA					#
 	# > Date: 	 20 February 2003								#
	#															#
	# This set of scripts allows you to develop, publish and	#
	# perform data-entry on surveys.							#
	#############################################################
	#															#
	#	Copyright (C) 2003  Jason Cleeland						#
	#															#
	# This program is free software; you can redistribute 		#
	# it and/or modify it under the terms of the GNU General 	#
	# Public License as published by the Free Software 			#
	# Foundation; either version 2 of the License, or (at your 	#
	# option) any later version.								#
	#															#
	# This program is distributed in the hope that it will be 	#
	# useful, but WITHOUT ANY WARRANTY; without even the 		#
	# implied warranty of MERCHANTABILITY or FITNESS FOR A 		#
	# PARTICULAR PURPOSE.  See the GNU General Public License 	#
	# for more details.											#
	#															#
	# You should have received a copy of the GNU General 		#
	# Public License along with this program; if not, write to 	#
	# the Free Software Foundation, Inc., 59 Temple Place - 	#
	# Suite 330, Boston, MA  02111-1307, USA.					#
	#############################################################	
*/
//SESSIONCONTROL.PHP FILE MANAGES ADMIN SESSIONS. IT WILL EVENTUALL EXTEND TO MANAGING USER LEVELS
//Ensure script is not run directly, avoid path disclosure
//if (empty($dbprefix)) {die ("Cannot run this script directly");}

session_name("PHPSurveyorAdmin");
if (session_id() == "") session_start();



//LANGUAGE ISSUES
if (returnglobal('action') == "changelang") 
	{
    $_SESSION['adminlang']=returnglobal('lang');
    
	}
elseif (!isset($_SESSION['adminlang']) || $_SESSION['adminlang']=='' )
	{
        $_SESSION['adminlang']=$defaultlang;
    }
// echo 'Domain:*'.$_SESSION['adminlang'].'*';
// for debug purposes

SetInterfaceLanguage($_SESSION['adminlang']);

?>
