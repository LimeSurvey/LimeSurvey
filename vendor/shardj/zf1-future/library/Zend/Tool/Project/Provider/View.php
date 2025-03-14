<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Tool
 * @subpackage Framework
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Tool_Project_Provider_Abstract
 */
require_once 'Zend/Tool/Project/Provider/Abstract.php';

/**
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Tool_Project_Provider_View extends Zend_Tool_Project_Provider_Abstract
{

    /**
     * createResource()
     *
     * @param Zend_Tool_Project_Profile $profile
     * @param string $actionName
     * @param string $controllerName
     * @param string $moduleName
     * @return Zend_Tool_Project_Profile_Resource
     */
    public static function createResource(Zend_Tool_Project_Profile $profile, $actionName, $controllerName, $moduleName = null)
    {
        if (!is_string($actionName)) {
            require_once 'Zend/Tool/Project/Provider/Exception.php';
            throw new Zend_Tool_Project_Provider_Exception('Zend_Tool_Project_Provider_View::createResource() expects \"actionName\" is the name of a controller resource to create.');
        }

        if (!is_string($controllerName)) {
            require_once 'Zend/Tool/Project/Provider/Exception.php';
            throw new Zend_Tool_Project_Provider_Exception('Zend_Tool_Project_Provider_View::createResource() expects \"controllerName\" is the name of a controller resource to create.');
        }

        $profileSearchParams = [];

        if ($moduleName) {
            $profileSearchParams = ['modulesDirectory', 'moduleDirectory' => ['moduleName' => $moduleName]];
            $noModuleSearch = null;
        } else {
            $noModuleSearch = ['modulesDirectory'];
        }

        $profileSearchParams[] = 'viewsDirectory';
        $profileSearchParams[] = 'viewScriptsDirectory';

        if (($viewScriptsDirectory = $profile->search($profileSearchParams, $noModuleSearch)) === false) {
            require_once 'Zend/Tool/Project/Provider/Exception.php';
            throw new Zend_Tool_Project_Provider_Exception('This project does not have a viewScriptsDirectory resource.');
        }

        $profileSearchParams['viewControllerScriptsDirectory'] = ['forControllerName' => $controllerName];

        // @todo check if below is failing b/c of above search params
        if (($viewControllerScriptsDirectory = $viewScriptsDirectory->search($profileSearchParams)) === false) {
            $viewControllerScriptsDirectory = $viewScriptsDirectory->createResource('viewControllerScriptsDirectory', ['forControllerName' => $controllerName]);
        }

        return $viewControllerScriptsDirectory->createResource('ViewScriptFile', ['forActionName' => $actionName]);
    }

    /**
     * create()
     *
     * @param string $controllerName
     * @param string $actionNameOrSimpleName
     */
    public function create($controllerName, $actionNameOrSimpleName, $module = null)
    {

        if ($controllerName == '' || $actionNameOrSimpleName == '') {
            require_once 'Zend/Tool/Project/Provider/Exception.php';
            throw new Zend_Tool_Project_Provider_Exception('ControllerName and/or ActionName are empty.');
        }

        $profile = $this->_loadProfile();

        $view = self::createResource($profile, $actionNameOrSimpleName, $controllerName, $module);

        if ($this->_registry->getRequest()->isPretend()) {
            $this->_registry->getResponse(
                'Would create a view script in location ' . $view->getContext()->getPath()
                );
        } else {
            $this->_registry->getResponse(
                'Creating a view script in location ' . $view->getContext()->getPath()
                );
            $view->create();
            $this->_storeProfile();
        }

    }
}
