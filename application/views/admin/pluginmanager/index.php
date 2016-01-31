<?php

/**
 * Index view for plugin manager
 *
 * @since 2015-10-02
 * @author Olle Haerstedt <olle.haerstedt@limesurvey.org>
 */

?>

<h3 class="pagetitle"><?php eT('Plugin manager'); ?></h3>
<div style="width: 75%; margin: auto;">
<?php
    /* @var $this ConfigController */
    /* @var $dataProvider CActiveDataProvider */

    $dataProvider = new CArrayDataProvider($data);

    $gridColumns = array(
        array(// display the status
            'class' => 'CDataColumn',
            'header' => gT('Status'),
            'type' => 'html',
            //'value' => function($data) { return ($data['active'] == 1 ? CHtml::image(App()->getConfig('adminimageurl') . 'active.png', gT('Active'), array('width' => 32, 'height' => 32)) : CHtml::image(App()->getConfig('adminimageurl') . 'inactive.png', gT('Inactive'), array('width' => 32, 'height' => 32))); }
            'value' => function($data)
            {
                if ($data['active'] == 1)
                {
                    return "<span class='fa fa-circle'></span>";
                }
                else
                {
                    return "<span class='fa fa-circle-thin'></span>";
                }
            }
        ),
        array(// display the activation link
            'class' => 'CDataColumn',
            'type' => 'raw',
            'header' => gT('Action'),
            'value' => function($data) {
                if ($data['active'] == 0)
                {
                    $output = "<a href='" . $this->createUrl('/admin/pluginmanager/sa/activate', array('id' => $data['id'])) . "' class='btn btn-default btn-xs btntooltip'><span class='fa fa-power-off'>&nbsp;</span>".gT('Activate')."</a>";
                } else {
                    $output = "<a href='" . $this->createUrl('/admin/pluginmanager/sa/deactivate', array('id' => $data['id'])) . "'class='btn btn-warning btn-xs'><span class='fa fa-power-off'>&nbsp;</span>".gT('Deactivate')."</a>";
                }
                if(count($data['settings'])>0)
                {
                    $output .= "&nbsp;<a href='" . $this->createUrl('/admin/pluginmanager/sa/configure', array('id' => $data['id'])) . "' class='btn btn-default btn-xs'><span class='icon-edit'>&nbsp;</span>" . gT('Configure') . "</a>";
                }
                return $output;
            }
        ),
        array(// display the 'name' attribute
            'class' => 'CDataColumn',
            'header' => gT('Plugin'),
            'name' => 'name'
        ),
        array(// display the 'description' attribute
            'class' => 'CDataColumn',
            'header' => gT('Description'),
            'name' => 'description'
        ),
    );

        /*
            array(            // display a column with "view", "update" and "delete" buttons
            'class' => 'CallbackColumn',
            'label' => function($data) { return ($data->active == 1) ? "deactivate": "activate"; },
            'url' => function($data) { return array("/plugins/activate", "id"=>$data["id"]); }
        )
    );
        */

    $this->widget('bootstrap.widgets.TbGridView', array(
        'dataProvider'=>$dataProvider,
        'columns'=>$gridColumns,
        'rowCssClassExpression'=> function ($data, $row) { return ($row % 2 ? 'even' : 'odd') . ' ' . ($data['new']==1 ? "new" : "old"); },
        'itemsCssClass' => 'items table-condensed table-bordered'
    ));
?>
</div>
