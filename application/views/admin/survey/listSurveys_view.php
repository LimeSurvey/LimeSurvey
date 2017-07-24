<?php
/**
* This file render the list of surveys
* It use the Survey model search method to build the data provider.
*
* @var $model  obj    the QuestionGroup model
*/
?>
<?php $pageSize=Yii::app()->user->getState('pageSize',Yii::app()->params['defaultPageSize']);?>
<div class="col-sm-12 list-surveys">
    <div class="pagetitle h3"><?php eT('Survey list'); ?></div>

    <!-- Survey List widget -->
    <?php $this->widget('ext.admin.survey.ListSurveysWidget.ListSurveysWidget', array(
                'pageSize' => Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']),
                'model' => $model
        ));
    ?>


    <h3><?php eT('Surveys Groups:'); ?></h3>

    <div class="row">
        <div class="col-sm-12 content-right">
            <?php
            $this->widget('bootstrap.widgets.TbGridView', array(
                'dataProvider' => $groupModel->search(),
                'columns' => array(

                    array(
                        'id'=>'gsid',
                        'class'=>'CCheckBoxColumn',
                        'selectableRows' => '100',
                    ),

                    array(
                        'header' => gT('Survey Group ID'),
                        'name' => 'gsid',
                        'type' => 'raw',
                        'value'=>'CHtml::link($data->gsid, Yii::app()->createUrl("admin/surveysgroups/sa/update/",array("id"=>$data->gsid)))',
                        'headerHtmlOptions'=>array('class' => 'hidden-xs'),
                        'htmlOptions' => array('class' => 'hidden-xs has-link'),
                    ),

                    array(
                        'header' => gT('Name'),
                        'name' => 'name',
                        'type' => 'raw',
                        'value'=>'CHtml::link($data->name, Yii::app()->createUrl("admin/surveysgroups/sa/update/",array("id"=>$data->gsid)))',
                        'headerHtmlOptions'=>array('class' => 'hidden-xs'),
                        'htmlOptions' => array('class' => 'has-link'),
                    ),

                    array(
                        'header' => gT('Description'),
                        'name' => 'description',
                        'type' => 'raw',
                        'value'=>'CHtml::link($data->description, Yii::app()->createUrl("admin/surveysgroups/sa/update/",array("id"=>$data->gsid)))',
                        'headerHtmlOptions'=>array('class' => 'hidden-xs'),
                        'htmlOptions' => array('class' => 'hidden-xs has-link'),
                    ),

                    array(
                        'header' => gT('Parent Group'),
                        'name' => 'parent',
                        'type' => 'raw',
                        'value'=>'CHtml::link( $data->parentTitle, Yii::app()->createUrl("admin/surveysgroups/sa/update/",array("id"=>$data->gsid)))',
                        'headerHtmlOptions'=>array('class' => 'hidden-xs'),
                        'htmlOptions' => array('class' => 'hidden-xs has-link'),
                    ),

                    array(
                        'header' => gT('Owner'),
                        'name' => 'owner',
                        'value'=>'$data->owner->users_name',
                        'headerHtmlOptions'=>array('class' => 'hidden-xs'),
                        'htmlOptions' => array('class' => 'hidden-xs has-link'),
                    ),

                    array(
                        'header' => gT('Order'),
                        'name' => 'order',
                        'type' => 'raw',
                        'value'=>'CHtml::link($data->order, Yii::app()->createUrl("admin/surveysgroups/sa/update/",array("id"=>$data->gsid)))',
                        'headerHtmlOptions'=>array('class' => 'hidden-xs'),
                        'htmlOptions' => array('class' => 'hidden-xs has-link'),
                    ),


                    array(
                        'header' => gT('Order'),
                        'name' => 'order',
                        'type' => 'raw',
                        'value'=> '$data->buttons',
                        'headerHtmlOptions'=>array('class' => 'hidden-xs'),
                        'htmlOptions' => array('class' => 'hidden-xs has-link'),
                    ),

                ),

            ));
            ?>
        </div>
    </div>

</div>
