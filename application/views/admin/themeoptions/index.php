<?php
/* @var $this AdminController */
/* @var $dataProvider CActiveDataProvider */

// TODO: rename to template_list.php

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('templateOptions');

?>

<?php
    //$bFromSurveyGroup = (isset($bFromSurveyGroup))?$bFromSurveyGroup:false;
    $bFromSurveyGroup = ($model->scenario == 'surveygroup')?true:false;
?>

<div class="col-lg-12 list-surveys">

    <?php
        if (!$bFromSurveyGroup){
            $this->renderPartial('super/fullpagebar_view', array(
            'fullpagebar' => array(
                'returnbutton'=>array(
                    'url'=>'index',
                    'text'=>gT('Close'),
                    ),
                )
            ));
            echo '<h3>'.gT('Installed templates:').'</h3>';
        }


    ?>

    <div class="row">
        <div class="col-sm-12 content-right">

            <?php $this->widget('bootstrap.widgets.TbGridView', array(
                'dataProvider' => $model->search(),
                'columns' => array(
                    array(
                        'header' => gT('Preview'),
                        'name' => 'preview',
                        'value'=> '$data->preview',
                        'type'=>'raw',
                        'htmlOptions' => array('class' => 'col-md-1'),
                    ),

                    array(
                        'header' => gT('Name'),
                        'name' => 'template_name',
                        'value'=>'$data->template_name',
                        'htmlOptions' => array('class' => 'col-md-2'),
                    ),

                    array(
                        'header' => gT('Description'),
                        'name' => 'template_name',
                        'value'=>'$data->template->description',
                        'htmlOptions' => array('class' => 'col-md-3'),
                        'type'=>'raw',
                    ),

                    array(
                        'header' => gT('Type'),
                        'name' => 'templates_type',
                        'value'=>'$data->typeIcon',
                        'type' => 'raw',
                        'htmlOptions' => array('class' => 'col-md-2'),
                    ),

                    array(
                        'header' => gT('Extends'),
                        'name' => 'templates_extends',
                        'value'=>'$data->template->extends',
                        'htmlOptions' => array('class' => 'col-md-2'),
                    ),

                    array(
                        'header' => '',
                        'name' => 'actions',
                        'value'=>'$data->buttons',
                        'type'=>'raw',
                        'htmlOptions' => array('class' => 'col-md-1'),
                    ),

                )));
            ?>

        </div>
    </div>

    <?php if (count($model->templatesWithNoDb) > 0 && !$bFromSurveyGroup):?>
        <h3><?php eT('Available templates:'); ?></h3>
        <div class="row">
            <div class="col-sm-12 content-right">

                <div id="templates_no_db" class="grid-view">
                    <table class="items table">
                        <thead>
                            <tr>
                                <th><?php eT('Preview'); ?></th><th><?php eT('Folder'); ?></th><th><?php eT('Description'); ?></th><th><?php eT('Type'); ?></th><th><?php eT('Extends'); ?></th><th></th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($model->templatesWithNoDb as $oTemplate):?>
                                <?php // echo $oTemplate; ?>
                                <tr class="odd">
                                    <td class="col-md-1"><?php echo $oTemplate->preview; ?></td>
                                    <td class="col-md-2"><?php echo $oTemplate->sTemplateName; ?></td>
                                    <td class="col-md-3"><?php echo $oTemplate->config->metadatas->description; ?></td>
                                    <td class="col-md-2"><?php eT('XML template');?></td>
                                    <td class="col-md-2"><?php echo $oTemplate->config->metadatas->extends; ?></td>
                                    <td class="col-md-1"><?php echo $oTemplate->buttons; ?></td>
                                </tr>
                            <?php endforeach;?>
                        </tbody>
                    </table>

                </div>

            </div>
        </div>
    <?php endif;?>
</div>
