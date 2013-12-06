<?php
/**
 * TbFormButtonElement class file.
 *
 * The buttonElementClass for TbForm
 *
 * Support for Yii formbuilder
 *
 * @author Joe Blocher <yii@myticket.at>
 * @copyright Copyright &copy; Joe Blocher 2012
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package bootstrap.widgets
 */

class TbFormButtonElement extends CFormElement
{
    /**
     * @var string the button layout: set as TbButton->type in function render()
     * Valid values are 'primary', 'info', 'success', 'warning', 'danger' and 'inverse'.
     */
    public $layoutType;

    /**
	 * @var array Core button types (alias=>CHtml method name)
	 */
	public static $TbButtonTypes=array(
		'htmlButton'=>'button',
		'htmlSubmit'=>'submit',
		'htmlReset'=>'reset',
		'button'=>'button',
		'submit'=>'submit',
		'reset'=>'reset',
		//'image'=>'imageButton', not supported
		'link'=>'link',

        //new YiiBooster types
        'ajaxLink'=>'ajaxLink',
        'ajaxButton'=>'ajaxButton',
        'ajaxSubmit'=>'ajaxSubmit',
	);

    /**
     * Prepare the options before running the TbButton widget
     *
     * Map Yii formbuilder compatible:
     * $this->type => TbButton->buttonType
     * $this->layoutType => TbButton->type
     *
     * @param $options
     * @return mixed
     */
    protected function prepareWidgetOptions($options)
    {
        //map $this->type to attribute buttonType of TbButton
        $options['buttonType'] = self::$TbButtonTypes[$this->type];
        unset($options['type']);

        //map layoutType to attribute type of TbButton
        if(isset($this->layoutType))
           $options['type'] = $this->layoutType;

        //move $options['name'] to htmlOptions
        $options['htmlOptions']['name'] = $this->name;
        unset($options['name']);

        return $options;
    }

	/**
	 * Run TbButton widget
     *
	 * @return string the rendering result
	 */
	public function render()
	{
        if(!empty(self::$TbButtonTypes[$this->type]))
        {
            $attributes = $this->prepareWidgetOptions($this->attributes);

            ob_start();
            Yii::app()->controller->widget('TbButton',$attributes);
            return ob_get_clean();
        }

        return parent::render();
	}


}
