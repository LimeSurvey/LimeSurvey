<?php
/**
 * JSONStorage.php
 *
 * Provides a very simple way to persistent JSON storage. Acts as a registry key saver.
 *
 * Example of use:
 *
 * $j = new JSONStorage();
 *
 * $j->addRegistry('custom');
 * echo $j->registryExists('custom');
 * $j->setData('mydata','super','custom');
 * $j->save();
 * $j->load();
 *
 * echo $j->getData('super','custom');
 *
 * @author: antonio ramirez <antonio@clevertech.biz>
 * Date: 8/2/12
 * Time: 6:48 PM
 */
class JSONStorage extends CComponent
{
	/**
	 * const string the key to keep value information
	 */
	const META = 'meta';
	/**
	 * const string the key of the registry
	 */
	const REGISTRY = 'registry';

	/**
	 * @var string the filename to save the values to
	 */
	protected  $filename = 'registry.json';

	/**
	 * @var string the full path to the directory with read/write access to save the registry to. If none set, the
	 * component will used the application's runtime folder
	 */
	protected $path;

	/**
	 * @var bool whether the registry has changed or not
	 */
	protected $dirty = false;

	/**
	 * @var null|string the name of the default registry
	 */
	protected $default = "default";

	/**
	 * @var array the data of the registry
	 */
	protected $data = array(
		self::META => array(
			"updated" => "",
			"hash" => ""
		),
		self::REGISTRY => array(
			/* collection name */
			"default" => array(
				"foo" => "bar" /* attributes by example */
			)
		)
	);

	/**
	 * class constructor
	 * @param null $registry
	 */
	public function __construct($registry = null)
	{
		if (null === $this->path)
		{
			$this->setPath(Yii::app()->getRuntimePath()); // JSON storage will be at the app runtime path
		}
		$this->setFile($this->filename);

		$this->load();

		// setup domain
		if ($registry)
		{
			if ($this->registryExists($registry) == false) $this->addRegistry($registry);
			$this->default = $registry;
		}
	}

	/**
	 * class destructor
	 */
	public function __destruct()
	{
		// flush data
		$this->flush();
	}

	/**
	 * Fires before registry has been saved
	 * @param $event
	 */
	public function onBeforeSave($event)
	{
		$this->raiseEvent('onBeforeSave', $event);
	}

	/**
	 * Fires after the registry has been saved
	 * @param $event
	 */
	public function onAfterSave($event)
	{
		$this->raiseEvent('onAfterSave', $event);
	}

	/**
	 * Property set path
	 * @param $path the full path of the directory with read/write access to save the registry file to
	 * @return bool
	 * @throws Exception
	 */
	public function setPath($path)
	{
		if (is_dir($path) && is_writable($path))
		{
			$this->path = substr($path, -1) == DIRECTORY_SEPARATOR ? $path : $path . DIRECTORY_SEPARATOR;
			return true;
		}
		throw new Exception('"Path" must be a writable directory.');
	}

	/**
	 * Property get path
	 * @return string
	 */
	public function getPath()
	{
		return $this->path;
	}

	/**
	 * Property set file
	 * @param $file the filename to save the registry to
	 */
	public function setFile($file)
	{
		$this->filename = $this->path . $file;
	}

	/**
	 * Property get file
	 * @return string
	 */
	public function getFile()
	{
		return $this->filename;
	}

	/**
	 * Verifies data integrity
	 * @return bool
	 */
	public function verify()
	{
		$registry = function_exists('json_encode') ? json_encode($this->data[self::REGISTRY]) : CJSON::encode($this->data[self::REGISTRY]);
		return $this->data[self::META]["hash"] == md5($registry);
	}

	/**
	 * Loads registry data into memory
	 * @throws Exception
	 */
	public function load()
	{
		// load data
		if (file_exists($this->getFile()))
		{
			$json = file_get_contents($this->getFile());
			if (strlen($json) == 0)
			{
				return;
			}
			$this->data = $this->decode($json);

			if ($this->data === null)
			{
				throw new Exception("Error while trying to decode \"$this->file\".");
			}

			if (!$this->verify())
			{
				throw new Exception($this->getFile() . ' failed checksum validation.');
			}
		}

	}

	/**
	 * Saves registry data to the file
	 */
	public function save()
	{
		if ($this->hasEventHandler('onBeforeSave'))
			$this->onBeforeSave(new CEvent($this));
		$this->flush();
		if ($this->hasEventHandler('onAfterSave'))
			$this->onAfterSave(new CEvent($this));
	}

	/**
	 * Saves data to the registry
	 * @param $key the name of the key that will hold the data
	 * @param $data the data to save
	 * @param null $registry the name of the registry
	 * @return bool
	 */
	public function setData($key, $data, $registry = null)
	{
		if ($registry == null) $registry = $this->default;
		if (is_string($key . $registry) && $this->registryExists($registry))
		{
			$this->data[self::REGISTRY][$registry][$key] = $data;
			$this->dirty = true;
			return true;
		}
		return false;
	}

	/**
	 * Retrieves a data value from the registry
	 * @param $key the name of the key that holds the data
	 * @param null $registry the registry name
	 * @return mixed the data in the key value, null otherwise
	 */
	public function getData($key, $registry = null)
	{
		if ($registry == null)
		{
			$registry = $this->default;
		}
		if (is_string($key . $registry) && $this->registryExists($registry))
		{
			if (array_key_exists($key, $this->data[self::REGISTRY][$registry]))
			{
				return $this->data[self::REGISTRY][$registry][$key];
			}
		}
		return null;
	}

	/**
	 * Removes data from a key in the registry
	 * @param $key the key name that holds the data to remove
	 * @param null $registry the registry name
	 * @return bool true if successful, false otherwise
	 */
	public function removeData($key, $registry = null)
	{
		if ($registry == null)
		{
			$registry = $this->default;
		}
		if (is_string($key . $registry) && $this->registryExists($registry))
		{
			if (array_key_exists($key, $this->data[self::REGISTRY][$registry]))
			{
				unset($this->data[self::REGISTRY][$registry][$key]);
				$this->dirty = true;
				return true;
			}
		}
		return false;
	}

	/**
	 * Retrieves the number of keys in registry
	 * @param null $registry the registry name
	 * @return int the data length
	 */
	public function getLength($registry = null)
	{
		if($registry == null)
		{
			$registry = $this->default;
		}
		if(is_string($registry) && $this->registryExists($registry))
		{
			return count($this->data[self::REGISTRY][$registry]);
		}
		return 0;
	}

	/**
	 * Retrieves a registry collection based on its name
	 * @param $registry the name of the registry to retrieve
	 * @return mixed|null the registry, null if none found
	 */
	public function getRegistry($registry)
	{
		return $this->registryExists($registry) ? $this->data[self::REGISTRY][$registry] : null;
	}

	/**
	 * Checkes whether a collection exists (registry)
	 * @param $registry the name of the registry to check existence
	 * @return bool
	 */
	public function registryExists($registry)
	{
		return array_key_exists($registry, $this->data[self::REGISTRY]);
	}

	/**
	 * Add new collection name
	 * @param $registry the name of the registry (collection) to create
	 * @return bool
	 */
	public function addRegistry($registry)
	{
		if ($this->registryExists($registry)) return false;
		$this->data[self::REGISTRY][$registry] = array();
		$this->dirty = true;
	}

	/**
	 * Remove an existing collection and all associated data
	 * @param $registry the name of the registry to remove
	 */
	public function removeRegistry($registry)
	{
		if ($this->registryExists($registry))
		{
			unset($this->data[self::REGISTRY][$registry]);
			$this->dirty = true;
			return true;
		}
		return false;
	}

	/**
	 * Saves the global registry to the file
	 * @return bool
	 * @throws Exception
	 */
	private function flush()
	{
		// check if writeback is needed
		if ($this->dirty == false) return true;
		// prepare to writeback to file
		$data = $this->data;
		$registry = $this->encode($this->data[self::REGISTRY]);
		$data[self::META]["updated"] = date("c");
		$data[self::META]["hash"] = md5($registry);

		// overwrite existing data
		if (file_put_contents($this->getFile(), $this->encode($data)))
		{
			return true;
		} else throw new Exception(strtr('Unable to write back to {FILE}. Data will be lost!', array('{FILE}' => $this->getFile())));
	}

	/**
	 * JSON encodes the data
	 * @param $data
	 * @return string
	 */
	private function encode($data)
	{
		return function_exists('json_encode') ? json_encode($data) : CJSON::encode($data);
	}

	/**
	 * JSON decodes the data
	 * @param $data
	 * @return mixed
	 */
	private function decode($data)
	{
		return function_exists('json_decode') ? json_decode($data, true) : CJSON::decode($data, true);
	}

}
