<PHP INPUT FILTER - README>


In Brief
------------------------------------------------------------------------------------------
Any website that has html forms should really use some sort of `cleaning` process
to filter out malicious code, or simply unwanted html tags for style reasons.

This class can filter input of stray or malicious PHP, Javascript or HTML tags.
It can be used to prevent cross-site scripting (XSS) attacks.
It should be used to filter input supplied by the user, such as an HTML code 
entered in form fields. You would create the filter object, configure it with your 
own settings, then call its process method to clean the form input values.


Background
------------------------------------------------------------------------------------------
Initially this class was developed to allow developers such as myself to strip certain tags from user input, 
for stylistic reasons. I'm not a big fan of BBTags such as [url]. The scope of this tool was expanded however 
to allow automated and extensive filtering of input with anti-XSS capabilities.


XSS related reading
------------------------------------------------------------------------------------------
Unfortunately, php's inbuilt strip_tags($) doesn't filter out unwanted attributes. 
This can allow XSS (Cross Site Scripting) attacks to launch malicious javascript or code.

Introduction to XSS:
http://blog.bitflux.ch/wiki/XSS_Prevention
http://www.globodigital.net/Documentation/Security_Articles/The_Cross_Site_Scripting_FAQ/
http://www.sandsprite.com/Sleuth/papers/RealWorld_XSS_1.html

XSS Cheat Sheet (Required reference reading!):
http://www.shocking.com/~rsnake/xss.html

This class's XSS blacklist page:
http://cyberai.com/inputfilter/blacklist.php


Instructions
------------------------------------------------------------------------------------------
Using the inputFilter class is simple, and described below;


0) Include Class-File
-------------------------------------
Goes somewhat without saying to move the version of the classfile 
you would like to use into your website's classfiles directory and include it.


1) User-Defined Arrays
-------------------------------------
Setup either just a tags array, or additionally an attributes array.

Eg.. $tags = array("em", "strong");


2) New Object
-------------------------------------
(If you do not enter any parameters, problem-tag stripping will still take place)

Instantiate the class with your settings.
1st (tags array):    Optional (since 1.2.0)
2nd (attr array):    Optional
3rd (tags method):   0 = remove ALL BUT these tags (default)
                     1 = remove ONLY these tags
4th (attr method):   0 = remove ALL BUT these attributes (default)
                     1 = remove ONLY these attributes
5th (xss autostrip): 1 = remove all identified problem tags (default)
                     0 = turn this feature off

Eg.. $myFilter = new InputFilter($tags, $attributes, 0, 0);


3) Process inputs
-------------------------------------
Process as many input variables as you like. 
The example.php file shows you working examples.
You can pass a string variable or an array-of-strings.

Another real-world example could be to `clean` a submitted HTML form
reading for processing. You would simply setup the filter and call...

$_POST = $myFilter->process($_POST);
$_GET["name"] = $myFilter->process($_GET["name"]);

...simple!

(If you don't implement a secure cookie method, running the filter could prove wise!)


SQL Injection (Experimental)
------------------------------------------------------------------------------------------
This feature is new, for more info, go here: http://cyberai.com/inputfilter/examples/sql-inject.php

Methods of use...
- $connection is valid MySQL-Resource variable.
- $source is some string containing SQL injection attack(s).


0) Similar to standard usage
-------------------------------------
$myFilter = new InputFilter();
$source = $myFilter->safeSQL($source, $connection);


1) Alongside standard usage
-------------------------------------
$myFilter = new InputFilter();
$source = $myFilter->safeSQL($myFilter->process($source), $connection);


2) As class method
-------------------------------------
$source = InputFilter::safeSQL($source, $connection);


3) Perform on array-of-strings
-------------------------------------
$_POST = InputFilter::safeSQL($_POST, $connection);


Advanced Features In Brief
------------------------------------------------------------------------------------------

1) XHTML ----------------------

single tags:                    - Before:  <br> 
                                - After:   <br />
				
single attributes:              - Before:  <tag attr>
                                - After:   <tag attr="attr">
			 
2) XSS ------------------------

customisation:                  - You have control over which tags and attributes to allow.

Automation:                     - Auto-strip tags whose tagnames contain non-alpha characters (<?php for instance)
                                - Auto-strip attributes containing javascript.
                                - Auto-strip blacklisted tags and attributes, as well as ALL action listener attributes.
				( Available at http://cyberai.com/inputfilter/blacklist.php )

Blacklist:                      - Choose to strip blacklisted problem tags and attributes.

Anti-sneak:                     - Embedded newlines and other whitespace or encoded characters do not fool the parser

Nested-tag protection:          - Before:  test <stron<strong>g>message</stron</strong>g>
                                - After:   test message


Alternatives
------------------------------------------------------------------------------------------
It has come to my attention that there is indeed a PEAR package to format bad html and XSS.
http://pear.php.net/pepr/pepr-proposal-show.php?id=199

I do feel they take a different approach to me, and do not offer customisible tag-stripping.
They do however offer more substantial XHTML reconstructing, and a stable alternative to this tool.
I have included the link so you can judge for yourself. :-)


Request For Comments
------------------------------------------------------------------------------------------
I'd love to know if you're using the InputFilter class, and what you think of it.
Please feel free to email me: dan@rootcube.com


Patch Contributors
------------------------------------------------------------------------------------------
Gianpaolo Racca, Ghislain Picard, Marco Wandschneider, Chris Tobin and Andrew Eddie.

Many Thanks to everyone else who has emailed!


ChangeLog 
------------------------------------------------------------------------------------------
(Line numbers reference the commented php4/php5 version of classfile)

1.2.2    - Improved nested tag detection.
	 - Patch accepted: Ghislain Picard. Changed eregi() to preg_match(), 
	   and well as allowing for numeric characters in tags on line 133.
	 - Patch accepted: Marco Wandschneider - Wrote PHP5 version that does not cause any E_STRICT warnings.
	 - Patch accepted: Chris Tobin - SQL Injection attack code (Lines 259-312 added.)
	 - Patch accepted: Andrew Eddie - Added explicit cast to array for user-specified
	   array parameters in constructor (Lines 37-38 changed.)
	 - Requested feature: Lukas Slansky - <tag attr='blah'> converted to <tag attr="blah"> 
	   allowing for incorrect input to conform to XHTML standards. (Lines 213-215 added.)
	 - Bugfix: Fixed a really dumb bug that was causing arrays to not be parsed.
	   (Changed for() loop to a foreach() on line 52.)
	 - Bugfix: Parser was mistaking attribute values "0" for a null value. (Lines 234-235 added.)

1.2.1    - Patch accepted: Gianpaolo Racca - Added trim() to line 195.

1.2.0    - Updated example-factory page - looks nicer, added "inject sample data" feature.
         - Many more XSS blocking features.
         - Rewrote some of the parser code over to be more efficient.
         - Have stopped producing seperate classfiles for php4 and php5. The current classfile will work with either!
           (If anyone would like be to continue making seperate files for each, drop me an email.)

1.1.2    - All tag and attribute names with non-alpha characters in are automatically stripped from now on
           (This applies to all programming tags, html comments and doctype tags too.)
         - User-defined arrays are converted to lowercase at object creation. A somewhat obvious problem I initially missed!
           (Had caused tag and attr matching to fail if Capitals used in arrays)

1.1.1    - Bugfix: to do with spaces in between attr name and value.
         - Bugfix: to do with single attributes.

1.1.0    - Support for array as input.
         - PHP5 version of class available.

1.0.1    - Bugfix: involved ignored tag attribute values with spaces in.

1.0.0    - Release version.
