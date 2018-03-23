<?php

/**
 * Index view for plugin manager
 * @var $this AdminController
 *
 * @since 2015-10-02
 * @author Olle Haerstedt
 */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('pluginManager');

?>
<?php $pageSize = intval(Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize'])); ?>

<div class="pagetitle h3"><?php eT('Plugin manager'); ?></div>
<div style="width: 75%; margin: auto;">
    <div id="ls_action_changestate_form_container">
    <?php
    echo CHtml::beginForm(Yii::app()->createUrl('/admin/pluginmanager/sa/changestate'),'POST', array('id' => 'ls_action_changestate_form'));
    /* @var $this ConfigController */
    /* @var $dataProvider CActiveDataProvider */

    $sort = new CSort();
    $sort->attributes = array(
        'name'=>array(
            'asc'=> 'name',
            'desc'=> 'name desc',
        ),
        'description'=>array(
            'asc'=> 'description',
            'desc'=> 'description desc',
        ),
        'status'=>array(
            'asc'=> 'active',
            'desc'=> 'active desc',
            'default'=> 'desc',
        ),
    );
    $sort->defaultOrder = array(
        'name'=>CSort::SORT_ASC,
    );

    $providerOptions = array(
        'pagination'=>array(
            'pageSize'=>$pageSize,
        ),
        'sort'=>$sort,
        'caseSensitiveSort'=> false,
    );

    $dataProvider = new CArrayDataProvider($data, $providerOptions);

    $gridColumns = array(
        array(// display the status
            'header' => gT('Status'),
            'type' => 'html',
            'name' => 'status',
            //'rowHtmlOptionsExpression' => 'array("data-id" => $data->id)',
            //'value' => function($data) { return ($data['active'] == 1 ? CHtml::image(App()->getConfig('adminimageurl') . 'active.png', gT('Active'), array('width' => 32, 'height' => 32)) : CHtml::image(App()->getConfig('adminimageurl') . 'inactive.png', gT('Inactive'), array('width' => 32, 'height' => 32))); }
            'value' => function ($data) {
                if ($data['load_error'] == 1) {
                    return sprintf(
                        "<span data-toggle='tooltip' title='%s' class='btntooltip fa fa-times text-warning'></span>",
                        gT('Plugin load error')
                    );
                } elseif ($data['active'] == 1) {
                    return "<span class='fa fa-circle'></span>";
                } else {
                    return "<span class='fa fa-circle-thin'></span>";
                }
            }
        ),
        array(// display the 'name' attribute
            'header' => gT('Plugin'),
            'name' => 'name',
            'type' => 'html',
            'value' => function ($data) {
                $url = Yii::app()->getController()->createUrl(
                    '/admin/pluginmanager',
                    [
                        'sa' => 'configure',
                        'id' => $data['id']
                    ]
                );
                if ($data['load_error'] == 0) {
                    return sprintf(
                        '<a href="%s">%s</a>',
                        $url,
                        $data['name']
                    );
                } else {
                    return $data['name'];
                }
            }
        ),
        array(// display the 'description' attribute
            'header' => gT('Description'),
            'name' => 'description'
        ),
        array(// display the activation link
            'type' => 'html',
            'header' => gT('Action'),
            'name' => 'action',
            'htmlOptions' => array(
                'style' => 'white-space: nowrap;',
            ),
            'value' => function($data) {

                $output='';
                if(Permission::model()->hasGlobalPermission('settings','update'))
                {
                    if ($data['load_error'] == 1) {
                        $reloadUrl = Yii::app()->createUrl(
                            'admin/pluginmanager',
                            [
                                'sa' => 'resetLoadError',
                                'pluginId' => $data['id']
                            ]
                        );
                        $output = "<a href='" . $reloadUrl . "' data-toggle='tooltip' title='" . gT('Attempt plugin reload') ."' class='btn btn-default btn-xs btntooltip'><span class='fa fa-refresh'></span></a>";
                    } elseif ($data['active'] == 0)
                    {
                        $output = "<a data-toggle='tooltip' title='" . gT('Activate'). "' href='#activate' data-action='activate' data-id='".$data['id']."' class='ls_action_changestate btn btn-default btn-xs btntooltip'>"
                            . "<span class='fa fa-power-off'></span>"
                        ."</a>";
                    } else {
                        $output = "<a data-toggle='tooltip' title='" . gT('Deactivate') . "' href='#deactivate' data-action='deactivate' data-id='".$data['id']."' class='ls_action_changestate btn btn-warning btn-xs btntooltip'>"
                            . "<span class='fa fa-power-off'></span>"
                        ."</a>";
                    }
                }
                if(count($data['settings'])>0)
                {
                    $output .= "&nbsp;<a href='" . Yii::app()->createUrl('/admin/pluginmanager/sa/configure', array('id' => $data['id'])) . "' class='btn btn-default btn-xs'><span class='icon-edit'>&nbsp;</span>" . gT('Configure') . "</a>";
                }
                return $output;
            }
        ),
    );

    $this->widget('bootstrap.widgets.TbGridView', array(
        'dataProvider'=>$dataProvider,
        'id' => 'plugins-grid',
        'summaryText'=>gT('Displaying {start}-{end} of {count} result(s).') .' '.sprintf(gT('%s rows per page'),
            CHtml::dropDownList(
                'pageSize',
                $pageSize,
                Yii::app()->params['pageSizeOptions'],
                array('class'=>'changePageSize form-control', 'style'=>'display: inline; width: auto'))),
        'columns'=>$gridColumns,
        'rowHtmlOptionsExpression' => 'array("data-id" => $data["id"])',
        ));
    ?>
</div>
<!-- 508 -->
<a name="activate"></a>
<a name="deactivate"></a>

<input id="ls_action_changestate_type" type="hidden" name="type" value="" />
<input id="ls_action_changestate_id" type="hidden" name="id" value="" />
<?php echo CHtml::endForm(); ?>
<script type="text/javascript">
    var bindActionButtons = function(){
        $('#ls_action_changestate_form').on('click','.ls_action_changestate', function(e){
            e.preventDefault();
            //get the values of the action
            $('#ls_action_changestate_type').val($(this).attr('href').split('#').pop());
            $('#ls_action_changestate_id').val($(this).closest('tr').data('id'));
            //get the form data and create a shadow form
            //The shadow form is necessary due to a bug/functionality in jQuery to update only the shadowDom values of input elements.
            //Therefore we need do create a shadowform which is submitted instead.
            var formData = $('#ls_action_changestate_form').serializeArray();
            var shadowForm = $('<form method="POST" action="'+$('#ls_action_changestate_form').attr('action')+'"></form>');
            for(var i in formData){
                shadowForm.append('<input name="'+formData[i]['name']+'" value="'+formData[i]['value']+'" />');
            }
            //Add the shadow form to the body to make it compatible with firefox and older IE browsers
            shadowForm.css({width: '1px', height: '1px', 'overflow': 'hidden'}).appendTo('body').submit();
        });
    };
    jQuery(function($) {
        // To update rows per page via ajax
        $(document).on("change", '#pageSize', function() {
            $.fn.yiiGridView.update('plugins-grid',{ data:{ pageSize: $(this).val() }});
        });
        bindActionButtons();
    });
</script>
