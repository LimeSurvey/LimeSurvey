<?php
/**
 * TbFormButtonElement class file.
 * @author Christoffer Niska <christoffer.niska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package bootstrap.form
 */

/**
 * Bootstrap form button element.
 */
class TbFormButtonElement extends CFormButtonElement
{
    /**
     * @var array Core input types (alias=>TbHtml button type)
     */
    public static $coreTypes = array(
        'htmlButton' => TbHtml::BUTTON_TYPE_HTML,
        'htmlReset' => TbHtml::BUTTON_TYPE_RESET,
        'htmlSubmit' => TbHtml::BUTTON_TYPE_SUBMIT,
        'submit' => TbHtml::BUTTON_TYPE_INPUTSUBMIT,
        'button' => TbHtml::BUTTON_TYPE_INPUTBUTTON,
        'image' => TbHtml::BUTTON_TYPE_IMAGE,
        'reset' => TbHtml::BUTTON_TYPE_RESET,
        'link' => TbHtml::BUTTON_TYPE_LINK,
        'ajaxLink' => TbHtml::BUTTON_TYPE_AJAXLINK,
        'ajaxButton' => TbHtml::BUTTON_TYPE_AJAXBUTTON,
    );

    /**
     * Returns this button.
     * @return string the rendering result.
     */
    public function render()
    {
        $attributes = $this->attributes;
        $attributes['name'] = $this->name;

        if (isset(self::$coreTypes[$this->type])) {
            $type = self::$coreTypes[$this->type];
            return TbHtml::btn($type, $this->label, $attributes);
        } else {
			return $this->getParent()->getOwner()->widget($this->type, $attributes, true);
        }
    }
}