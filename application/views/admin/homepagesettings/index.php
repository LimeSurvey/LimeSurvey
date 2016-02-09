<?php
/* @var $this homepagesettings  */
/* @var $dataProvider CActiveDataProvider */

?>
<div class="col-lg-12 list-surveys">
    <h3><?php eT('Home Page settings'); ?></h3>

    <div class="row">
        <label class="col-sm-2 control-label"><?php eT("Display logo: ");?></label>
        <div class="col-sm-8">
            <?php $this->widget('yiiwheels.widgets.switch.WhSwitch', array('name' => 'displaylogoswitch'));?>
        </div>
        <br/><br/><br/>
    </div>

<!--
    <div class="row">
        <label class="col-sm-2 control-label" for="boxbyrow"><?php eT("Number of boxes by row: ");?></label>
        <div class="col-sm-1">
            <input type="text" class="form-control" value="3" name="boxbyrow"/>
        </div>
        <br/><br/><br/>
    </div>
-->

    <!-- Grid -->
    <div class="row">
        <div class="col-sm-12 content-right">
            <?php $this->widget('bootstrap.widgets.TbGridView', array(
                'dataProvider'=>$dataProvider,
                'columns' => array(
                    array(
                        'header' => gT('Position'),
                        'name' => 'position',
                        'value'=>'$data->position',
                        'htmlOptions' => array('class' => 'col-md-1'),
                    ),
                    array(
                        'header' => gT('Title'),
                        'name' => 'title',
                        'value'=>'$data->title',
                        'htmlOptions' => array('class' => 'col-md-1'),
                    ),
                    array(
                        'header' => gT('Icon'),
                        'name' => 'icon',
                        'value'=>'$data->spanicon',
                        'type'=>'raw',
                        'htmlOptions' => array('class' => 'col-md-1'),
                    ),
                    array(
                        'header' => gT('Description'),
                        'name' => 'desc',
                        'value'=>'$data->desc',
                        'htmlOptions' => array('class' => 'col-md-1'),
                    ),
                    array(
                        'header' => gT('Pointed url'),
                        'name' => 'url',
                        'value'=>'$data->url',
                        'htmlOptions' => array('class' => 'col-md-1'),
                    ),
                    array(
                        'header' => gT('User group'),
                        'name' => 'url',
                        'value'=>'$data->usergroupname',
                        'htmlOptions' => array('class' => 'col-md-1'),
                    ),
                    array(
                        'header' => '',
                        'name' => 'actions',
                        'value'=>'$data->buttons',
                        'type'=>'raw',
                        'htmlOptions' => array('class' => 'col-md-1'),
                    ),                    
                ),
            ));
            ?>
        </div>
    </div>
</div>
