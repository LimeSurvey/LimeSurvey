<?php
/* @var $this AdminController */
/* @var $dataProvider CActiveDataProvider */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('usersIndex');

?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <button id="add_user_admin" data-target="#adduser-modal" data-toggle="modal" title="<?php eT('Add a new survey administrator'); ?>" class="btn btn-default"><span class="icon-add text-success"></span> <?php eT("Add user");?></button>
        </div>
    </div>
<div class="pagetitle h3"><?php eT("User control");?></div>
    <!-- Search Box -->
    <div class="row">
        <div class="pull-right">
            <div class="form text-right">
                <!-- Begin Form -->
                <?php $form  =  $this->beginWidget('CActiveForm', array(
                    'action' => Yii::app()->createUrl($formUrl),
                    'method' => 'get',
                    'htmlOptions'=>array(
                        'class'=>'form-inline',
                    ),
                )); ?>

                <!-- search input -->
                <div class="form-group">
                    <?php echo $form->label($model, 'searched_value', array('label'=>gT('Search:'),'class'=>'control-label')); ?>
                    <?php echo $form->textField($model, 'searched_value', array('class'=>'form-control')); ?>
                </div>

                <?php echo CHtml::submitButton(gT('Search','unescaped'), array('class'=>'btn btn-success')); ?>
                <a href="<?php echo Yii::app()->createUrl('admin/user/sa/index');?>" class="btn btn-warning"><?php eT('Reset');?></a>

                <?php $this->endWidget(); ?>
            </div>
        </div>
    </div>

    <div class="row" style="margin-bottom: 100px">
        <div class="container-fluid">
            <?php
            $this->widget('bootstrap.widgets.TbGridView', array(
                'id' => 'all_users',
                'itemsCssClass' => 'table table-striped items',
                'dataProvider' => $model->search(),
                'columns' => $model->colums,
                'afterAjaxUpdate' => 'bindButtons',
                'summaryText'   => gT('Displaying {start}-{end} of {count} result(s).').' '. sprintf(gT('%s rows per page'),
                            CHtml::dropDownList(
                                'pageSize',
                                $pageSize,
                                Yii::app()->params['pageSizeOptions'],
                                array('class'=>'changePageSize form-control', 'style'=>'display: inline; width: auto'))
                            ),
                    ));

                ?>
            </div>

            <!-- To update rows per page via ajax -->
            <script type="text/javascript">
                jQuery(function($) {
                    jQuery(document).on("change", '#pageSize', function(){
                        $.fn.yiiGridView.update('all_users',{ data:{ pageSize: $(this).val() }});
                    });
                });
            </script>
    </div>
</div>
<div id='adduser-modal' class="modal fade " tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel"><?php eT("Add a new survey administrator") ?></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                 <?php echo CHtml::form(array('admin/user/sa/adduser'), 'post', array('class'=>''));?>
                    <?php if (App()->getPluginManager()->isPluginActive('AuthLDAP')) {
                        echo "<div class=\"form-group\">";
                          echo "<label  class='control-label'>";
                            eT("Central database");
                          echo "</label>";
                          echo "<div class=''>";
                            echo CHtml::dropDownList('user_type',
                                'DB',
                                array(
                                'DB' => gT("Internal database authentication",'unescaped'),
                                'LDAP' => gT("LDAP authentication",'unescaped')
                                ),
                                array(
                                    'class' => ""
                                )
                            );
                          echo "</div>";
                        echo "</div>";
                      } else {
                          echo "<input type='hidden' id='user_type' name='user_type' value='DB'/>";
                      }
                    ?>

                    <div class="form-group">
                        <label for="new_user" class="control-label"><?php eT("Username:");?></label>
                        <div class="">
                            <input type='text' class="text input-sm form-control" id='new_user' name='new_user' required />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="new_email" class="control-label" ><?php eT("Email:");?></label>
                        <div class="">
                            <input type='email' class="text input-sm form-control" id='new_email' name='new_email' required />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="new_full_name" class="control-label "><?php eT("Full name:");?></label>
                        <div class="">
                            <input type='text' class="text input-sm form-control" id='new_full_name' name='new_full_name' required />
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12 text-right">
                            <?php eT("The password will be generated and sent by email.") ?>
                        </div>
                    </div>
                    <div class="col-md-12">&nbsp;</div>
                    <div class="col-md-4 col-md-offset-8">
                        <input type='submit' id='add_user_btn' class="btn btn-primary btn-block" value='<?php eT("Save");?>' />
                        <input type='hidden' name='action' value='adduser' />
                    </div>
                </div>
                </form>
            </div>
        </div>
        </div>
    </div>
</div>
