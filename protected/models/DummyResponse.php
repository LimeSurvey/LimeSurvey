<?php

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

    public function getAttributes($names = null) {
        return $this->fields;
    }

    /**
     * Stores a file with the response.
     * @param $field
     * @param \Psr\Http\Message\UploadedFileInterface[] $file
     */
    public function setFiles($field, array $files) {
        // First check if the question type for the field is actually an upload question.
//         Get the question id from the field name.
//vdd($files);
        if (preg_match('/^\\d+X\\d+X(\\d+)$/', $field, $matches)) {
            $question = Question::model()->findByPk($matches[1]);
            if ($question->type == Question::TYPE_UPLOAD) {
                $directory = App()->runtimePath . "/responses/{$this->dynamicId}";
                if (!is_dir($directory)) {
                    vd(mkdir($directory, null, true));
                }
                $base = "$directory/{$this->getId()}_";
                /** @var \Psr\Http\Message\UploadedFileInterface $file */
                $meta = [];
                foreach($files as $file) {
                    if ($file->getSize() > 0) {
                        $extension = pathinfo($file->getClientFilename())['extension'];
                        $targetPath = $base . App()->securityManager->generateRandomString(10) . '.' . strtolower($extension);
                        $file->moveTo($targetPath);
                        $meta[] = [
                            'filename' => $targetPath,
                            'size' => $file->getSize(),
                            'name' => $file->getClientFilename()
                        ];
                    }
                }
                // Set count.
                $this->fields[$field . "_filecount"] = count($meta);
                // Set metadata
                $this->fields[$field] = json_encode($meta);
            }
        }

    }
}