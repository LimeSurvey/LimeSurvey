<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <div class="row">
        <div class="col-lg-12 content-right">
            <?php $this->renderPartial('/admin/survey/breadcrumb', array('oSurvey'=>$oSurvey, 'active'=> gT("Survey quotas"))); ?>
            <h3>
                <?php eT("Survey quotas");?>
            </h3>

            <?php if( isset($sShowError) ):?>
                <div class="alert alert-warning alert-dismissible" role="alert">
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                  <strong><?php eT("Quota could not be added!", 'js'); ?></strong><br/> <?php eT("It is missing a quota message for the following languages:", 'js'); ?><br/><?php echo $sShowError; ?>
                </div>
            <?php endif; ?>

            <table id="quotalist" class="quotalist table-striped">
            <thead>
                <tr>
                    <th style="width:20%"><?php eT("Quota name");?></th>
                    <th style="width:20%"><?php eT("Status");?></th>
                    <th style="width:30%"><?php eT("Quota action");?></th>
                    <th style="width:5%; padding-right: 1em;"><?php eT("Completed");?></th>
                    <th style="width:5%"><?php eT("Limit");?></th>
                    <th style="width:20%"><?php eT("Action");?></th>
                </tr>
            </thead>

            <tfoot>
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td style="padding: 3px;"><input type="button" class="btn btn-default" value="<?php eT("Quick CSV report");?>" onClick="window.open('<?php echo $this->createUrl("admin/quotas/sa/index/surveyid/$surveyid/quickreport/y") ?>', '_top')" /></td>
                </tr>
                </tfoot>
                <tbody>
