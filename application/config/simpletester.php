<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* SimpleTester 1.1 configuration file.
* CodeIgniter-library for the SimpleTest unit test library, http://simpletest.org/
*/

/*
|--------------------------------------------------------------------------
| Test directory
|--------------------------------------------------------------------------
|
| All php files in this directory will be added to the unit test suite.
|
*/
$config['testDir'] = APPPATH . 'tests';

/*
|--------------------------------------------------------------------------
| Allowed autorun IPs
|--------------------------------------------------------------------------
|
| The tests will be automatically run from all IPs listed here, space
| separated. Useful to avoid testing on a production site, since tests can
| be too intensive to run on each request.
|
| Tests can still be run manually using the Run() method.
|
*/
$config['runFromIPs'] = '127.0.0.1';

/*
|--------------------------------------------------------------------------
| Reporter output class
|--------------------------------------------------------------------------
|
| The tests will display using this class. Pre-packaged classes:
| - HtmlReporter: A full html display of passed or failed tests.
| - ShowPasses: Detailed view of all passed tests.
| - SmallReporter: Displays a small status report in upper right corner.
| - MinimalReporter: Displays a small status report only when tests fail.
|
| Note that any output is sent before the view, so there will be Doctype
| problems if your view uses these. The Minimalreporter outputs nothing
| when everything is OK, so it's usually the best one to use when you have
| confirmed that the library is working correctly.
|
*/
$config['reporter'] = 'SmallReporter';

/*
|--------------------------------------------------------------------------
| Prevent autorun of tests
|--------------------------------------------------------------------------
|
| If noautorun is set to TRUE, tests are not automatically run.
| Tests can be run manually using the Run() method. Example:
|
| echo $this->simpletester->Run();
|
*/
$config['noautorun'] = FALSE;

/*
|--------------------------------------------------------------------------
| Test files extension
|--------------------------------------------------------------------------
|
| If your php files have a different extension, php5 for example,
| specify it here (without dot). All files ending with this extension will
| be added to the test suite.
|
*/
$config['fileExtension'] = 'php';

/*
|--------------------------------------------------------------------------
| Test title
|--------------------------------------------------------------------------
|
| Here you can specify the title of the test suite. Will be displayed for
| example in the MinimalReporter when a test fails.
|
*/
$config['testTitle'] = 'CodeIgnited Unit Tests';
