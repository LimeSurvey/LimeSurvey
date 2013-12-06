<?php
/**
 * EditableSaver class file.
 * 
 * This component is server-side part for editable widgets. It performs update of one model attribute.
 * 
 * @author Vitaliy Potapov <noginsk@rambler.ru>
 * @link https://github.com/vitalets/yii-bootstrap-editable
 * @copyright Copyright &copy; Vitaliy Potapov 2012
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version 1.0.0
 * @since 10/2/12 12:24 AM  renamed class for YiiBooster integration antonio ramirez <antonio@clevertech.ibz>
 */
 
class TbEditableSaver extends CComponent
{
    /**
     * scenarion used in model for update
     *
     * @var mixed
     */
    public $scenario = 'editable';

    /**
     * name of model
     *
     * @var mixed
     */
    public $modelClass;
    /**
     * primaryKey value
     *
     * @var mixed
     */
    public $primaryKey;
    /**
     * name of attribute to be updated
     *
     * @var mixed
     */
    public $attribute;
    /**
     * model instance
     *
     * @var CActiveRecord
     */
    public $model;

    /**
     * http status code ruterned for errors
    */
    public $errorHttpCode = 400;

    /**
    * name of changed attributes. Used when saving model
    * 
    * @var mixed
    */
    protected $changedAttributes = array();
    
    /**
     * Constructor
     *
     * @param mixed $modelName
     * @return EditableBackend
     */
    public function __construct($modelClass)
    {
        if (empty($modelClass)) {
            throw new CException(Yii::t('zii', 'You should provide modelClass in constructor of TbEditableSaver.'));
        }
        $this->modelClass = ucfirst($modelClass);
    }

    /**
     * main function called to update column in database
     *
     */
    public function update()
    {
        //set params from request
        $this->primaryKey = yii::app()->request->getParam('pk');
        $this->attribute = yii::app()->request->getParam('name');
        $value = Yii::app()->request->getParam('value');

        //checking params
        if (empty($this->attribute)) {
            throw new CException(Yii::t('zii','Property "attribute" should be defined.'));
        }
        if (empty($this->primaryKey)) {
            throw new CException(Yii::t('zii','Property "primaryKey" should be defined.'));
        }

        //loading model
        $this->model = CActiveRecord::model($this->modelClass)->findByPk($this->primaryKey);
        if (!$this->model) {
            throw new CException(Yii::t('editable', 'Model {class} not found by primary key "{pk}"', array(
               '{class}'=>get_class($this->model), '{pk}'=>$this->primaryKey)));
        }
        $this->model->setScenario($this->scenario);
        
        //is attribute exists
        if (!$this->model->hasAttribute($this->attribute)) {
            throw new CException(Yii::t('editable', 'Model {class} does not have attribute "{attr}"', array(
              '{class}'=>get_class($this->model), '{attr}'=>$this->attribute)));            
        }

        //is attribute safe
        if (!$this->model->isAttributeSafe($this->attribute)) {
            throw new CException(Yii::t('zii', 'Model {class} rules do not allow to update attribute "{attr}"', array(
              '{class}'=>get_class($this->model), '{attr}'=>$this->attribute))); 
        }

        //setting new value
        $this->setAttribute($this->attribute, $value);

        //validate
        $this->model->validate(array($this->attribute));
        if ($this->model->hasErrors()) {
            $this->error($this->model->getError($this->attribute));
        }

        //save
        if ($this->beforeUpdate()) {
            //saving (only chnaged attributes)
            if ($this->model->save(false, $this->changedAttributes)) {
                $this->afterUpdate();
            } else {
                $this->error(Yii::t('zii', 'Error while saving record!'));
            }
        } else {
            $firstError = reset($this->model->getErrors());
            $this->error($firstError[0]);
        }
    }

    /**
     * This event is raised before the update is performed.
     * @param CModelEvent $event the event parameter
     */
    public function onBeforeUpdate($event)
    {
        $this->raiseEvent('onBeforeUpdate', $event);
    }

    /**
     * This event is raised after the update is performed.
     * @param CEvent $event the event parameter
     */
    public function onAfterUpdate($event)
    {
        $this->raiseEvent('onAfterUpdate', $event);
    }

    /**
     * errors  as CHttpException
     * @param $msg
     * @throws CHttpException
     */
    protected function error($msg)
    {
        throw new CHttpException($this->errorHttpCode, $msg);
    }

    /**
     * beforeUpdate
     *
     */
    protected function beforeUpdate()
    {
        $this->onBeforeUpdate(new CEvent($this));
        return !$this->model->hasErrors();
    }

    /**
     * afterUpdate
     *
     */
    protected function afterUpdate()
    {
        $this->onAfterUpdate(new CEvent($this));
    }
    
    /**
    * setting new value of attribute.
    * Attrubute name also stored in array to save only changed attributes
    * 
    * @param mixed $name
    * @param mixed $value
    */
    public function setAttribute($name, $value)
    {
         $this->model->$name = $value;
         $this->changedAttributes[] = $name;
         $this->changedAttributes = array_unique($this->changedAttributes);
    }
}
