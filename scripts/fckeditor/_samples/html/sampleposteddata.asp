<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!--
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
-->
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>FCKeditor - Samples - Posted Data</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="robots" content="noindex, nofollow" />
	<link href="../sample.css" rel="stylesheet" type="text/css" />
</head>
<body>
	<h1>
		FCKeditor - Samples - Posted Data</h1>
	<div>
		This page lists all data posted by the form.
	</div>
	<hr />
	<table width="100%" border="1" cellpadding="3" style="border-color: #999999; border-collapse: collapse;">
		<tr style="font-weight: bold; color: #dddddd; background-color: #999999">
			<td style="white-space: nowrap;">
				Field Name&nbsp;&nbsp;</td>
			<td>
				Value</td>
		</tr>
		<% For Each sForm in Request.Form %>
		<tr>
			<td valign="top" style="white-space: nowrap;">
				<b>
					<%=sForm%>
				</b>
			</td>
			<td style="width: 100%;">
				<pre><%=ModifyForOutput( Request.Form(sForm) )%></pre>
			</td>
		</tr>
		<% Next %>
	</table>
</body>
</html>
<%

' This function is useful only for this sample page se whe can display the
' posted data accordingly. This processing is usually not done on real
' applications, where the posted data must be saved on a DB or file. In those
' cases, no processing must be done, and the data is saved as posted.
Function ModifyForOutput( value )

	ModifyForOutput = Server.HTMLEncode( value )

End Function

%>
