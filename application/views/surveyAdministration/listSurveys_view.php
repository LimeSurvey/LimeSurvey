<?php
/**
* This file render the list of surveys
* It use the Survey model search method to build the data provider.
*
* @var $model  Survey
*/

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('listSurveys');

?>
<link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
<div class="ls-space list-surveys">
    <ul class="nav nav-tabs" id="surveysystem" role="tablist">
        <li class="nav-item"><a class="nav-link active" href="#surveys" aria-controls="surveys" role="tab" data-bs-toggle="tab"><?php eT('Survey list'); ?></a></li>
        <li class="nav-item"><a class="nav-link" href="#surveygroups" aria-controls="surveygroups" role="tab" data-bs-toggle="tab"><?php eT('Survey groups'); ?></a></li>
    </ul>
    <div class="tab-content">
        <div id="surveys" class="tab-pane show active">
            <!-- Survey List widget -->
            <?php $this->widget('ext.admin.survey.ListSurveysWidget.ListSurveysWidget', array(
                        'pageSize' => Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']),
                        'model' => $model,
                ));
            ?>
        </div>

        <div id="surveygroups" class="tab-pane">
            <div class="pagetitle h3 ls-space margin top-25"><?php eT('Survey groups'); ?></div>
            <div class="row">
                <div class="col-12 content-right">
                    <?php
                    $this->widget('application.extensions.admin.grid.CLSGridView', [
                        'id'               => 'surveygroups--gridview',
                        'dataProvider'     => $groupModel->search(),
                        'lsAfterAjaxUpdate'          => [],
                        'columns'          => $groupModel->columns,
                        'rowLink' => 'App()->createUrl("admin/surveysgroups/sa/update/",array("id"=>$data->gsid))',
                        'summaryText'      => gT('Displaying {start}-{end} of {count} result(s).') . ' '
                            . sprintf(
                                gT('%s rows per page'),
                                CHtml::dropDownList(
                                    'surveygroups--pageSize',
                                    Yii::app()->user->getState('pageSize', Yii::app()->params['defaultPageSize']),
                                    App()->params['pageSizeOptions'],
                                    ['class' => 'changePageSize form-select', 'style' => 'display: inline; width: auto']
                                )
                            ),
                    ]);
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
if (Yii::app()->session['templatetoken'] ?? null) {
    Yii::import('application.helpers.admin.token_helper', true);
    $filename = decodeFilename(Yii::app()->session['templatetoken']);
    ?>
    <div id="dialog" title="Import template?">
        <?php echo "Shall we import the template file of {$filename}?"; ?>
    </div>
    <?php
}
?>
<script type="text/javascript">
    $('#surveysystem a').on('shown.bs.tab', function () {
        var tabId = $(this).attr('href');
        $('.tab-dependent-button:not([data-tab="' + tabId + '"])').toggleClass("d-none");
        $('.tab-dependent-button[data-tab="' + tabId + '"]').toggleClass("d-none");
    });
    $(document).on('ready pjax:scriptcomplete', function(){
        if(window.location.hash){
            $('#surveysystem').find('a[href='+window.location.hash+']').trigger('click');
        }
    });
    function sendRequest(type, url, callback, async = true, params = "") {
        if (async !== false) async = true;
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = callback;
        xhttp.open(type, url, async);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send(params);
    }
</script>
<!-- To update rows per page via ajax -->
<script type="text/javascript">
    jQuery(function($) {
        jQuery(document).on("change", '#surveygroups--pageSize', function(){
            $.fn.yiiGridView.update('surveygroups--gridview',{ data:{ pageSize: $(this).val() }});
        });
        <?php
        if (Yii::app()->session['templatetoken'] ?? null) {
        ?>
        jQuery("#dialog").dialog({
            open: function() {
                jQuery(this).closest(".ui-dialog")
                .find(".ui-dialog-titlebar-close")
                .removeClass("ui-dialog-titlebar-close")
                .html("<span class=\'ui-button-icon-primary ui-icon ui-icon-closethick\' id=\'dialog-close\'></span>");
                jQuery(this).parent().find(".ui-dialog-title").css("width", "calc(100% - 32px)");
            },
            close: function() {
                sendRequest("POST", "/index.php?r=admin/removeTemplateToken", undefined, true, `${LS.data.csrfTokenName}=${LS.data.csrfToken}`);
            },
            buttons: {
                Yes: function() {
                    sendRequest("POST", "/index.php?r=admin/installTemplateByToken", function() {
                        if (this.readyState === 4) {
                            if (this.responseText === 'success') {
                                jQuery('#dialog-close').click();
                            } else {
                                document.getElementById('dialog').innerHTML = this.responseText;
                            }
                        }
                    }, true, `${LS.data.csrfTokenName}=${LS.data.csrfToken}`);
                },
                No: function() {
                    jQuery('#dialog-close').click();
                }
            }
        })
        <?php
        }
        ?>
    });
    //show tooltip for gridview icons
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
</script>
