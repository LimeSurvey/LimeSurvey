<!-- Warning if user has a token table created -->
<?php if (isset($hasTokenTable) && $hasTokenTable): ?>
    <p class='alert alert-warning'>
        <span class='fa fa-exclamation-circle'></span>
        &nbsp;
        <?php echo $plugin->gT('Please delete your token participant table to be able to use Cint Link.'); ?>
    </p>
<?php endif; ?>

<!-- Some info -->
<?php if (empty($surveyId)): ?>
    <p class='alert alert-info'>
        <span class='fa fa-info-circle'></span>
        &nbsp;
        <?php echo $plugin->gT('To order participants, please go to the survey specific CintLink view.'); ?>
    </p>
<?php elseif (!empty($survey) && $survey->active != 'Y'):  ?>
    <p class='alert alert-info'>
        <span class='fa fa-info-circle'></span>
        &nbsp;
        <?php echo $plugin->gT('Please make sure the survey is activated before placing a Cint order.'); ?>
    </p>
<?php endif; ?>

<!-- Cint widget button (not visible from global dashboard) -->
<?php if (!empty($surveyId)): ?>
    <div class='row'>

        <!-- Show Cint widget -->
        <div id='cintlink-widget-button' class='col-sm-3' style='cursor: pointer;'>
            <div
                class='panel panel-primary cintlink-shadow'
                  onclick='<?php if ($additionalLanguages === null): echo 'LS.plugin.cintlink.showWidget();'; else: echo 'LS.plugin.cintlink.showLangWizard();'; endif; ?>'
                <?php if ($hasTokenTable): ?> disabled='disabled' <?php endif; ?>
            >
                <div class='panel-heading'>
                    <h4 class='panel-title'><?php echo $plugin->gT('Choose target group'); ?></h4>
                </div>
                <div class='panel-body text-center text-success'>
                    <span class='fa-stack fa-lg'>
                        <i class='fa fa-circle fa-stack-2x text-success'></i>
                        <i class='fa fa-bars fa-stack-1x fa-inverse'></i>
                    </span>
                    <p><?php echo $plugin->gT('Add participants to your survey'); ?></p>
                </div>
            </div>
        </div>

        <div id='cintlink-login-button' class='col-sm-3' style='<?php if (!$loggedIn) echo 'cursor: pointer;'; ?>'>
                <div
                class='panel panel-primary cintlink-shadow <?php if ($loggedIn) echo 'disabled'; ?>'
                    onclick='<?php if (!$loggedIn) echo 'LS.plugin.cintlink.showLoginForm();'; ?>'
                >
                    <div class='panel-heading'>
                        <h4 class='panel-title'><?php echo $plugin->gT('Login'); ?></h4>
                    </div>
                    <div class='panel-body text-center text-success'>
                        <span class='fa-stack fa-lg'>
                            <i class='fa fa-circle fa-stack-2x text-success'></i>
                            <i class='fa fa-sign-in fa-stack-1x fa-inverse'></i>
                        </span>
                        <p><?php echo $plugin->gT('Login to limesurvey.org'); ?></p>
                    </div>
                </div>
            </div>

        <!-- Modal for Cint language wizard -->
        <div id="cint-lang-wizard" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <!-- Modal content-->
                <div class="modal-content">  <?php // JS add not.type as panel-type, e.g. panel-default, panel-danger ?>
                    <div class="modal-header panel-heading">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title"><?php echo $plugin->gT('Choose language'); ?></h4>
                    </div>
                    <div class="modal-body">
                        <p class='modal-body-text'><?php echo $plugin->gT('Pick a language that will be used as default for this order. Make sure your order\'s country have this language as an official language.'); ?></p>

                        <!-- Radio list with languages -->
                        <form class='form-horizontal'>
                            <div class='radio'>
                                <label><input type='radio' name='lang' checked='checked' value='<?php echo $survey->language; ?>' /><?php echo getLanguageNameFromCode($survey->language)[0]; ?></label>
                            </div>
                            <?php foreach ($survey->additionalLanguages as $lang): ?>
                                <div class='radio'>
                                    <label><input type='radio' name='lang' value='<?php echo $lang; ?>' /><?php echo getLanguageNameFromCode($lang)[0]; ?></label>
                                </div>
                            <?php endforeach; ?>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">&nbsp;<?php eT("Cancel"); ?></button>
                        <button type="button" class="btn btn-primary" data-dismiss="modal" onclick='LS.plugin.cintlink.langWizardOK();' >&nbsp;<?php eT("OK"); ?></button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Refresh -->
        <button id='cintlink-refresh-button' class='btn btn-default pull-right' onclick='LS.plugin.cintlink.showDashboard();'><span class='fa fa-refresh'></span>&nbsp;<?php echo $plugin->gT('Refresh'); ?></button>

  </div>
<?php endif; ?>

<h4>Orders</h4>
<div id='cintlink-gridview'>
<?php 
    $columns = array();
    $columns[] = array(
        'name' => 'url',
        'header' => 'ID',
        'value' => '$data->shortId'
    );
    $columns[] = array(
        'name' => 'created',
        'header' => $plugin->gT('Created'),
        'value' => '$data->formattedCreatedDate'
    );

    // Only needed on global dashboard
    if (empty($surveyId))
    {
        $columns[] = array(
            'name' => 'sid',
            'header' => $plugin->gT('Survey ID'),
            'value' => '$data->surveyIdLink',
            'type' => 'raw'
        );
    }

    $columns[] = array(
        'name' => 'ordered_by',
        'header' => $plugin->gT('Ordered by'),
        'value' => '$data->user->full_name'
    );
    $columns[] = array(
        'name' => 'country',
        'header' => $plugin->gT('Country'),
        'value' => '$data->country'
    );
    $columns[] = array(
        'name' => 'target-group',
        'header' => $plugin->gT('Target group'),
        'value' => '(strlen($data->targetGroup) > 50
            ? CHtml::tag("span", array("title" => $data->targetGroup, "data-toggle" => "tooltip"), ellipsize($data->targetGroup, 50))
            : $data->targetGroup)',
        'type' => 'raw'
    );
    $columns[] = array(
        'name' => 'age',
        'header' => $plugin->gT('Age'),
        'value' => '$data->age',
        'htmlOptions' => array(
            'class' => 'cint-age'
        )
    );
    $columns[] = array(
        'name' => 'price',
        'header' => $plugin->gT('Price'),
        'value' => '$data->price',
        'type' => 'raw',
        'htmlOptions' => array(
            'class' => 'cint-price'
        )
    );
    $columns[] = array(
        'name' => 'completes',
        'header' => $plugin->gT('Completes'),
        'value' => '$data->completes',
    );
    $columns[] = array(
        'name' => '__completedCheck',
        'header' => '',
        'value' => '$data->completedCheck',
        'type' => 'raw'
    );
    $columns[] = array(
        'name' => 'status',
        'header' => $plugin->gT('Status'),
        'value' => '$data->styledStatus',
        'type' => 'raw',
        'id' => 'cintlink-status-column',
        'htmlOptions' => array(
        )
    );
    $columns[] = array(
        'name' => 'buttons',
        'header' => '',
        'value' => '$data->buttons',
        'type' => 'raw',
        'htmlOptions' => array(
            'class' => 'cint-buttons'
        )
    );

    $widget = $this->widget('bootstrap.widgets.TbGridView', array(
        'dataProvider' => $model->search($surveyId),
        'id' => 'url',
        'itemsCssClass' =>'table-striped',
        'emptyText' => $plugin->gT('No order made yet'),
        'afterAjaxUpdate' => 'doToolTip',
        'ajaxUpdate' => true,
        'columns' => $columns
    ));
?>
</div>

<!-- Hack to not publish jQuery twice -->
<?php $plugin->renderClientScripts(); ?>
