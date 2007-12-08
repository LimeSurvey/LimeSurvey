<cfsetting enablecfoutputonly="true">
<!---
 * FCKeditor - The text editor for Internet - http://www.fckeditor.net
 * Copyright (C) 2003-2007 Frederico Caldeira Knabben
 *
 * == BEGIN LICENSE ==
 *
 * Licensed under the terms of any of the following licenses at your
 * choice:
 *
 *  - GNU General Public License Version 2 or later (the "GPL")
 *    http://www.gnu.org/licenses/gpl.html
 *
 *  - GNU Lesser General Public License Version 2.1 or later (the "LGPL")
 *    http://www.gnu.org/licenses/lgpl.html
 *
 *  - Mozilla Public License Version 1.1 or later (the "MPL")
 *    http://www.mozilla.org/MPL/MPL-1.1.html
 *
 * == END LICENSE ==
 *
 * Sample page for ColdFusion MX 6.0 and above.
--->

<cfoutput>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>FCKeditor - Sample</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="robots" content="noindex, nofollow">
	<link href="../sample.css" rel="stylesheet" type="text/css" />
		<script type="text/javascript">

function FCKeditor_OnComplete( editorInstance )
{
	var oCombo = document.getElementById( 'cmbLanguages' ) ;
	for ( code in editorInstance.Language.AvailableLanguages )
	{
		AddComboOption( oCombo, editorInstance.Language.AvailableLanguages[code] + ' (' + code + ')', code ) ;
	}
	oCombo.value = editorInstance.Language.ActiveLanguage.Code ;
}

function AddComboOption(combo, optionText, optionValue)
{
	var oOption = document.createElement("OPTION") ;

	combo.options.add(oOption) ;

	oOption.innerHTML = optionText ;
	oOption.value     = optionValue ;

	return oOption ;
}

function ChangeLanguage( languageCode )
{
	window.location.href = window.location.pathname + "?Lang=" + languageCode ;
}
		</script>
</head>
<body>
<h1>FCKeditor - ColdFusion Component (CFC) - Sample 2</h1>
This sample shows the editor in all its available languages.
<hr>
</cfoutput>
<cfif listFirst( server.coldFusion.productVersion ) LT 6>
	<cfoutput><br><em style="color: red;">This sample works only with a ColdFusion MX server and higher, because it uses some advantages of this version.</em></cfoutput>
	<cfabort>
</cfif>
<cfoutput>
<table cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td>
			Select a language:&nbsp;
		</td>
		<td>
			<select id="cmbLanguages" onchange="ChangeLanguage(this.value);">
			</select>
		</td>
	</tr>
</table>
<br>
<form action="sampleposteddata.cfm" method="post" target="_blank">
</cfoutput>
	<cfscript>
		// Calculate basepath for FCKeditor. It's in the folder right above _samples
		basePath = Left( cgi.script_name, FindNoCase( '_samples', cgi.script_name ) - 1 ) ;

		fckEditor = createObject( "component", "#basePath#fckeditor" ) ;
		fckEditor.instanceName	= "myEditor" ;
		fckEditor.value			= '<p>This is some <strong>sample text</strong>. You are using <a href="http://www.fckeditor.net/">FCKeditor</a>.</p>' ;
		fckEditor.basePath		= basePath ;
		if ( isDefined( "URL.Lang" ) )
		{
			fckEditor.config["AutoDetectLanguage"]		= false ;
			fckEditor.config["DefaultLanguage"]			= HTMLEditFormat( URL.Lang ) ;
		}
		else
		{
			fckEeditor.config["AutoDetectLanguage"]		= true ;
			fckEeditor.config["DefaultLanguage"]		= 'en' ;
		}
		fckEditor.create() ; // create the editor.
	</cfscript>
<cfoutput>
	<br>
	<input type="submit" value="Submit">
	</form>
</body>
</html>
</cfoutput>
<cfsetting enablecfoutputonly="false">