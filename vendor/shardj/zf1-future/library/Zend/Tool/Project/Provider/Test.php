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
 * @see Zend_Tool_Project_Provider_Exception
 */
require_once 'Zend/Tool/Project/Provider/Exception.php';

/**
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Tool_Project_Provider_Test extends Zend_Tool_Project_Provider_Abstract
{

    protected $_specialties = ['Application', 'Library'];

    /**
     * isTestingEnabled()
     *
     * @param Zend_Tool_Project_Profile $profile
     * @return bool
     */
    public static function isTestingEnabled(Zend_Tool_Project_Profile $profile)
    {
        $profileSearchParams = ['testsDirectory'];
        $testsDirectory = $profile->search($profileSearchParams);

        return $testsDirectory->isEnabled();
    }

    public static function isPHPUnitAvailable()
    {
        if (class_exists('PHPUnit_Runner_Version', false)) {
            return true;
        }

        $included = @include 'PHPUnit/Runner/Version.php';

        if ($included === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * createApplicationResource()
     *
     * @param Zend_Tool_Project_Profile $profile
     * @param string $controllerName
     * @param string $actionName
     * @param string $moduleName
     * @return Zend_Tool_Project_Profile_Resource
     */
    public static function createApplicationResource(Zend_Tool_Project_Profile $profile, $controllerName, $actionName, $moduleName = null)
    {
        if (!is_string($controllerName)) {
            throw new Zend_Tool_Project_Provider_Exception('Zend_Tool_Project_Provider_View::createApplicationResource() expects \"controllerName\" is the name of a controller resource to create.');
        }

        if (!is_string($actionName)) {
            throw new Zend_Tool_Project_Provider_Exception('Zend_Tool_Project_Provider_View::createApplicationResource() expects \"actionName\" is the name of a controller resource to create.');
        }

        $testsDirectoryResource = $profile->search('testsDirectory');

        // parentOfController could either be application/ or a particular module folder, which is why we use this name
        if (($testParentOfControllerDirectoryResource = $testsDirectoryResource->search('testApplicationDirectory')) === false) {
            $testParentOfControllerDirectoryResource = $testsDirectoryResource->createResource('testApplicationDirectory');
        }

        if ($moduleName) {
            if (($testAppModulesDirectoryResource = $testParentOfControllerDirectoryResource->search('testApplicationModulesDirectory')) === false) {
                $testAppModulesDirectoryResource = $testParentOfControllerDirectoryResource->createResource('testApplicationModulesDirectory');
            }

            if (($testAppModuleDirectoryResource = $testAppModulesDirectoryResource->search(['testApplicationModuleDirectory' => ['forModuleName' => $moduleName]])) === false) {
                $testAppModuleDirectoryResource = $testAppModulesDirectoryResource->createResource('testApplicationModuleDirectory', ['forModuleName' => $moduleName]);
            }

            $testParentOfControllerDirectoryResource = $testAppModuleDirectoryResource;
        }

        if (($testAppControllerDirectoryResource = $testParentOfControllerDirectoryResource->search('testApplicationControllerDirectory', 'testApplicationModuleDirectory')) === false) {
            $testAppControllerDirectoryResource = $testParentOfControllerDirectoryResource->createResource('testApplicationControllerDirectory');
        }

        if (($testAppControllerFileResource = $testAppControllerDirectoryResource->search(['testApplicationControllerFile' => ['forControllerName' => $controllerName]])) === false) {
            $testAppControllerFileResource = $testAppControllerDirectoryResource->createResource('testApplicationControllerFile', ['forControllerName' => $controllerName]);
        }

        return $testAppControllerFileResource->createResource('testApplicationActionMethod', ['forActionName' => $actionName]);
    }

    /**
     * createLibraryResource()
     *
     * @param Zend_Tool_Project_Profile $profile
     * @param string $libraryClassName
     * @return Zend_Tool_Project_Profile_Resource
     */
    public static function createLibraryResource(Zend_Tool_Project_Profile $profile, $libraryClassName)
    {
        $testLibraryDirectoryResource = $profile->search(['TestsDirectory', 'TestLibraryDirectory']);


        $fsParts = explode('_', $libraryClassName);

        $currentDirectoryResource = $testLibraryDirectoryResource;

        while ($nameOrNamespacePart = array_shift($fsParts)) {

            if (count($fsParts) > 0) {

                if (($libraryDirectoryResource = $currentDirectoryResource->search(['TestLibraryNamespaceDirectory' => ['namespaceName' => $nameOrNamespacePart]])) === false) {
                    $currentDirectoryResource = $currentDirectoryResource->createResource('TestLibraryNamespaceDirectory', ['namespaceName' => $nameOrNamespacePart]);
                } else {
                    $currentDirectoryResource = $libraryDirectoryResource;
                }

            } else {

                if (($libraryFileResource = $currentDirectoryResource->search(['TestLibraryFile' => ['forClassName' => $libraryClassName]])) === false) {
                    $libraryFileResource = $currentDirectoryResource->createResource('TestLibraryFile', ['forClassName' => $libraryClassName]);
                }

            }

        }

        return $libraryFileResource;
    }

    public function enable()
    {

    }

    public function disable()
    {

    }

    /**
     * create()
     *
     * @param string $libraryClassName
     */
    public function create($libraryClassName)
    {
        $profile = $this->_loadProfile();

        if (!self::isTestingEnabled($profile)) {
            $this->_registry->getResponse()->appendContent('Testing is not enabled for this project.');
        }

        $testLibraryResource = self::createLibraryResource($profile, $libraryClassName);

        $response = $this->_registry->getResponse();

        if ($this->_registry->getRequest()->isPretend()) {
            $response->appendContent('Would create a library stub in location ' . $testLibraryResource->getContext()->getPath());
        } else {
            $response->appendContent('Creating a library stub in location ' . $testLibraryResource->getContext()->getPath());
            $testLibraryResource->create();
            $this->_storeProfile();
        }

    }

}
