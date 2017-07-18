<?php
/**
 * Right accordion in the edit survey page
 *
 * @var $data
 */
?>
<!-- Enable aufo-focus on element via url hash -->
<script>
    $(document).ready(function () {
        if(location.hash != null && location.hash != ""){
            $('.collapse').removeClass('in');
            $(location.hash + '.collapse').collapse('show');
        }
    });
</script>

<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">

    <!-- General Option -->
    <div class="panel panel-default" id="generaloptionsContainer">
        <div class="panel-heading" role="tab" id="heading-generaloptions">
            <div class="panel-title h4">
                <a class="btn btn-default btn-xs hide-button hidden-xs opened handleAccordion hidden-sm">
                    <span class="fa fa-chevron-left"></span>
		    <span class="sr-only">Expand/Collapse</span>
                </a>
                <a role="button" data-toggle="collapse" data-parent="#accordion" href="#generaloptions" aria-expanded="true" aria-controls="generaloptions">
                    <?php eT("General options");?>
                </a>
            </div>
        </div>
        <div id="generaloptions" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="heading-generaloptions">
            <div class="panel-body">
                <?php $this->renderPartial('/admin/survey/subview/accordion/_generaloptions_panel', $data); ?>
            </div>
        </div>
    </div>

    <!-- Presentation & navigation  -->
    <div class="panel panel-default">
        <div class="panel-heading" role="tab" id="heading-presentationoptions">
            <div class="panel-title h4">
                <a class="btn btn-default btn-xs hide-button hidden-xs opened handleAccordion hidden-sm">
                    <span class="fa fa-chevron-left"></span>
		    <span class="sr-only">Expand/Collapse</span>
                </a>
                <a class="collapsed" role="button" data-parent="#accordion" data-toggle="collapse" href="#presentationoptions" aria-expanded="false" aria-controls="presentationoptions">
                    <?php  eT("Presentation & navigation"); ?>
                </a>
            </div>
        </div>
        <div id="presentationoptions" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading-presentationoptions">
            <div class="panel-body">
                <?php $this->renderPartial('/admin/survey/subview/accordion/_presentation_panel', $data); ?>
            </div>
        </div>
    </div>

    <!-- Publication & access control -->
    <div class="panel panel-default">
        <div class="panel-heading" role="tab" id="heading-publicationoptions">
            <div class="panel-title h4">
                <a class="btn btn-default btn-xs hide-button hidden-xs opened handleAccordion hidden-sm">
                    <span class="fa fa-chevron-left"></span>
		    <span class="sr-only">Expand/Collapse</span>
                </a>
                <a class="collapsed" role="button" data-parent="#accordion" data-toggle="collapse" href="#publicationoptions" aria-expanded="false" aria-controls="publicationoptions">
                    <?php  eT("Publication & access control"); ?>
                </a>
            </div>
        </div>
        <div id="publicationoptions" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading-publicationoptions">
            <div class="panel-body">
                <?php $this->renderPartial('/admin/survey/subview/accordion/_publication_panel', $data); ?>
            </div>
        </div>
    </div>

    <!-- Notification & data management -->
    <div class="panel panel-default">
        <div class="panel-heading" role="tab" id="heading-notificationoptions">
            <div class="panel-title h4">
                <a class="btn btn-default btn-xs hide-button hidden-xs opened handleAccordion hidden-sm">
                    <span class="fa fa-chevron-left"></span>
		    <span class="sr-only">Expand/Collapse</span>
                </a>
                <a class="collapsed" role="button" data-parent="#accordion" data-toggle="collapse" href="#notificationoptions" aria-expanded="false" aria-controls="notificationoptions">
                    <?php  eT("Notification & data management"); ?>
                </a>
            </div>
        </div>
        <div id="notificationoptions" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading-notificationoptions">
            <div class="panel-body">
                <?php $this->renderPartial('/admin/survey/subview/accordion/_notification_panel', $data); ?>
            </div>
        </div>
    </div>

    <!-- Tokens -->
    <div class="panel panel-default">
        <div class="panel-heading" role="tab" id="heading-tokensoptions">
            <div class="panel-title h4">
                <a class="btn btn-default btn-xs hide-button hidden-xs opened handleAccordion hidden-sm">
                    <span class="fa fa-chevron-left"></span>
		    <span class="sr-only">Expand/Collapse</span>
                </a>
                <a class="collapsed" role="button" data-parent="#accordion" data-toggle="collapse" href="#tokensoptions" aria-expanded="false" aria-controls="tokensoptions">
                    <?php  eT("Tokens"); ?>
                </a>
            </div>
        </div>
        <div id="tokensoptions" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading-tokensoptions">
            <div class="panel-body">
                <?php $this->renderPartial('/admin/survey/subview/accordion/_tokens_panel', $data); ?>
            </div>
        </div>
    </div>

    <!-- Edition Mode -->
    <?php if($data['action']=='surveygeneralsettings'):?>
        <!-- Panel integration -->
        <div class="panel panel-default">
            <div class="panel-heading" role="tab" id="heading-integrationoptions">
                <div class="panel-title h4">
                    <a class="btn btn-default btn-xs hide-button hidden-xs opened handleAccordion hidden-sm">
                        <span class="fa fa-chevron-left"></span>
			<span class="sr-only">Expand/Collapse</span>
                    </a>
                    <a class="collapsed" role="button" data-parent="#accordion" data-toggle="collapse" href="#integrationoptions" aria-expanded="false" aria-controls="integrationoptions">
                        <?php  eT("Panel integration"); ?>
                    </a>
                </div>
            </div>
            <div id="integrationoptions" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading-integrationoptions">
                <div class="panel-body">
                    <?php $this->renderPartial('/admin/survey/subview/accordion/_integration_panel', $data); ?>
                </div>
            </div>
        </div>

        <!-- PLugin settings -->
        <?php $this->renderPartial('/admin/survey/subview/accordion/_plugins_panel', $data); ?>

        <!-- Resources -->
        <div class="panel panel-default">
            <div class="panel-heading" role="tab" id="heading-resourcesoptions">
                <div class="panel-title h4">
                    <a class="btn btn-default btn-xs hide-button hidden-xs opened handleAccordion hidden-sm">
                        <span class="fa fa-chevron-left"></span>
                    </a>
                    <a class="collapsed" role="button" data-parent="#accordion" data-toggle="collapse" href="#resourcesoptions" aria-expanded="false" aria-controls="resourcesoptions">
                        <?php  eT("Resources"); ?>
                    </a>
                </div>
            </div>
            <div id="resourcesoptions" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading-resourcesoptions">
                <div class="panel-body">
                    <?php $this->renderPartial('/admin/survey/subview/accordion/_resources_panel', $data); ?>
                </div>
            </div>
        </div>
    <?php endif;?>
    */?>
</div>
