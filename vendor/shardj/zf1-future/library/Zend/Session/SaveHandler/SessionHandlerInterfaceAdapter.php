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
 * @package    Zend_Session
 * @subpackage SaveHandler
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * Zend_Session_SaveHandler_SessionHandlerInterfaceAdapter
 *
 * Adapts Zend_Session_SaveHandler_Interface to PHP's native SessionHandlerInterface,
 * avoiding the deprecation of passing individual callbacks to
 * session_set_save_handler() introduced in PHP 8.4.
 *
 * @category   Zend
 * @package    Zend_Session
 * @subpackage SaveHandler
 * @see        https://www.php.net/manual/en/class.sessionhandlerinterface.php
 */
class Zend_Session_SaveHandler_SessionHandlerInterfaceAdapter implements SessionHandlerInterface
{
    /**
     * @var Zend_Session_SaveHandler_Interface
     */
    private Zend_Session_SaveHandler_Interface $saveHandler;

    /**
     * @param Zend_Session_SaveHandler_Interface $saveHandler
     */
    public function __construct(Zend_Session_SaveHandler_Interface $saveHandler)
    {
        $this->saveHandler = $saveHandler;
    }

    /**
     * @param mixed $path
     * @param mixed $name
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function open($path, $name): bool
    {
        return (bool) $this->saveHandler->open($path, $name);
    }

    /**
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function close(): bool
    {
        return (bool) $this->saveHandler->close();
    }

    /**
     * @param mixed $id
     * @return string|false
     */
    #[\ReturnTypeWillChange]
    public function read($id)
    {
        $data = $this->saveHandler->read($id);
        return $data ?? false;
    }

    /**
     * @param mixed $id
     * @param mixed $data
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function write($id, $data): bool
    {
        return (bool) $this->saveHandler->write($id, $data);
    }

    /**
     * @param mixed $id
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function destroy($id): bool
    {
        return (bool) $this->saveHandler->destroy($id);
    }

    /**
     * @param mixed $max_lifetime
     * @return int|false
     */
    #[\ReturnTypeWillChange]
    public function gc($max_lifetime)
    {
        $result = $this->saveHandler->gc($max_lifetime);
        return $result === false ? false : (int) $result;
    }
}