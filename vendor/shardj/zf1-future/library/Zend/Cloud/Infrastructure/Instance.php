<?php
/**
 * @category   Zend
 * @package    Zend_Cloud
 * @subpackage Infrastructure
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * Instance of an infrastructure service
 *
 * @package    Zend_Cloud
 * @subpackage Infrastructure
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Cloud_Infrastructure_Instance
{
    public const STATUS_RUNNING       = 'running';
    public const STATUS_STOPPED       = 'stopped';
    public const STATUS_SHUTTING_DOWN = 'shutting-down';
    public const STATUS_REBOOTING     = 'rebooting';
    public const STATUS_TERMINATED    = 'terminated';
    public const STATUS_PENDING       = 'pending';
    public const STATUS_REBUILD       = 'rebuild';
    public const INSTANCE_ID          = 'id';
    public const INSTANCE_IMAGEID     = 'imageId';
    public const INSTANCE_NAME        = 'name';
    public const INSTANCE_STATUS      = 'status';
    public const INSTANCE_PUBLICDNS   = 'publicDns';
    public const INSTANCE_CPU         = 'cpu';
    public const INSTANCE_RAM         = 'ram';
    public const INSTANCE_STORAGE     = 'storageSize';
    public const INSTANCE_ZONE        = 'zone';
    public const INSTANCE_LAUNCHTIME  = 'launchTime';
    public const MONITOR_CPU          = 'CpuUsage';
    public const MONITOR_RAM          = 'RamUsage';
    public const MONITOR_NETWORK_IN   = 'NetworkIn';
    public const MONITOR_NETWORK_OUT  = 'NetworkOut';
    public const MONITOR_DISK         = 'DiskUsage';
    public const MONITOR_DISK_WRITE   = 'DiskWrite';
    public const MONITOR_DISK_READ    = 'DiskRead';
    public const MONITOR_START_TIME   = 'StartTime';
    public const MONITOR_END_TIME     = 'EndTime';
    public const SSH_USERNAME         = 'username';
    public const SSH_PASSWORD         = 'password';
    public const SSH_PRIVATE_KEY      = 'privateKey';
    public const SSH_PUBLIC_KEY       = 'publicKey';
    public const SSH_PASSPHRASE       = 'passphrase';

    /**
     * @var Zend_Cloud_Infrastructure_Adapter
     */
    protected $adapter;

    /**
     * Instance's attribute
     *
     * @var array
     */
    protected $attributes;

    /**
     * Attributes required for an instance
     *
     * @var array
     */
    protected $attributeRequired = [
        self::INSTANCE_ID,
        self::INSTANCE_STATUS,
        self::INSTANCE_IMAGEID,
        self::INSTANCE_ZONE
    ];

    /**
     * Constructor
     *
     * @param  Adapter $adapter
     * @param  array $data
     * @return void
     */
    public function __construct($adapter, $data = null)
    {
        if (!($adapter instanceof Zend_Cloud_Infrastructure_Adapter)) {
            require_once 'Zend/Cloud/Infrastructure/Exception.php';
            throw new Zend_Cloud_Infrastructure_Exception("You must pass a Zend_Cloud_Infrastructure_Adapter instance");
        }

        if (is_object($data)) {
            if (method_exists($data, 'toArray')) {
                $data= $data->toArray();
            } elseif ($data instanceof Traversable) {
                $data = iterator_to_array($data);
            }
        }

        if (empty($data) || !is_array($data)) {
            require_once 'Zend/Cloud/Infrastructure/Exception.php';
            throw new Zend_Cloud_Infrastructure_Exception("You must pass an array of parameters");
        }

        foreach ($this->attributeRequired as $key) {
            if (empty($data[$key])) {
                require_once 'Zend/Cloud/Infrastructure/Exception.php';
                throw new Zend_Cloud_Infrastructure_Exception(sprintf(
                    'The param "%s" is a required param for %s',
                    $key,
                    __CLASS__
                ));
            }
        }

        $this->adapter    = $adapter;
        $this->attributes = $data;
    }

    /**
     * Get Attribute with a specific key
     *
     * @param array $data
     * @return misc|false
     */
    public function getAttribute($key)
    {
        if (!empty($this->attributes[$key])) {
            return $this->attributes[$key];
        }
        return false;
    }

    /**
     * Get all the attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Get the instance's id
     *
     * @return string
     */
    public function getId()
    {
        return $this->attributes[self::INSTANCE_ID];
    }

    /**
     * Get the instance's image id
     *
     * @return string
     */
    public function getImageId()
    {
        return $this->attributes[self::INSTANCE_IMAGEID];
    }

    /**
     * Get the instance's name
     *
     * @return string
     */
    public function getName()
    {
        return $this->attributes[self::INSTANCE_NAME];
    }

    /**
     * Get the status of the instance
     *
     * @return string|boolean
     */
    public function getStatus()
    {
        return $this->adapter->statusInstance($this->attributes[self::INSTANCE_ID]);
    }

    /**
     * Wait for status $status with a timeout of $timeout seconds
     *
     * @param  string $status
     * @param  integer $timeout
     * @return boolean
     */
    public function waitStatus($status, $timeout = Adapter::TIMEOUT_STATUS_CHANGE)
    {
        return $this->adapter->waitStatusInstance($this->attributes[self::INSTANCE_ID], $status, $timeout);
    }

    /**
     * Get the public DNS of the instance
     *
     * @return string
     */
    public function getPublicDns()
    {
        if (!isset($this->attributes[self::INSTANCE_PUBLICDNS])) {
            $this->attributes[self::INSTANCE_PUBLICDNS] =  $this->adapter->publicDnsInstance($this->attributes[self::INSTANCE_ID]);
        }
        return $this->attributes[self::INSTANCE_PUBLICDNS];
    }

    /**
     * Get the instance's CPU
     *
     * @return string
     */
    public function getCpu()
    {
        return $this->attributes[self::INSTANCE_CPU];
    }

    /**
     * Get the instance's RAM size
     *
     * @return string
     */
    public function getRamSize()
    {
        return $this->attributes[self::INSTANCE_RAM];
    }

    /**
     * Get the instance's storage size
     *
     * @return string
     */
    public function getStorageSize()
    {
        return $this->attributes[self::INSTANCE_STORAGE];
    }

    /**
     * Get the instance's zone
     *
     * @return string
     */
    public function getZone()
    {
        return $this->attributes[self::INSTANCE_ZONE];
    }

    /**
     * Get the instance's launch time
     *
     * @return string
     */
    public function getLaunchTime()
    {
        return $this->attributes[self::INSTANCE_LAUNCHTIME];
    }

    /**
     * Reboot the instance
     *
     * @return boolean
     */
    public function reboot()
    {
        return $this->adapter->rebootInstance($this->attributes[self::INSTANCE_ID]);
    }

    /**
     * Stop the instance
     *
     * @return boolean
     */
    public function stop()
    {
        return $this->adapter->stopInstance($this->attributes[self::INSTANCE_ID]);
    }

    /**
     * Start the instance
     *
     * @return boolean
     */
    public function start()
    {
        return $this->adapter->startInstance($this->attributes[self::INSTANCE_ID]);
    }

    /**
     * Destroy the instance
     *
     * @return boolean
     */
    public function destroy()
    {
        return $this->adapter->destroyInstance($this->attributes[self::INSTANCE_ID]);
    }

    /**
     * Return the system informations about the $metric of an instance
     *
     * @param  string $metric
     * @param  null|array $options
     * @return array|boolean
     */
    public function monitor($metric, $options = null)
    {
        return $this->adapter->monitorInstance($this->attributes[self::INSTANCE_ID], $metric, $options);
    }

    /**
     * Run arbitrary shell script on the instance
     *
     * @param  array $param
     * @param  string|array $cmd
     * @return string|array
     */
    public function deploy($params, $cmd)
    {
        return $this->adapter->deployInstance($this->attributes[self::INSTANCE_ID], $params, $cmd);
    }
}
