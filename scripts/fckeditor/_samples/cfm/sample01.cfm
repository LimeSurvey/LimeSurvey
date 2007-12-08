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
 * Sample page for ColdFusion.
--->

<cfoutput>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>FCKeditor - Sample</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="robots" content="noindex, nofollow">
	<link href="../sample.css" rel="stylesheet" type="text/css" />
</head>
<body>
<h1>FCKeditor - ColdFusion - Sample 1</h1>

This sample displays a normal HTML form with a FCKeditor with full features enabled.
<hr>

<form method="POST" action="sampleposteddata.cfm">
</cfoutput>

<!--- Calculate basepath for FCKeditor. It's in the folder right above _samples --->
<cfset basePath = Left( cgi.script_name, FindNoCase( '_samples', cgi.script_name ) - 1 )>

<cfmodule
	template="../../fckeditor.cfm"
	basePath="#basePath#"
	instanceName="myEditor"
	value='<p>This is some <strong>sample text</strong>. You are using <a href="http://www.fckeditor.net/">FCKeditor</a>.</p>'
	width="100%"
	height="200"
>

<cfoutput>
<br />
<input type="submit" value="Submit">
<hr />
</form>
</body>
</html>
</cfoutput>
<cfsetting enablecfoutputonly="false">