<?php

/**
 * Created by PhpStorm.
 * User: sam
 * Date: 8/26/15
 * Time: 11:12 AM
 */
class DummyResponse extends CFormModel implements \ls\interfaces\iResponse
{
    protected $_id;

    public function __construct() {
        $this->_id = \Cake\Utility\Text::uuid();
        $this->save();
    }
    public function __get($name) {
        return null;
    }

    /**
     * @return string The UUID for this response.
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Load the class given an ID,
     * Where should we store these? For now opt for session.
     * @param string $id The UUID for this response.
     * @return self Returns the loaded response or null if not found.
     */
    public static function loadById($id)
    {
        $key = 'dummy.' . $id;
        return App()->session->get($key);
    }

    public function save() {
        $key = 'dummy.' . $this->_id;
        App()->session->add($key, $this);
    }

    public function getToken() {
        return null;
    }
}