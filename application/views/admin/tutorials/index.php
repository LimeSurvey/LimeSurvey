<?php
/* @var $this AdminController */
/* @var $dataProvider CActiveDataProvider */

// $this->breadcrumbs=array(
// 	'Surveymenus',
// );

// $this->menu=array(
// 	array('label'=>'Create Surveymenu', 'url'=>array('create')),
// 	array('label'=>'Manage Surveymenu', 'url'=>array('admin')),
// );
//
$pageSize=Yii::app()->user->getState('pageSize',Yii::app()->params['defaultPageSize']);

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('tutorials');

?>
<div class="ls-flex-column ls-space padding left-35 right-35">
    <div class="col-12 h1 pagetitle">
        <?php eT('Tutorials')?>
    </div>
    <div class="col-12 jumbotron well">
        <h3><?=gT("We will soon add the functionality to create your own tutorials and download them from our store.")?></h3>
    </div>
    <div class="col-12 ls-space margin top-15">
        <div class="col-12 ls-flex-item">
            <?php $this->widget('application.extensions.admin.grid.CLSGridView', [
                'dataProvider'             => $model->search(),
                // Number of row per page selection
                'id'                       => 'tutorial-grid',
                'columns'                  => $model->getColumns(),
                'filter'                   => $model,
                'emptyText'                => gT('No customizable entries found.'),
                'summaryText'              => gT('Displaying {start}-{end} of {count} result(s).') . ' ' . sprintf(gT('%s rows per page'),
                        CHtml::dropDownList(
                            'pageSize',
                            $pageSize,
                            Yii::app()->params['pageSizeOptions'],
                            ['class' => 'changePageSize form-select', 'style' => 'display: inline; width: auto']
                        )
                    ),
                'rowHtmlOptionsExpression' => '["data-tutorial-id" => $data->tid]',
                'htmlOptions'              => ['class' => 'table-responsive grid-view-ls'],
                'ajaxType'                 => 'POST',
                'ajaxUpdate'               => 'tutorial-grid',
                'lsAfterAjaxUpdate'        => ['bindAction'],
            ]);
            ?>
        </div>
    </div>
</div>
