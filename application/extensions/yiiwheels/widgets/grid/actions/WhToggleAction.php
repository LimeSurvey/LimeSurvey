<?php
/**
 * WhToggleAction action class
 *
 * Works in conjunction with WhToggleColumn widget in order to ease the task of the developer to update the the attribute
 * displayed on the grid. Just attach to the controller you wish to make the calls to.
 *
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @copyright Copyright &copy; 2amigos.us 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package YiiWheels.widgets.toggle
 */

class WhToggleAction extends CAction
{
    /**
     * @var string the name of the model we are going to toggle values to
     */
    public $modelName;

    /**
     * @var bool whether to throw an exception if we cannot find a model requested by the id
     */
    public $exceptionOnNullModel = true;

    /**
     * @var array additional criteria to use to get the model
     */
    public $additionalCriteriaOnLoadModel = array();

    /**
     * @var mixed the route to redirect the call after updating attribute
     */
    public $redirectRoute;

    /**
     * @var int|string the value to update the model to [yes|no] standard toggle options, but you can toggle any value.
     */
    public $yesValue = 1;

    /**
     * @var int|string the value to update the model to [yes|no]
     */
    public $noValue = 0;

    /**
     * @var mixed the response to return to an AJAX call when the attribute was successfully saved.
     */
    public $ajaxResponseOnSuccess = 1;

    /**
     * @var mixed the response to return to an AJAX call when failed to update the attribute.
     */
    public $ajaxResponseOnFailed = 0;


    /**
     * Widgets run function
     * @param integer $id
     * @param string $attribute
     * @throws CHttpException
     */
    public function run($id, $attribute)
    {
        if (Yii::app()->getRequest()->isPostRequest) {
            $model             = $this->loadModel($id);
            $model->$attribute = ($model->$attribute == $this->noValue) ? $this->yesValue : $this->noValue;
            $success           = $model->save(false, array($attribute));

            if (Yii::app()->getRequest()->isAjaxRequest) {
                echo $success ? $this->ajaxResponseOnSuccess : $this->ajaxResponseOnFailed;
                exit(0);
            }
            if ($this->redirectRoute !== null) {
                $this->getController()->redirect($this->redirectRoute);
            }
        } else {
            throw new CHttpException(Yii::t('zii', 'Invalid request'));
        }
    }

    /**
     * Loads the requested data model.
     * @param integer $id the model ID
     * @return CActiveRecord the model instance.
     * @throws CHttpException if the model cannot be found
     */
    protected function loadModel($id)
    {
        if (empty($this->additionalCriteriaOnLoadModel)) {
            $model = CActiveRecord::model($this->modelName)->findByPk($id);
        } else {
            $finder = CActiveRecord::model($this->modelName);
            $c      = new CDbCriteria($this->additionalCriteriaOnLoadModel);
            $c->mergeWith(
                array(
                    'condition' => $finder->tableSchema->primaryKey . '=:id',
                    'params'    => array(':id' => $id),
                )
            );
            $model = $finder->find($c);
        }
        if (isset($model)) {
            return $model;
        }
        if ($this->additionalCriteriaOnLoadModel) {
            throw new CHttpException(404, 'Unable to find the requested object.');
        }
    }
}