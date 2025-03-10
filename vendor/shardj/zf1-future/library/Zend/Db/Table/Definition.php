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
 * @package    Zend_Db
 * @subpackage Table
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * Class for SQL table interface.
 *
 * @category   Zend
 * @package    Zend_Db
 * @subpackage Table
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Db_Table_Definition
{

    /**
     * @var array
     */
    protected $_tableConfigs = [];

    /**
     * __construct()
     *
     * @param array|Zend_Config $options
     */
    public function __construct($options = null)
    {
        if ($options instanceof Zend_Config) {
            $this->setConfig($options);
        } elseif (is_array($options)) {
            $this->setOptions($options);
        }
    }

    /**
     * setConfig()
     *
     * @param Zend_Config $config
     * @return $this
     */
    public function setConfig(Zend_Config $config)
    {
        $this->setOptions($config->toArray());
        return $this;
    }

    /**
     * setOptions()
     *
     * @param array $options
     * @return $this
     */
    public function setOptions(Array $options)
    {
        foreach ($options as $optionName => $optionValue) {
            $this->setTableConfig($optionName, $optionValue);
        }
        return $this;
    }

    /**
     * @param string $tableName
     * @param array  $tableConfig
     * @return $this
     */
    public function setTableConfig($tableName, array $tableConfig)
    {
        // @todo logic here
        $tableConfig[Zend_Db_Table::DEFINITION_CONFIG_NAME] = $tableName;
        $tableConfig[Zend_Db_Table::DEFINITION] = $this;

        if (!isset($tableConfig[Zend_Db_Table::NAME])) {
            $tableConfig[Zend_Db_Table::NAME] = $tableName;
        }

        $this->_tableConfigs[$tableName] = $tableConfig;
        return $this;
    }

    /**
     * getTableConfig()
     *
     * @param string $tableName
     * @return array
     */
    public function getTableConfig($tableName)
    {
        return $this->_tableConfigs[$tableName];
    }

    /**
     * removeTableConfig()
     *
     * @param string $tableName
     */
    public function removeTableConfig($tableName)
    {
        unset($this->_tableConfigs[$tableName]);
    }

    /**
     * hasTableConfig()
     *
     * @param string $tableName
     * @return bool
     */
    public function hasTableConfig($tableName)
    {
        return (isset($this->_tableConfigs[$tableName]));
    }

}
