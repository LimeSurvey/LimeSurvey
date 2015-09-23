<?php
namespace ls\controllers\surveys;


class Index extends \Action
{
    public function run() {
        $this->layout = 'main';
        $filter = new \ls\models\filter\Survey();
        $filter->setAttributes(App()->request->getParam(\CHtml::modelName($filter)));
        $surveys = \Survey::model()->accessible();
        $surveys->getDbCriteria()->mergeWith($filter->search());
        $surveys->with('languagesettings');
        $dataProvider = new \CActiveDataProvider($surveys);
        $dataProvider->pagination->pageSize = 100;
        $this->render('index', ['surveys' => $dataProvider, 'filter' => $filter]);
    }
}