<?php
/**
 * EditableColumn class file.
 * 
 * This widget makes editable column in GridView
 * 
 * @author Vitaliy Potapov <noginsk@rambler.ru>
 * @link https://github.com/vitalets/yii-bootstrap-editable
 * @copyright Copyright &copy; Vitaliy Potapov 2012
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version 1.0.0
 * @since 10/2/12 12:24 AM  renamed class for YiiBooster integration antonio ramirez <antonio@clevertech.ibz>
 */

Yii::import('bootstrap.widgets.TbEditableField');
Yii::import('zii.widgets.grid.CDataColumn');

class TbEditableColumn extends CDataColumn
{
    //editable params
    public $editable = array();

    //flag to render client script only once
    protected $isScriptRendered = false;

    public function init()
    {
        if (!$this->grid->dataProvider instanceOf CActiveDataProvider) {
            throw new CException(Yii::t('zii', 'TbEditableColumn can be applied only to grid based on CActiveDataProvider'));
        }
        if (!$this->name) {
            throw new CException(Yii::t('zii', 'You should provide name for TbEditableColumn'));
        }

        parent::init();
        
        if($this->isEditable($this->grid->dataProvider->model)) {
            $this->attachAjaxUpdateEvent();
        }
    }

    protected function renderDataCellContent($row, $data)
    {
        if(!$this->isEditable($data)) {
            parent::renderDataCellContent($row, $data);
            return; 
        }
        
        $options = CMap::mergeArray($this->editable, array(
            'model'     => $data,
            'attribute' => $this->name,
        ));
        
        //if value defined for column --> use it as element text
        if(strlen($this->value)) {
            ob_start();
            parent::renderDataCellContent($row, $data);
            $text = ob_get_clean();
            $options['text'] = $text;
            $options['encode'] = false;
        }
       
        $editable = $this->grid->controller->createWidget('TbEditableField', $options);

        //manually make selector non unique to match all cells in column
        $selector = get_class($editable->model) . '_' . $editable->attribute;
        $editable->htmlOptions['rel'] = $selector;

        $editable->renderLink();

        //manually render client script (one for all cells in column)
        if (!$this->isScriptRendered) {
            $script = $editable->registerClientScript();
            Yii::app()->getClientScript()->registerScript(__CLASS__ . '#' . $selector.'-event', '
                $("#'.$this->grid->id.'").parent().on("ajaxUpdate.yiiGridView", "#'.$this->grid->id.'", function() {'.$script.'});
            ');
            $this->isScriptRendered = true;
        }
    }
    
   /**
   * Unfortunatly Yii yet does not support custom js events in it's widgets. 
   * So we need to invoke it manually to ensure update of editables on grid ajax update.
   * 
   * issue in Yii github: https://github.com/yiisoft/yii/issues/1313
   * 
   */
    protected function attachAjaxUpdateEvent()
    {
        $trigger = '$("#"+id).trigger("ajaxUpdate");';
        
        //check if trigger already inserted by another column
        if(strpos($this->grid->afterAjaxUpdate, $trigger) !== false) return;
        
        //inserting trigger
        if(strlen($this->grid->afterAjaxUpdate)) {
            $orig = $this->grid->afterAjaxUpdate;
            if(strpos($orig, 'js:')===0) $orig = substr($orig,3);
            $orig = "\n($orig).apply(this, arguments);";
        } else {
            $orig = '';
        }
        $this->grid->afterAjaxUpdate = "js: function(id, data) {
            $trigger $orig
        }";
    }
    
    /**
    * determines wether column currently editable or not
    * 
    * @param mixed $model
    */
    protected function isEditable($model)
    {
         return $model->isAttributeSafe($this->name) && (!array_key_exists('enabled', $this->editable) || $this->editable['enabled'] === true);
    }
}