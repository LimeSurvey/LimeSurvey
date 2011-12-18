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

//Compatibility with classic modrewrite
$route['(:num)/lang-(:any)/tk-(:any)'] = "survey/sid/$1/lang/$2/token/$3"; //This one must be first
$route['(:num)/lang-(:any)'] = "survey/sid/$1/lang/$2";
$route['(:num)/tk-(:any)'] = "survey/sid/$1/token/$2";
$route['(:num)'] = "survey/sid/$1";

//Admin Routes
$route['admin/index'] = "admin";
$route['admin/labels/view'] = "admin/labels/view";

//question
$route['admin/question/newquestion/(:num)/(:num)'] = "admin/question/index/addquestion/$1/$2";
$route['admin/question/editquestion/(:num)/(:num)/(:num)'] = "admin/question/index/editquestion/$1/$2/$3";
$route['admin/question/deletequestion/(:num)/(:num)/(:num)'] = "admin/question/delete/delquestion/$1/$2/$3";

$route['admin/labels/exportmulti'] = "admin/labels/exportmulti";
$route['admin/labels/process'] = "admin/labels/process";
$route['admin/labels/view/<lid:\d+>'] = "admin/labels/view/<lid>";
$route['admin/labels/<action:\w+>'] = "admin/labels/index/<action>";
$route['admin/labels/<action:\w+>/<lid:\d+>'] = "admin/labels/index/<action>/<id>";
//labels
//$route['admin/labels/newlabel'] = "admin/labels/index/newlabelset";
//$route['admin/labels/editlabel/(:num)'] = "admin/labels/index/editlabelset/$1";

$route['<controller:\w+>/<action:\w+>'] = '<controller>/<action>';

//Expression Manager tests
$route['admin/expressions'] = "admin/expressions/index";
$route['admin/expressions/test'] = "admin/expressions/index";

//optout
$route['optout/(:num)/(:any)/(:any)'] = "optout/index/$1/$2/$3";

return $route;
//templates
//$route['admin/templates/screenredirect/(:any)/(:any)/(:any)'] = "admin/templates/index/$1/$3/$2";
//$route['admin/templates/fileredirect/(:any)/(:any)/(:any)'] = "admin/templates/index/$1/$2/$3";
//$route['admin/templates/(:any)/(:any)/(:any)'] = "admin/templates/index/$3/$2/$1";


/* End of file routes.php */
/* Location: ./application/config/routes.php */