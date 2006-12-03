<?

sendcacheheaders();

echo $htmlheader;

echo "<script type='text/javascript'>\n"
."\tfunction showhelp(action)\n"
."\t\t{\n"
."\t\tvar name='help';\n"
."\t\tif (action == \"hide\")\n"
."\t\t\t{\n"
."\t\t\tdocument.getElementById(name).style.display='none';\n"
."\t\t\t}\n"
."\t\telse if (action == \"show\")\n"
."\t\t\t{\n"
."\t\t\tdocument.getElementById(name).style.display='';\n"
."\t\t\t}\n"
."\t\t}\n"
."</script>\n";

if (!isset($action)) {$action=returnglobal('action');}

// check data for login
if(isset($_POST['user']) && isset($_POST['password']) || ($action == "forgotpass"))	// added by Dennis
{
	include("usercontrol.php");
}

// login form
if(!isset($_SESSION['loginID']) && $action != "forgotpass") // && $action != "login")	// added by Dennis
{
	if($action == "forgotpassword")
	{
		$loginsummary = "<form name='forgot' id='forgot' method='post' action='$rooturl/admin/admin.php' ><br /><strong>"._("You have to enter user name and email.")."</strong><br />	<br />
							<table>
								<tr>
									<td><p>"._("Username")."</p></td>
									<td><input name='user' type='text' id='user' size='40' maxlength='40' value='' /></td>
								</tr>
								<tr>
									<td><p>"._("Email")."</p></td>
									<td><input name='email' id='email' type='text' size='40' maxlength='40' value='' /></td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td><input type='hidden' name='action' value='forgotpass' />
									<input class='action' type='submit' value='Check data' /><br />&nbsp;\n</td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td><a href='$scriptname'>"._("Main Admin Screen")."</a></td>
								</tr>
							</table>						
						</form>";	
	}
	else
	{
		$loginsummary = "<form name='login' id='login' method='post' action='$rooturl/admin/admin.php' ><br /><strong>"._("You have to login first.")."</strong><br />	<br />
							<table>
								<tr>
									<td>"._("Username")."</td>
									<td><input name='user' type='text' id='user' size='40' maxlength='40' value='' /></td>
								</tr>
								<tr>
									<td>"._("Password")."</td>
									<td><input name='password' id='password' type='password' size='40' maxlength='40' /></td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td align='center'><input type='hidden' name='action' value='login' />
									<input class='action' type='submit' value='Login' /><br />&nbsp;\n</td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td><a href='$scriptname?action=forgotpassword'>"._("Forgot Your Password?")."</a><br />&nbsp;\n</td>
								</tr>
							</table>
						</form>";

		// Language selection
		$loginsummary .=  "\t<form name='language' id='language' method='post' action='$rooturl/admin/admin.php' >"
		. "\t<table><tr>\n"
		. "\t\t<td align='center' >\n"
		. "\t\t\t"._("Current Language").":\n"
		. "\t\t</td><td>\n"
		. "\t\t\t<select name='lang' onChange='form.submit()'>\n";
		foreach (getlanguagedata() as $langkey=>$languagekind)
		{
			$loginsummary .= "\t\t\t\t<option value='$langkey'";
			if ($langkey == $_SESSION['adminlang']) {$loginsummary .= " selected";}
			$loginsummary .= ">".$languagekind['description']." - ".$languagekind['nativedescription']."</option>\n";
		}
		$loginsummary .= "\t\t\t</select>\n"
		. "\t\t\t<input type='hidden' name='action' value='changelang' />\n"
		. "\t\t</td>\n"
		. "\t</tr>\n"
		. "</table>"
		. "</form><br />";
	}
}

if (isset($loginsummary)) {

	echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n"
	."\t<tr>\n"
	."\t\t<td valign='top' align='center' bgcolor='#BBBBBB'>\n";
	
	if(isset($_SESSION['loginID']))
	{
		echo showadminmenu();
	}
	echo $loginsummary;
	
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	echo "</table>\n";
	echo getAdminFooter("$langdir/instructions.html", "Using PHPSurveyors Admin Script");
	
	exit;
}

if(!isset($_SESSION['loginID'])){die ("Cannot run this script directly");}

// logout user
if ($action == "logoutuser") // && isset($_SESSION['loginID']))
{
	$action = "logout";
	include("usercontrol.php");
	
	echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>\n"
	."\t<tr>\n"
	."\t\t<td valign='top' align='center' bgcolor='#BBBBBB'>\n";
	
	echo $logoutsummary;
	
	echo "\t\t</td>\n";
	echo "\t</tr>\n";
	echo "</table>\n";
	echo getAdminFooter("$langdir/instructions.html", "Using PHPSurveyors Admin Script");
	
	exit;
	
}
?>