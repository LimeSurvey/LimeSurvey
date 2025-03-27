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
 * @category  Zend
 * @package   Zend_ProgressBar
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 * @version   $Id$
 */

/**
 * Abstract class for Zend_ProgressBar_Adapters
 *
 * @category  Zend
 * @package   Zend_ProgressBar
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_ProgressBar_Adapter
{
    /**
     * Option keys to skip when calling setOptions()
     *
     * @var array
     */
    protected $_skipOptions = [
        'options',
        'config',
    ];

    /**
     * Create a new adapter
     *
     * $options may be either be an array or a Zend_Config object which
     * specifies adapter related options.
     *
     * @param null|array|Zend_Config $options
     */
    public function __construct($options = null)
    {
        if (is_array($options)) {
            $this->setOptions($options);
        } elseif ($options instanceof Zend_Config) {
            $this->setConfig($options);
        }
    }

    /**
     * Set options via a Zend_Config instance
     *
     * @param  Zend_Config $config
     * @return Zend_ProgressBar_Adapter
     */
    public function setConfig(Zend_Config $config)
    {
        $this->setOptions($config->toArray());

        return $this;
    }

    /**
     * Set options via an array
     *
     * @param  array $options
     * @return Zend_ProgressBar_Adapter
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            if (in_array(strtolower($key), $this->_skipOptions)) {
                continue;
            }

            $method = 'set' . ucfirst($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }

        return $this;
    }

    /**
     * Notify the adapter about an update
     *
     * @param  float   $current       Current progress value
     * @param  float   $max           Max progress value
     * @param  float   $percent       Current percent value
     * @param  integer $timeTaken     Taken time in seconds
     * @param  integer $timeRemaining Remaining time in seconds
     * @param  string  $text          Status text
     * @return void
     */
    abstract public function notify($current, $max, $percent, $timeTaken, $timeRemaining, $text);

    /**
     * Called when the progress is explicitly finished
     *
     * @return void
     */
    abstract public function finish();
}
