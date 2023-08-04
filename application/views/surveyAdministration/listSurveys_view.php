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
<style>
    #install-template-token .modal-dialog {
        transition: max-width 1s;
    }
    #install-template-token.preview-outer .modal-dialog {
        max-width: 50%;
    }

    #install-template-token .modal-dialog .modal-content {
        transition: height 1s;
        height: 77%;
    }

    #install-template-token.preview-outer .modal-dialog {
        height:60vh;
    }

    #install-template-token.preview-outer .modal-dialog .modal-content {
        height: 90%;
    }
    #install-template-token.preview-outer .modal-dialog .modal-body {
        height:100%;
        overflow:auto;
    }

    #install-template-token:not(.preview-outer) .modal-footer .btn.btn-close {
        display: none;
    }

    #install-template-token.preview-outer .modal-footer .btn:not(.btn-close) {
        display: none;
    }

</style>
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
    ?>
    <div id="install-template-token" class="modal fade" role="dialog">
    <div class="modal-dialog" style="transform: translate(0, 98px);">
        <!-- Modal content-->
        <div class="modal-content" style="text-align:left; color:#000;">
            <div class="modal-header">
                <h1 class="modal-title"><?php eT('Import Survey?'); ?></h1>
            </div>
            <div class="modal-body">
                <div class="modal-body-text"><?php echo sprintf(gT('%sPlease confirm that you want to create your template.%s'), "<p>", "</p>"); ?></div>
                <div class="preview" style="display: none;"><img src="https://mdbcdn.b-cdn.net/img/Photos/Thumbnails/Slides/2.webp" class="w-100"/></div>
            </div>
            
            <div class="modal-footer modal-footer-buttons">
                <a role="button" class="btn btn-primary btn-ok"><?php eT('Use This Template'); ?></a>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" style="color: white;"><?php eT('No'); ?></button>
                <button type="button" class="btn btn-close" data-bs-dismiss="modal"><?php eT('Close'); ?></button>
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
        let popupBackground = document.createElement("div");
        popupBackground.className = "modal-backdrop fade show";
        document.body.appendChild(popupBackground);
        let isPreview = false;
        let extraParams = `${LS.data.csrfTokenName}=${LS.data.csrfToken}`;
        function closeTemplatePopup() {
            sendRequest("POST", "/index.php?r=admin/removeTemplateToken", undefined, true, extraParams);
            context.classList.remove('show');
            context.style.display = 'none';
            if (isPreview) {
                window.location.reload();
            }
            popupBackground.remove();
        }
        for (let closeItem of context.querySelectorAll('.btn-close, .btn-cancel')) {
            closeItem.addEventListener("click", closeTemplatePopup);
        }
        context.querySelector('.btn-ok').addEventListener("click", function() {
            sendRequest("POST", "/index.php?r=admin/installTemplateByToken", function() {
                if (this.readyState === 4) {
                    if (this.responseText === 'success') {
                        context.querySelector('.modal-body-text').style.display = 'none';
                        let preview = context.querySelector('.preview');
                        preview.style.display = 'block';
                        context.querySelector('.modal-title').innerText = '<?php eT('Question preview'); ?>';
                        for (let btn of context.querySelectorAll('.modal-footer .btn')) {
                            btn.classList[btn.classList.contains('invisible') ? 'add' : 'remove']('invisible');
                        };
                        isPreview = true;
                        context.classList.add('preview-outer');
                    } else {
                        context.querySelector('.modal-body-text').innerHTML = this.responseText;
                    }
                    sendRequest("POST", "/index.php?r=admin/removeTemplateToken", undefined, true, extraParams);
                }
            }, true, extraParams);
        });

        context.addEventListener("click", closeTemplatePopup);

        context.querySelector('.modal-dialog').addEventListener("click", function(evt) {
            evt.stopPropagation();
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
