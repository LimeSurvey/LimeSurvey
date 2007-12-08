<!---
/*
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
 * This page lists the data posted by a form.
 */
--->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>FCKeditor - Samples - Posted Data</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="robots" content="noindex, nofollow">
		<link href="../sample.css" rel="stylesheet" type="text/css" />
	</head>
	<body>
		<h1>FCKeditor - Samples - Posted Data</h1>
		This page lists all data posted by the form.
		<hr>
<cfif listFirst( server.coldFusion.productVersion ) LT 6>
	<cfif isDefined( 'FORM.fieldnames' )>
		<cfoutput>
		<hr />
		<style>
		<!--
			td, th { font: 11px Verdana, Arial, Helv, Helvetica, sans-serif; }
		-->
		</style>
		<table border="1" cellspacing="0" cellpadding="2" bordercolor="darkblue" bordercolordark="darkblue" bordercolorlight="darkblue">
		<tr>
			<th colspan="2" bgcolor="darkblue"><font color="white"><strong>Dump of FORM Variables</strong></font></th>
		</tr>
		<tr>
			<td bgcolor="lightskyblue">FieldNames</td>
			<td>#FORM.fieldNames#</td>
		</tr>
		<cfloop list="#FORM.fieldnames#" index="key">
		<tr>
			<td valign="top" bgcolor="lightskyblue">#key#</td>
			<td style="white-space:pre">#HTMLEditFormat( evaluate( "FORM.#key#" ) )#</td>
		</tr>
		</cfloop>
		</table>
		</cfoutput>
	</cfif>
<cfelse>
	<cfdump var="#FORM#" label="Dump of FORM Variables">
</cfif>


	</body>
</html>
