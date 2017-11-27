<?php
/**
 * WhSwitch widget class
 *
 * @see https://github.com/nostalgiaz/bootstrap-switch
 *
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @copyright Copyright &copy; 2amigos.us 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package YiiWheels.widgets.switch
 * @uses YiiStrap.helpers.TbArray
 * @uses YiiStrap.helpers.TbHtml
 */
Yii::import('bootstrap.helpers.TbArray');
Yii::import('bootstrap.helpers.TbHtml');

class WhSwitch extends CInputWidget
{
    /**
     * @var string input type. It can be 'radio' or 'checkbox'
     */
    public $inputType = 'checkbox';

    /**
     * @var string size of switch button. Supports 'large', 'small' or 'mini'
     */
    public $size = 'small';

    /**
     * @var string color when is on. It can be any of bootstrap button states. Defaults to 'primary'.
     */
    public $onColor = 'primary';

    /**
     * @var string color when is off. It can be any of bootstrap button states. Defaults to 'warning'.
     */
    public $offColor = 'warning';

    /**
     * @var bool whether the slide is animated or not.
     */
    public $animated = true;

    /**
     * @var string the label when is on. Defaults to 'On'.
     */
    public $onLabel;

    /**
     * @var string the label when is off. Defaults to 'Off'.
     */
    public $offLabel;

    /**
     * @var string the text label. Defaults to null.
     */
    public $textLabel;

    /**
     * @var string[] the JavaScript event handlers.
     */
    public $events = array();

    /**
     * @var bool whether to display minified versions of the files or not
     */
    public $debugMode = false;


    /**
     * Initializes the widget.
     * @throws CException
     */
    public function init()
    {
        if (!in_array($this->inputType, array('radio', 'checkbox'))) {
            throw new CException(Yii::t('zii', '"inputType" attribute must be of type "radio" or "checkbox"'));
        }
        if (!in_array($this->size, array('mini', 'small', 'normal', 'large'))) {
            throw new CException(Yii::t('zii', 'Unknown value for attribute "size".'));
        }
        $this->attachBehavior('ywplugin', array('class' => 'yiiwheels.behaviors.WhPlugin'));

        if (!$this->animated) {
            $this->htmlOptions['data-animate'] = 'false';
        }

        if (!isset($this->htmlOptions['uncheckValue']))
        {
            $this->htmlOptions['uncheckValue']='0';
        }

        $this->htmlOptions['data-on-text'] = $this->onLabel;
        $this->htmlOptions['data-off-text'] = $this->offLabel;
        $this->htmlOptions['data-label-text'] = $this->textLabel;
        $this->htmlOptions['data-on-color'] = $this->onColor;
        $this->htmlOptions['data-off-color'] = $this->offColor;
        $this->htmlOptions['data-size'] = $this->size;
        $this->htmlOptions['data-is-bootstrap-switch'] = true;
    }

    /**
     * Runs the widget.
     */
    public function run()
    {
        $this->renderField();
        $this->registerClientScript();
    }

    /**
     * Renders the typeahead field
     */
    public function renderField()
    {
        list($name, $id) = $this->resolveNameID();

        TbArray::defaultValue('id', $id, $this->htmlOptions);
        TbArray::defaultValue('name', $name, $this->htmlOptions);

        if ($this->hasModel()) {
            echo $this->inputType == 'radio'
                ? CHtml::activeRadioButton($this->model, $this->attribute, $this->htmlOptions)
                : CHtml::activeCheckBox($this->model, $this->attribute, $this->htmlOptions);
        } else {
            echo $this->inputType == 'radio'
                ? CHtml::radioButton($this->name, $this->value, $this->htmlOptions)
                : CHtml::checkBox($this->name, $this->value, $this->htmlOptions);
        }
    }

    /**
     * Registers required client script for bootstrap typeahead. It is not used through bootstrap->registerPlugin
     * NOT NEEDED ANYMORE BECAUSE THE BOOTSTRAP SWITCH IS IN OFFICIAL THIRD PARTY
     * in order to attach events if any
     */
    public function registerClientScript()
    {

    }
}
