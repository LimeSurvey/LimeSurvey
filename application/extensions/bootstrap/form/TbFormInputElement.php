<?php
/**
 * TbFormInputElement class file.
 * @author Christoffer Niska <christoffer.niska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package bootstrap.form
 */

/**
 * Bootstrap form input element.
 */
class TbFormInputElement extends CFormInputElement
{
    /**
     * @var array Core input types (alias=>TbHtml method name)
     */
    public static $coreTypes = array(
        'text' => TbHtml::INPUT_TYPE_TEXT,
        'hidden' => TbHtml::INPUT_TYPE_HIDDEN,
        'password' => TbHtml::INPUT_TYPE_PASSWORD,
        'textarea' => TbHtml::INPUT_TYPE_TEXTAREA,
        'file' => TbHtml::INPUT_TYPE_FILE,
        'radio' => TbHtml::INPUT_TYPE_RADIOBUTTONLIST,
        'checkbox' => TbHtml::INPUT_TYPE_CHECKBOX,
        'listbox' => TbHtml::INPUT_TYPE_LISTBOX,
        'dropdownlist' => TbHtml::INPUT_TYPE_DROPDOWNLIST,
        'checkboxlist' => TbHtml::INPUT_TYPE_CHECKBOXLIST,
        'inlinecheckboxlist' => TbHtml::INPUT_TYPE_INLINECHECKBOXLIST,
        'radiolist' => TbHtml::INPUT_TYPE_RADIOBUTTONLIST,
        'inlineradiolist' => TbHtml::INPUT_TYPE_INLINERADIOBUTTONLIST,
        'url' => TbHtml::INPUT_TYPE_URL,
        'email' => TbHtml::INPUT_TYPE_EMAIL,
        'number' => TbHtml::INPUT_TYPE_NUMBER,
        'range' => TbHtml::INPUT_TYPE_RANGE,
        'date' => TbHtml::INPUT_TYPE_DATE,
        'uneditable' => TbHtml::INPUT_TYPE_UNEDITABLE,
        'search' => TbHtml::INPUT_TYPE_SEARCH,
        'widget' => TbHtml::INPUT_TYPE_CUSTOM,
    );

    /**
     * Renders a control group for this input.
     * @return string the rendered control group.
     */
    public function render()
    {
        /** @var TbForm $parent */
        $parent = $this->getParent();
        /** @var TbActiveForm $form */
        $form = $parent->getActiveFormWidget();
        /** @var CModel $model */
        $model = $parent->getModel();

        // Hidden fields do not require control groups.
        if ($this->type === TbHtml::INPUT_TYPE_HIDDEN) {
            return $form->hiddenField($model, $this->name, $this->attributes);
        }

        if (isset(self::$coreTypes[$this->type])) {
            $type = self::$coreTypes[$this->type];
        } else {
            $type = TbHtml::INPUT_TYPE_CUSTOM;
            $properties = $this->attributes;
            $properties['model'] = $model;
            $properties['attribute'] = $this->name;
            $this->attributes['input'] = $parent->getOwner()->widget($this->type, $properties, true);
        }

        return $form->createControlGroup($type, $model, $this->name, $this->attributes, $this->items);
    }
}