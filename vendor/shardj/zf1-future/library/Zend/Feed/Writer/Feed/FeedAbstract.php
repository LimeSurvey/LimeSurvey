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
 * @package    Zend_Feed_Writer
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Date
 */
require_once 'Zend/Date.php';

/**
 * @see Zend_Date
 */
require_once 'Zend/Uri.php';

/**
 * @see Zend_Feed_Writer
 */
require_once 'Zend/Feed/Writer.php';

/**
 * @see Zend_Feed_Writer_Entry
 */
require_once 'Zend/Feed/Writer/Entry.php';

/**
 * @see Zend_Feed_Writer_Renderer_Feed_Atom
 */
require_once 'Zend/Feed/Writer/Renderer/Feed/Atom.php';

/**
 * @see Zend_Feed_Writer_Renderer_Feed_Rss
 */
require_once 'Zend/Feed/Writer/Renderer/Feed/Rss.php';

require_once 'Zend/Validate/EmailAddress.php';


/**
 * @category   Zend
 * @package    Zend_Feed_Writer
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Feed_Writer_Feed_FeedAbstract
{
    /**
     * @var array
     */
    protected $_extensions = [];

    /**
     * Contains all Feed level date to append in feed output
     *
     * @var array
     */
    protected $_data = [];

    /**
     * Holds the value "atom" or "rss" depending on the feed type set when
     * when last exported.
     *
     * @var string
     */
    protected $_type = null;

    /**
     * Constructor: Primarily triggers the registration of core extensions and
     * loads those appropriate to this data container.
     *
     * @return void
     */
    public function __construct()
    {
        Zend_Feed_Writer::registerCoreExtensions();
        $this->_loadExtensions();
    }

    /**
     * Set a single author
     *
     * @param  int $index
     * @return void
     */
    public function addAuthor($name, $email = null, $uri = null)
    {
        $author = [];
        if (is_array($name)) {
            if (!array_key_exists('name', $name) || empty($name['name']) || !is_string($name['name'])) {
                require_once 'Zend/Feed/Exception.php';
                throw new Zend_Feed_Exception('Invalid parameter: author array must include a "name" key with a non-empty string value');
            }
            $author['name'] = $name['name'];
            if (isset($name['email'])) {
                if (empty($name['email']) || !is_string($name['email'])) {
                    require_once 'Zend/Feed/Exception.php';
                    throw new Zend_Feed_Exception('Invalid parameter: "email" array value must be a non-empty string');
                }
                $author['email'] = $name['email'];
            }
            if (isset($name['uri'])) {
                if (empty($name['uri']) || !is_string($name['uri']) || !Zend_Uri::check($name['uri'])) {
                    require_once 'Zend/Feed/Exception.php';
                    throw new Zend_Feed_Exception('Invalid parameter: "uri" array value must be a non-empty string and valid URI/IRI');
                }
                $author['uri'] = $name['uri'];
            }
        } else {
            if (empty($name) || !is_string($name)) {
                require_once 'Zend/Feed/Exception.php';
                throw new Zend_Feed_Exception('Invalid parameter: "name" must be a non-empty string value');
            }
            $author['name'] = $name;
            if (isset($email)) {
                if (empty($email) || !is_string($email)) {
                    require_once 'Zend/Feed/Exception.php';
                    throw new Zend_Feed_Exception('Invalid parameter: "email" value must be a non-empty string');
                }
                $author['email'] = $email;
            }
            if (isset($uri)) {
                if (empty($uri) || !is_string($uri) || !Zend_Uri::check($uri)) {
                    require_once 'Zend/Feed/Exception.php';
                    throw new Zend_Feed_Exception('Invalid parameter: "uri" value must be a non-empty string and valid URI/IRI');
                }
                $author['uri'] = $uri;
            }
        }
        $this->_data['authors'][] = $author;
    }

    /**
     * Set an array with feed authors
     *
     * @return void
     */
    public function addAuthors(array $authors)
    {
        foreach($authors as $author) {
            $this->addAuthor($author);
        }
    }

    /**
     * Set the copyright entry
     *
     * @return void
     */
    public function setCopyright($copyright)
    {
        if (empty($copyright) || !is_string($copyright)) {
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid parameter: parameter must be a non-empty string');
        }
        $this->_data['copyright'] = $copyright;
    }

    /**
     * Set the feed creation date
     *
     * @param null|integer|Zend_Date $date
     */
    public function setDateCreated($date = null)
    {
        $zdate = null;
        if ($date === null) {
            $zdate = new Zend_Date;
        } elseif ($date instanceof Zend_Date) {
            $zdate = $date;
        } elseif (ctype_digit((string)$date)) {
            $zdate = new Zend_Date($date, Zend_Date::TIMESTAMP);
        } else {
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid Zend_Date object or UNIX Timestamp passed as parameter');
        }
        $this->_data['dateCreated'] = $zdate;
    }

    /**
     * Set the feed modification date
     *
     * @param null|integer|Zend_Date $date
     */
    public function setDateModified($date = null)
    {
        $zdate = null;
        if ($date === null) {
            $zdate = new Zend_Date;
        } elseif ($date instanceof Zend_Date) {
            $zdate = $date;
        } elseif (ctype_digit((string)$date)) {
            $zdate = new Zend_Date($date, Zend_Date::TIMESTAMP);
        } else {
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid Zend_Date object or UNIX Timestamp passed as parameter');
        }
        $this->_data['dateModified'] = $zdate;
    }

    /**
     * Set the feed last-build date. Ignored for Atom 1.0.
     *
     * @param null|integer|Zend_Date $date
     */
    public function setLastBuildDate($date = null)
    {
        $zdate = null;
        if ($date === null) {
            $zdate = new Zend_Date;
        } elseif ($date instanceof Zend_Date) {
            $zdate = $date;
        } elseif (ctype_digit((string)$date)) {
            $zdate = new Zend_Date($date, Zend_Date::TIMESTAMP);
        } else {
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid Zend_Date object or UNIX Timestamp passed as parameter');
        }
        $this->_data['lastBuildDate'] = $zdate;
    }

    /**
     * Set the feed description
     *
     * @return void
     */
    public function setDescription($description)
    {
        if (empty($description) || !is_string($description)) {
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid parameter: parameter must be a non-empty string');
        }
        $this->_data['description'] = $description;
    }

    /**
     * Set the feed generator entry
     *
     * @return void
     */
    public function setGenerator($name, $version = null, $uri = null)
    {
        if (is_array($name)) {
            $data = $name;
            if (empty($data['name']) || !is_string($data['name'])) {
                require_once 'Zend/Feed/Exception.php';
                throw new Zend_Feed_Exception('Invalid parameter: "name" must be a non-empty string');
            }
            $generator = ['name' => $data['name']];
            if (isset($data['version'])) {
                if (empty($data['version']) || !is_string($data['version'])) {
                    require_once 'Zend/Feed/Exception.php';
                    throw new Zend_Feed_Exception('Invalid parameter: "version" must be a non-empty string');
                }
                $generator['version'] = $data['version'];
            }
            if (isset($data['uri'])) {
                if (empty($data['uri']) || !is_string($data['uri']) || !Zend_Uri::check($data['uri'])) {
                    require_once 'Zend/Feed/Exception.php';
                    throw new Zend_Feed_Exception('Invalid parameter: "uri" must be a non-empty string and a valid URI/IRI');
                }
                $generator['uri'] = $data['uri'];
            }
        } else {
            if (empty($name) || !is_string($name)) {
                require_once 'Zend/Feed/Exception.php';
                throw new Zend_Feed_Exception('Invalid parameter: "name" must be a non-empty string');
            }
            $generator = ['name' => $name];
            if (isset($version)) {
                if (empty($version) || !is_string($version)) {
                    require_once 'Zend/Feed/Exception.php';
                    throw new Zend_Feed_Exception('Invalid parameter: "version" must be a non-empty string');
                }
                $generator['version'] = $version;
            }
            if (isset($uri)) {
                if (empty($uri) || !is_string($uri) || !Zend_Uri::check($uri)) {
                    require_once 'Zend/Feed/Exception.php';
                    throw new Zend_Feed_Exception('Invalid parameter: "uri" must be a non-empty string and a valid URI/IRI');
                }
                $generator['uri'] = $uri;
            }
        }
        $this->_data['generator'] = $generator;
    }

    /**
     * Set the feed ID - URI or URN (via PCRE pattern) supported
     *
     * @param string $id
     */
    public function setId($id)
    {
        if ((empty($id) || !is_string($id) || !Zend_Uri::check($id)) &&
        !preg_match("#^urn:[a-zA-Z0-9][a-zA-Z0-9\-]{1,31}:([a-zA-Z0-9\(\)\+\,\.\:\=\@\;\$\_\!\*\-]|%[0-9a-fA-F]{2})*#", $id)
        && !$this->_validateTagUri($id)) {
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid parameter: parameter must be a non-empty string and valid URI/IRI');
        }
        $this->_data['id'] = $id;
    }

    /**
     * Validate a URI using the tag scheme (RFC 4151)
     *
     * @param string $id
     * @return bool
     */
    protected function _validateTagUri($id)
    {
        if (preg_match('/^tag:(?<name>.*),(?<date>\d{4}-?\d{0,2}-?\d{0,2}):(?<specific>.*)(.*:)*$/', $id, $matches)) {
            $dvalid = false;
            $nvalid = false;

            $date = $matches['date'];
            $d6 = strtotime($date);
            $lenDate = strlen($date);

            if (($lenDate === 4) && $date <= date('Y')) {
                $dvalid = true;
            } elseif (($lenDate === 7) && ($d6 < time())) {
                $dvalid = true;
            } elseif (($lenDate === 10) && ($d6 < time())) {
                $dvalid = true;
            }

            $validator = new Zend_Validate_EmailAddress;

            if ($validator->isValid($matches['name'])) {
                $nvalid = true;
            } else {
                $nvalid = $validator->isValid('info@' . $matches['name']);
            }
            return $dvalid && $nvalid;

        }
        return false;
    }

    /**
     * Set a feed image (URI at minimum). Parameter is a single array with the
     * required key 'uri'. When rendering as RSS, the required keys are 'uri',
     * 'title' and 'link'. RSS also specifies three optional parameters 'width',
     * 'height' and 'description'. Only 'uri' is required and used for Atom rendering.
     *
     * @param array $data
     */
    public function setImage(array $data)
    {
        if (empty($data['uri']) || !is_string($data['uri'])
        || !Zend_Uri::check($data['uri'])) {
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid parameter: parameter \'uri\''
            . ' must be a non-empty string and valid URI/IRI');
        }
        $this->_data['image'] = $data;
    }

    /**
     * Set a feed icon (URI at minimum). Parameter is a single array with the
     * required key 'uri'. Only 'uri' is required and used for Atom rendering.
     * RSS does not support an Icon tag except via Atom 1.0 as an extension.
     *
     * @param array $data
     */
    public function setIcon(array $data)
    {
        if (empty($data['uri']) || !is_string($data['uri'])
        || !Zend_Uri::check($data['uri'])) {
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid parameter: parameter \'uri\''
            . ' must be a non-empty string and valid URI/IRI');
        }
        $this->_data['icon'] = $data;
    }

    /**
     * Set the feed language
     *
     * @return void
     */
    public function setLanguage($language)
    {
        if (empty($language) || !is_string($language)) {
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid parameter: parameter must be a non-empty string');
        }
        $this->_data['language'] = $language;
    }

    /**
     * Set a link to the HTML source
     *
     * @param string $link
     */
    public function setLink($link)
    {
        if (empty($link) || !is_string($link) || !Zend_Uri::check($link)) {
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid parameter: parameter must be a non-empty string and valid URI/IRI');
        }
        $this->_data['link'] = $link;
    }

    /**
     * Set a link to an XML feed for any feed type/version
     *
     * @return void
     */
    public function setFeedLink($link, $type)
    {
        if (empty($link) || !is_string($link) || !Zend_Uri::check($link)) {
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid parameter: "link"" must be a non-empty string and valid URI/IRI');
        }
        if (!in_array(strtolower($type), ['rss', 'rdf', 'atom'])) {
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid parameter: "type"; You must declare the type of feed the link points to, i.e. RSS, RDF or Atom');
        }
        $this->_data['feedLinks'][strtolower($type)] = $link;
    }

    /**
     * Set the feed title
     *
     * @return void
     */
    public function setTitle($title)
    {
        if (empty($title) || !is_string($title)) {
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid parameter: parameter must be a non-empty string');
        }
        $this->_data['title'] = $title;
    }

    /**
     * Set the feed character encoding
     *
     * @param string $encoding
     */
    public function setEncoding($encoding)
    {
        if (empty($encoding) || !is_string($encoding)) {
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid parameter: parameter must be a non-empty string');
        }
        $this->_data['encoding'] = $encoding;
    }

    /**
     * Set the feed's base URL
     *
     * @param string $url
     */
    public function setBaseUrl($url)
    {
        if (empty($url) || !is_string($url) || !Zend_Uri::check($url)) {
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid parameter: "url" array value'
            . ' must be a non-empty string and valid URI/IRI');
        }
        $this->_data['baseUrl'] = $url;
    }

    /**
     * Add a Pubsubhubbub hub endpoint URL
     *
     * @param string $url
     */
    public function addHub($url)
    {
        if (empty($url) || !is_string($url) || !Zend_Uri::check($url)) {
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Invalid parameter: "url" array value'
            . ' must be a non-empty string and valid URI/IRI');
        }
        if (!isset($this->_data['hubs'])) {
            $this->_data['hubs'] = [];
        }
        $this->_data['hubs'][] = $url;
    }

    /**
     * Add Pubsubhubbub hub endpoint URLs
     *
     * @param array $urls
     */
    public function addHubs(array $urls)
    {
        foreach ($urls as $url) {
            $this->addHub($url);
        }
    }

    /**
     * Add a feed category
     *
     * @param string $category
     */
    public function addCategory(array $category)
    {
        if (!isset($category['term'])) {
            require_once 'Zend/Feed/Exception.php';
            throw new Zend_Feed_Exception('Each category must be an array and '
            . 'contain at least a "term" element containing the machine '
            . ' readable category name');
        }
        if (isset($category['scheme'])) {
            if (empty($category['scheme'])
                || !is_string($category['scheme'])
                || !Zend_Uri::check($category['scheme'])
            ) {
                require_once 'Zend/Feed/Exception.php';
                throw new Zend_Feed_Exception('The Atom scheme or RSS domain of'
                . ' a category must be a valid URI');
            }
        }
        if (!isset($this->_data['categories'])) {
            $this->_data['categories'] = [];
        }
        $this->_data['categories'][] = $category;
    }

    /**
     * Set an array of feed categories
     *
     * @param array $categories
     */
    public function addCategories(array $categories)
    {
        foreach ($categories as $category) {
            $this->addCategory($category);
        }
    }

    /**
     * Get a single author
     *
     * @param  int $index
     * @return string|null
     */
    public function getAuthor($index = 0)
    {
        if (isset($this->_data['authors'][$index])) {
            return $this->_data['authors'][$index];
        } else {
            return null;
        }
    }

    /**
     * Get an array with feed authors
     *
     * @return array
     */
    public function getAuthors()
    {
        if (!array_key_exists('authors', $this->_data)) {
            return null;
        }
        return $this->_data['authors'];
    }

    /**
     * Get the copyright entry
     *
     * @return string|null
     */
    public function getCopyright()
    {
        if (!array_key_exists('copyright', $this->_data)) {
            return null;
        }
        return $this->_data['copyright'];
    }

    /**
     * Get the feed creation date
     *
     * @return string|null
     */
    public function getDateCreated()
    {
        if (!array_key_exists('dateCreated', $this->_data)) {
            return null;
        }
        return $this->_data['dateCreated'];
    }

    /**
     * Get the feed modification date
     *
     * @return string|null
     */
    public function getDateModified()
    {
        if (!array_key_exists('dateModified', $this->_data)) {
            return null;
        }
        return $this->_data['dateModified'];
    }

    /**
     * Get the feed last-build date
     *
     * @return string|null
     */
    public function getLastBuildDate()
    {
        if (!array_key_exists('lastBuildDate', $this->_data)) {
            return null;
        }
        return $this->_data['lastBuildDate'];
    }

    /**
     * Get the feed description
     *
     * @return string|null
     */
    public function getDescription()
    {
        if (!array_key_exists('description', $this->_data)) {
            return null;
        }
        return $this->_data['description'];
    }

    /**
     * Get the feed generator entry
     *
     * @return string|null
     */
    public function getGenerator()
    {
        if (!array_key_exists('generator', $this->_data)) {
            return null;
        }
        return $this->_data['generator'];
    }

    /**
     * Get the feed ID
     *
     * @return string|null
     */
    public function getId()
    {
        if (!array_key_exists('id', $this->_data)) {
            return null;
        }
        return $this->_data['id'];
    }

    /**
     * Get the feed image URI
     *
     * @return array
     */
    public function getImage()
    {
        if (!array_key_exists('image', $this->_data)) {
            return null;
        }
        return $this->_data['image'];
    }

    /**
     * Get the feed icon URI
     *
     * @return array
     */
    public function getIcon()
    {
        if (!array_key_exists('icon', $this->_data)) {
            return null;
        }
        return $this->_data['icon'];
    }

    /**
     * Get the feed language
     *
     * @return string|null
     */
    public function getLanguage()
    {
        if (!array_key_exists('language', $this->_data)) {
            return null;
        }
        return $this->_data['language'];
    }

    /**
     * Get a link to the HTML source
     *
     * @return string|null
     */
    public function getLink()
    {
        if (!array_key_exists('link', $this->_data)) {
            return null;
        }
        return $this->_data['link'];
    }

    /**
     * Get a link to the XML feed
     *
     * @return string|null
     */
    public function getFeedLinks()
    {
        if (!array_key_exists('feedLinks', $this->_data)) {
            return null;
        }
        return $this->_data['feedLinks'];
    }

    /**
     * Get the feed title
     *
     * @return string|null
     */
    public function getTitle()
    {
        if (!array_key_exists('title', $this->_data)) {
            return null;
        }
        return $this->_data['title'];
    }

    /**
     * Get the feed character encoding
     *
     * @return string|null
     */
    public function getEncoding()
    {
        if (!array_key_exists('encoding', $this->_data)) {
            return 'UTF-8';
        }
        return $this->_data['encoding'];
    }

    /**
     * Get the feed's base url
     *
     * @return string|null
     */
    public function getBaseUrl()
    {
        if (!array_key_exists('baseUrl', $this->_data)) {
            return null;
        }
        return $this->_data['baseUrl'];
    }

    /**
     * Get the URLs used as Pubsubhubbub hubs endpoints
     *
     * @return string|null
     */
    public function getHubs()
    {
        if (!array_key_exists('hubs', $this->_data)) {
            return null;
        }
        return $this->_data['hubs'];
    }

    /**
     * Get the feed categories
     *
     * @return string|null
     */
    public function getCategories()
    {
        if (!array_key_exists('categories', $this->_data)) {
            return null;
        }
        return $this->_data['categories'];
    }

    /**
     * Resets the instance and deletes all data
     *
     * @return void
     */
    public function reset()
    {
        $this->_data = [];
    }

    /**
     * Set the current feed type being exported to "rss" or "atom". This allows
     * other objects to gracefully choose whether to execute or not, depending
     * on their appropriateness for the current type, e.g. renderers.
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->_type = $type;
    }

    /**
     * Retrieve the current or last feed type exported.
     *
     * @return string Value will be "rss" or "atom"
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Unset a specific data point
     *
     * @param string $name
     */
    public function remove($name)
    {
        if (isset($this->_data[$name])) {
            unset($this->_data[$name]);
        }
    }

    /**
     * Method overloading: call given method on first extension implementing it
     *
     * @param  string $method
     * @param  array $args
     * @return mixed
     * @throws Zend_Feed_Exception if no extensions implements the method
     */
    public function __call($method, $args)
    {
        foreach ($this->_extensions as $extension) {
            try {
                return call_user_func_array([$extension, $method], $args);
            } catch (Zend_Feed_Writer_Exception_InvalidMethodException $e) {
            }
        }
        require_once 'Zend/Feed/Exception.php';
        throw new Zend_Feed_Exception('Method: ' . $method
            . ' does not exist and could not be located on a registered Extension');
    }

    /**
     * Load extensions from Zend_Feed_Writer
     *
     * @return void
     */
    protected function _loadExtensions()
    {
        $all = Zend_Feed_Writer::getExtensions();
        $exts = $all['feed'];
        foreach ($exts as $ext) {
            $className = Zend_Feed_Writer::getPluginLoader()->getClassName($ext);
            $this->_extensions[$ext] = new $className();
            $this->_extensions[$ext]->setEncoding($this->getEncoding());
        }
    }
}
