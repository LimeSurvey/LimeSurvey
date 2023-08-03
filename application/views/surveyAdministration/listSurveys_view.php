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
    <div id="install-template-token" class="modal fade" role="dialog">
    <div class="modal-dialog ">
        <!-- Modal content-->
        <div class="modal-content" style="text-align:left; color:#000">
            <div class="modal-header">
                <h1 class="modal-title">Import Survey?</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="modal-body-text"><?php echo "Shall we import the template file of {$filename}?"; ?></div>
                <div class="preview" style="display: none;">mypreview</div>
            </div>
            
            <div class="modal-footer modal-footer-buttons">
                <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">No</button>
                <a role="button" class="btn btn-danger btn-ok">Yes</a>
            </div>
        </div>
    </div>
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
        let context = document.getElementById("install-template-token");
        context.classList.add("show");
        context.style.display = "block";
        let isPreview = false;
        let extraParams = `${LS.data.csrfTokenName}=${LS.data.csrfToken}`;
        for (let closeItem of context.querySelectorAll('.btn-close, .btn-cancel')) {
            closeItem.addEventListener("click", function() {
                sendRequest("POST", "/index.php?r=admin/removeTemplateToken", undefined, true, extraParams);
                context.classList.remove("show");
                context.style.display = "none";
                if (isPreview) {
                    window.location.reload();
                }
            });
        }
        context.querySelector('.btn-ok').addEventListener("click", function() {
            sendRequest("POST", "/index.php?r=admin/installTemplateByToken", function() {
                if (this.readyState === 4) {
                    if (this.responseText === 'success') {
                        context.querySelector('.modal-body-text').style.display = 'none';
                        context.querySelector('.preview').style.display = 'block';
                        for (let btn of context.querySelectorAll('.btn')) btn.style.display = 'none';
                        isPreview = true;
                    } else {
                        context.querySelector('.modal-body-text').innerHTML = this.responseText;
                    }
                    sendRequest("POST", "/index.php?r=admin/removeTemplateToken", undefined, true, extraParams);
                }
            }, true, extraParams);
        });
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
