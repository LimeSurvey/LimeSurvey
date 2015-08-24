<?php

/**
 * Created by PhpStorm.
 * User: sam
 * Date: 8/22/15
 * Time: 2:31 PM
 */
class RenderedQuestion implements ArrayAccess
{

    /**
     * The HTML for the question.
     * @var
     */
    protected $_html;

    /**
     * The validation messages.
     * Keys are the javascript expression, values the messages.
     * @var array
     */
    protected $_validations = [];

    protected $_text;



    public function setQuestionText($text) {
        $this->_text = $text;
    }
    public function addValidation($javascript, $message = null) {
        $this->_validations[$javascript] = $message;
    }


    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return in_array($offset, ['html', 'messages']);
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return string
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        switch ($offset) {
            case 'html':
                $result = $this->_html;
                break;
            case 'messages':
                $result = $this->getMessages();
                break;
            case 'text':
                $result = $this->_text;
                break;


        }

        return $result;
    }

    public function offsetSet($offset, $value)
    {
        throw new \Exception("Cannot set values.");
    }


    public function offsetUnset($offset)
    {
        throw new \Exception("Cannot unset values");
    }

    public function getMessages() {
        $result = '';

        foreach ($this->_validations as $expression => $message) {
            /**
             * Render with irrelevance-expression, so the message gets shown automatically.
             *
             */
            $result .= TbHtml::tag('span', [
                'class' => 'validation-message irrelevant',
                'data-irrelevance-expression' => $expression,
            ], $message);
        }
        return $result;
    }

    public function setHtml($html) {
        $this->_html = $html;
    }
}