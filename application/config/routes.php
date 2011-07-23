<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There area two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router what URI segments to use if those provided
| in the URL cannot be matched to a valid route.
|
*/

$route['default_controller'] = "admin2";
$route['404_override'] = '';

//Admin Routes
$route['admin'] = "admin/index";

//survey
$route['admin/survey/newsurvey'] = "admin/survey/index/newsurvey";
$route['admin/survey/editsurveysettings/(:num)'] = "admin/survey/index/editsurveysettings/$1";

//question
$route['admin/question/newquestion/(:num)/(:num)'] = "admin/question/index/addquestion/$1/$2";
$route['admin/question/editquestion/(:num)/(:num)/(:num)'] = "admin/question/index/editquestion/$1/$2/$3";

//labels
$route['admin/labels/newlabel'] = "admin/labels/index/newlabelset";
$route['admin/labels/editlabel/(:num)'] = "admin/labels/index/editlabelset/$1";

//templates
//$route['admin/templates/screenredirect/(:any)/(:any)/(:any)'] = "admin/templates/index/$1/$3/$2";
//$route['admin/templates/fileredirect/(:any)/(:any)/(:any)'] = "admin/templates/index/$1/$2/$3";
//$route['admin/templates/(:any)/(:any)/(:any)'] = "admin/templates/index/$3/$2/$1";


/* End of file routes.php */
/* Location: ./application/config/routes.php */