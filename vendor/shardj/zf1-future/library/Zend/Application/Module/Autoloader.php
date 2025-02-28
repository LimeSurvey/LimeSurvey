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
 * @package    Zend_Application
 * @subpackage Module
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id$
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** @see Zend_Loader_Autoloader_Resource */
require_once 'Zend/Loader/Autoloader/Resource.php';

/**
 * Resource loader for application module classes
 *
 * @uses       Zend_Loader_Autoloader_Resource
 * @category   Zend
 * @package    Zend_Application
 * @subpackage Module
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Application_Module_Autoloader extends Zend_Loader_Autoloader_Resource
{
    /**
     * Constructor
     *
     * @param  array|Zend_Config $options
     */
    public function __construct($options)
    {
        parent::__construct($options);
        $this->initDefaultResourceTypes();
    }

    /**
     * Initialize default resource types for module resource classes
     *
     * @return void
     */
    public function initDefaultResourceTypes()
    {
        $basePath = $this->getBasePath();
        $this->addResourceTypes(
            [
                'dbtable'    => [
                    'namespace' => 'Model_DbTable',
                    'path'      => 'models/DbTable',
                ],
                'mappers'    => [
                    'namespace' => 'Model_Mapper',
                    'path'      => 'models/mappers',
                ],
                'form'       => [
                    'namespace' => 'Form',
                    'path'      => 'forms',
                ],
                'model'      => [
                    'namespace' => 'Model',
                    'path'      => 'models',
                ],
                'plugin'     => [
                    'namespace' => 'Plugin',
                    'path'      => 'plugins',
                ],
                'service'    => [
                    'namespace' => 'Service',
                    'path'      => 'services',
                ],
                'viewhelper' => [
                    'namespace' => 'View_Helper',
                    'path'      => 'views/helpers',
                ],
                'viewfilter' => [
                    'namespace' => 'View_Filter',
                    'path'      => 'views/filters',
                ],
            ]
        );
        $this->setDefaultResourceType('model');
    }
}
