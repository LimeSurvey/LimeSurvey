<?php

/** @var $this SurveyAdministrationController */

?>

<style>
    #template-preview .modal-dialog {
        transition: max-width 1s;
        transform: translate(0, 98px);
    }

    #template-preview.preview-outer .modal-dialog {
        max-width: 50%;
        height: 60vh;
    }

    #template-preview .modal-dialog .modal-content {
        transition: height 1s;
        height: 77%;
        text-align: left;
        color: #000;
    }

    #template-preview.preview-outer .modal-dialog .modal-content {
        height: 90%;
    }

    #template-preview.preview-outer .modal-dialog .modal-body {
        height: 100%;
        overflow-y: hidden;
    }

    #template-preview:not(.preview-outer) .modal-footer .btn.btn-collapse, #template-preview.preview-outer .modal-footer .btn:not(.btn-collapse), #template-preview:not(.preview-outer) #actual-preview {
        display: none;
    }

    .btn.btn-collapse {
        border: 1px solid #212529;
    }

    .btn.btn-collapse:hover {
        color: #212529;
    }

    #actual-preview {
        height: 100%;
        width: 100%;
    }

    .modal-body .preview {
        height: 100%;
    }
</style>
<?php
if ((Yii::app()->request->getParam('popuppreview', false) !== false) && ($sid = intval(Yii::app()->request->getParam('surveyid', false)))) {
?>
<div id="template-preview" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal Content -->
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title"><?php eT("Survey preview"); ?></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="controls alert alert-filled-info">
                    <span class="ri-notification-2-line me-2"></span>
                    <?php echo sprintf(gT("You can always make changes to the %stheme%s and customise questions of this %stemplate%s"), "<a target='_blank' href='/themeOptions/updateSurvey?surveyid={$sid}&gsid=1'>", "</a>", "<a target='_blank' href='/questionAdministration/listQuestions?surveyid={$sid}'>", "</a>"); ?>
                </div>
                <div class="preview">
                    <iframe id="actual-preview" src="/<?php echo $sid; ?>?newtest=Y&lang=en&popuppreview=true"></iframe>
                </div>
            </div>
            <div class="modal-footer modal-footer-buttons">
                <button type="button" class="btn btn-collapse" data-bs-dismiss="modal"><?php eT('Close'); ?></button>
            </div>
        </div>
    </div>
</div>
<script>
    let context = document.getElementById("template-preview");
    context.classList.add("show");
    context.style.display = "block";
    let popupBackground = document.createElement("div");
    popupBackground.className = "modal-backdrop fade show";
    document.body.appendChild(popupBackground);
    context.querySelector("#actual-preview").onload = function() {
        context.classList.add("preview-outer");
    };
    function closeTemplatePopup() {
        context.classList.remove('show');
        context.style.display = 'none';
        popupBackground.remove();
    }
    for (let closeItem of context.querySelectorAll('.btn-close, .btn-collapse')) {
        closeItem.addEventListener("click", closeTemplatePopup);
    }
    context.querySelector('.modal-dialog').addEventListener("click", function(e) {
        e.stopPropagation();
    });
</script>

<?php
}
?>

<div id="vue-side-body-app" class='side-body'>
    <?php // OLD echo $content; ?>
    <?php  $this->renderPartial("surveySummary_view", $this->aData); ?>
</div>
