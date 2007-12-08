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

Sample page.
"""

import cgi
import os

# Ensure that the fckeditor.py is included in your classpath
import fckeditor

# Tell the browser to render html
print "Content-Type: text/html"
print ""

# Document header
print """<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
	<head>
		<title>FCKeditor - Sample</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="robots" content="noindex, nofollow">
		<link href="../sample.css" rel="stylesheet" type="text/css" />
	</head>
	<body>
		<h1>FCKeditor - Python - Sample 1</h1>
		This sample displays a normal HTML form with an FCKeditor with full features
		enabled.
		<hr>
		<form action="sampleposteddata.py" method="post" target="_blank">
"""

# This is the real work
try:
	sBasePath = os.environ.get("SCRIPT_NAME")
	sBasePath = sBasePath[0:sBasePath.find("_samples")]

	oFCKeditor = fckeditor.FCKeditor('FCKeditor1')
	oFCKeditor.BasePath = sBasePath
	oFCKeditor.Value = """<p>This is some <strong>sample text</strong>. You are using <a href="http://www.fckeditor.net/">FCKeditor</a>.</p>"""
	print oFCKeditor.Create()
except Exception, e:
	print e
print """
			<br>
			<input type="submit" value="Submit">
		</form>
"""

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


