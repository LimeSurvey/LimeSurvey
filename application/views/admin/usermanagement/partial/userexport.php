<?php
/**
 * Subview: Modal for exporting Users in csv and json format 
 * 
 * @package UserManagement
 * @author Eddy lackmann <eddy.lackmann@limesurvey.org>
 * @license GPL3.0
 */
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title"><?=gT('Export users')?></h4>
</div>
<div class="modal-body">
    <div class="container-fluid">
        <div class="row">
            <pre>
                <?php 
                    echo $json;
                ?>
            </pre>
        </div>
            
        <div class="row ls-space margin top-5">
            <hr class="ls-space margin top-5 bottom-10"/>
        </div>
        <div class="row ls-space margin top-35">
            <?= CHtml::link(gT("Export (CSV)"),App()->createUrl("admin/usermanagement/sa/exportUser",["outputFormat"=>"csv"]),
                    array(
                         'class'=>'btn btn-success col-sm-3 col-xs-5 col-sm-offset-2 col-xs-offset-1'
                    ));
            ?>
            <?= CHtml::link(gT("Export (Json)"),App()->createUrl("admin/usermanagement/sa/exportUser",["outputFormat"=>"json"]),
                    array(
                      'class'=>'btn btn-success col-sm-3 col-xs-5 col-sm-offset-2 col-xs-offset-1'
                    ));
            ?>

        </div>
        
    </div>
</div>

