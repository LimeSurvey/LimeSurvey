<?php
/**
 * EditableDetailView class file.
 * 
 * This widget makes editable several attributes of single model, shown as name-value table
 * 
 * @author Vitaliy Potapov <noginsk@rambler.ru>
 * @link https://github.com/vitalets/yii-bootstrap-editable
 * @copyright Copyright &copy; Vitaliy Potapov 2012
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version 1.0.0
 * @since 10/2/12 12:24 AM  renamed class for YiiBooster integration antonio ramirez <antonio@clevertech.ibz>
 */
 
Yii::import('bootstrap.widgets.TbEditableField');
Yii::import('zii.widgets.CDetailView');

class TbEditableDetailView extends CDetailView
{
    //common url for all editables
    public $url = '';

    //set bootstrap css
    public $htmlOptions = array('class'=> 'table table-bordered table-striped table-hover table-condensed');
    
    /**
     * @var string the URL of the CSS file used by this detail view.
     * Defaults to false, meaning that no CSS will be included.
     */
    public $cssFile = false;
    
    public function init()
    {
        if (!$this->data instanceof CModel) {
            throw new CException(Yii::t('zii','Property "data" should be of CModel class.'));
        }

        parent::init();
    }

    protected function renderItem($options, $templateData)
    {
        //if editable set to false --> not editable
        $isEditable = array_key_exists('editable', $options) && $options['editable'] !== false;

        //if name not defined or it is not safe --> not editable
        $isEditable = !empty($options['name']) && $this->data->isAttributeSafe($options['name']);

        if ($isEditable) {    
            //ensure $options['editable'] is array
            if(!array_key_exists('editable', $options) || !is_array($options['editable'])) $options['editable'] = array();

            //take common url
            if (!array_key_exists('url', $options['editable'])) {
                $options['editable']['url'] = $this->url;
            }

            $editableOptions = CMap::mergeArray($options['editable'], array(
                'model'     => $this->data,
                'attribute' => $options['name'],
                'emptytext' => ($this->nullDisplay === null) ? Yii::t('zii', 'Not set') : strip_tags($this->nullDisplay),
            ));
            
            //if value in detailview options provided, set text directly
            if(array_key_exists('value', $options) && $options['value'] !== null) {
                $editableOptions['text'] = $templateData['{value}'];
                $editableOptions['encode'] = false;
            }

            $templateData['{value}'] = $this->controller->widget('TbEditableField', $editableOptions, true);
        } 

        parent::renderItem($options, $templateData);
    }

}

