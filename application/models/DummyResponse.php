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

    protected $fields = [];
    public function __construct(Survey $survey) {
        $this->_id = \Cake\Utility\Text::uuid();
        $this->save();
        foreach($survey->getColumns() as $field => $type) {
            $this->fields[$field] = null;
        }
    }

    public function __get($name) {
        if (array_key_exists($name, $this->fields)) {
            return $this->fields[$name];
        }
        parent::__get($name);
    }

    public function __set($name, $value) {
        if (array_key_exists($name, $this->fields)) {
            $this->fields[$name] = $value;
        } else {
            parent::__set($name, $value);
        }
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

    public function getAttributes() {
        return $this->fields;
    }
}