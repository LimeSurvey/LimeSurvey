<?php
/**
 * TbSortableAction CAction Component
 *
 * It is a component that works in conjunction of TbExtendedGridView widget with sortableRows true. Just attach to the controller you wish to
 * make the calls to.
 *
 * @author: ruslan fadeev <fadeevr@gmail.com>
 * Date: 10/23/12
 * Time: 4:02 PM
 */
class TbSortableAction extends CAction
{
	/**
	 * @var string the name of the model we are going to toggle values to
	 */
	public $modelName;

	/**
	 * Widgets run function
	 * @throws CHttpException
	 */
    public function run()
    {
        if (Yii::app()->request->isPostRequest && Yii::app()->request->isAjaxRequest && isset($_POST['sortOrder'])) {
            $sortableAttribute = Yii::app()->request->getQuery('sortableAttribute');

            /** @var $model CActiveRecord */
            $model = new $this->modelName;
            if (!$model->hasAttribute($sortableAttribute)) {
                throw new CHttpException(500, Yii::t('yii', '{attribute} "{value}" is invalid.', array('{attribute}' => 'sortableAttribute', '{value}' => $sortableAttribute)));
            }

            $query = "UPDATE {$model->tableName()} SET {$sortableAttribute} = CASE ";
            $ids = array();
            foreach ($_POST['sortOrder'] as $id => $sort_order) {
                $id = intval($id);
                $sort_order = intval($sort_order);
                $query .= "WHEN {$model->tableSchema->primaryKey}={$id} THEN {$sort_order} ";
                $ids[] = $id;
            }
            $query .= "END WHERE {$model->tableSchema->primaryKey} IN (" . implode(',', $ids) . ');';
            Yii::app()->db->createCommand($query)->execute();
        } else {
            throw new CHttpException(500, Yii::t('yii', 'Your request is invalid.'));
        }
    }
}
