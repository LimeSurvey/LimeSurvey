<?php
 /**
 * 
 * WhSelectBox.php
 *
 * Date: 06/09/14
 * Time: 13:57
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @link http://www.ramirezcobos.com/
 * @link http://www.2amigos.us/
 */
Yii::import('yiiwheels.widgets.formhelpers.WhInputWidget');

class WhSelectBox extends WhInputWidget
{
    /**
     * @var array the array keys are option values, and the array values
     * are the corresponding option labels.
     */
    public $data = array();

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        TbHtml::addCssClass('bfh-selectbox', $this->htmlOptions);
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $input[] = CHtml::openTag('div', $this->htmlOptions);
        foreach ($this->data as $key => $value) {
            $input[] = CHtml::tag('div', array('data-value' => (string)$key), (string)$value);
        }
        $input[] = CHtml::closeTag('div');

        echo implode("\n", $input);

        $this->registerPlugin('bfhselectbox');
    }
} 