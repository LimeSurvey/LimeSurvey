#!/usr/bin/env python

"""
FCKeditor - The text editor for Internet - http://www.fckeditor.net
Copyright (C) 2003-2007 Frederico Caldeira Knabben

== BEGIN LICENSE ==

Licensed under the terms of any of the following licenses at your
choice:

 - GNU General Public License Version 2 or later (the "GPL")
   http://www.gnu.org/licenses/gpl.html

 - GNU Lesser General Public License Version 2.1 or later (the "LGPL")
   http://www.gnu.org/licenses/lgpl.html

 - Mozilla Public License Version 1.1 or later (the "MPL")
   http://www.mozilla.org/MPL/MPL-1.1.html

== END LICENSE ==

This page lists the data posted by a form.
"""

import cgi
import os

# Tell the browser to render html
print "Content-Type: text/html"
print ""

try:
	# Create a cgi object
	form = cgi.FieldStorage()
except Exception, e:
	print e

# Document header
print """<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>FCKeditor - Samples - Posted Data</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="robots" content="noindex, nofollow">
		<link href="../sample.css" rel="stylesheet" type="text/css" />
	</head>
	<body>
"""

# This is the real work
print """
		<h1>FCKeditor - Samples - Posted Data</h1>
		This page lists all data posted by the form.
		<hr>
		<table width="100%" border="1" cellspacing="0" bordercolor="#999999">
			<tr style="FONT-WEIGHT: bold; COLOR: #dddddd; BACKGROUND-COLOR: #999999">
				<td nowrap>Field Name&nbsp;&nbsp;</td>
				<td>Value</td>
			</tr>
"""
for key in form.keys():
	try:
		value = form[key].value
		print """
				<tr>
					<td valign="top" nowrap><b>%s</b></td>
					<td width="100%%" style="white-space:pre">%s</td>
				</tr>
			""" % (key, value)
	except Exception, e:
		print e
print "</table>"

# For testing your environments
print "<hr>"
for key in os.environ.keys():
	print "%s: %s<br>" % (key, os.environ.get(key, ""))
print "<hr>"

# Document footer
print """
	</body>
</html>
"""
