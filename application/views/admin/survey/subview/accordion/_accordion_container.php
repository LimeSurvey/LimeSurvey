<?php
/**
 * Right accordion in the edit survey page
 *
 * @var $data
 */
?>

<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">

    <!-- General Option -->
    <div class="panel panel-default" id="generaloptionsContainer">
        <div class="panel-heading" role="tab" id="headingOne">
            <h4 class="panel-title">
                <a class="btn btn-default btn-xs hide-button hidden-xs opened handleAccordion hidden-sm">
                    <span class="glyphicon glyphicon-chevron-left"></span>
                </a>
                <a role="button" data-toggle="collapse" href="#generaloptions" aria-expanded="true" aria-controls="generaloptions">
                    <?php eT("General options");?>
                </a>
            </h4>
        </div>
        <div id="generaloptions" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
            <div class="panel-body">
                <?php $this->renderPartial('/admin/survey/subview/accordion/_generaloptions_panel', $data); ?>
            </div>
        </div>
    </div>


    <!-- Presentation & navigation  -->
    <div class="panel panel-default">
        <div class="panel-heading" role="tab" id="headingTwo">
            <h4 class="panel-title">
                <a class="btn btn-default btn-xs hide-button hidden-xs opened handleAccordion hidden-sm">
                    <span class="glyphicon glyphicon-chevron-left"></span>
                </a>
                <a class="collapsed" role="button" data-toggle="collapse" href="#presentationoptions" aria-expanded="false" aria-controls="presentationoptions">
                    <?php  eT("Presentation & navigation"); ?>
                </a>
            </h4>
        </div>
        <div id="presentationoptions" class="panel-collapse collapse" role="tabpanel" aria-labelledby="presentationoptions">
            <div class="panel-body">
                <?php $this->renderPartial('/admin/survey/subview/accordion/_presentation_panel', $data); ?>
            </div>
        </div>
    </div>

    <!-- Publication & access control -->
    <div class="panel panel-default">
        <div class="panel-heading" role="tab" id="headingThree">
            <h4 class="panel-title">
                <a class="btn btn-default btn-xs hide-button hidden-xs opened handleAccordion hidden-sm">
                    <span class="glyphicon glyphicon-chevron-left"></span>
                </a>
                <a class="collapsed" role="button" data-toggle="collapse" href="#publicationoptions" aria-expanded="false" aria-controls="publicationoptions">
                    <?php  eT("Publication & access control"); ?>
                </a>
            </h4>
        </div>
        <div id="publicationoptions" class="panel-collapse collapse" role="tabpanel" aria-labelledby="publicationoptions">
            <div class="panel-body">
                <?php $this->renderPartial('/admin/survey/subview/accordion/_publication_panel', $data); ?>
            </div>
        </div>
    </div>

    <!-- Notification & data management -->
    <div class="panel panel-default">
        <div class="panel-heading" role="tab" id="headingFour">
            <h4 class="panel-title">
                <a class="btn btn-default btn-xs hide-button hidden-xs opened handleAccordion hidden-sm">
                    <span class="glyphicon glyphicon-chevron-left"></span>
                </a>
                <a class="collapsed" role="button" data-toggle="collapse" href="#notificationoptions" aria-expanded="false" aria-controls="notificationoptions">
                    <?php  eT("Notification & data management"); ?>
                </a>
            </h4>
        </div>
        <div id="notificationoptions" class="panel-collapse collapse" role="tabpanel" aria-labelledby="notificationoptions">
            <div class="panel-body">
                <?php $this->renderPartial('/admin/survey/subview/accordion/_notification_panel', $data); ?>
            </div>
        </div>
    </div>

    <!-- Tokens -->
    <div class="panel panel-default">
        <div class="panel-heading" role="tab" id="headingFive">
            <h4 class="panel-title">
                <a class="btn btn-default btn-xs hide-button hidden-xs opened handleAccordion hidden-sm">
                    <span class="glyphicon glyphicon-chevron-left"></span>
                </a>
                <a class="collapsed" role="button" data-toggle="collapse" href="#tokensoptions" aria-expanded="false" aria-controls="tokensoptions">
                    <?php  eT("Tokens"); ?>
                </a>
            </h4>
        </div>
        <div id="tokensoptions" class="panel-collapse collapse" role="tabpanel" aria-labelledby="tokensoptions">
            <div class="panel-body">
                <?php $this->renderPartial('/admin/survey/subview/accordion/_tokens_panel', $data); ?>
            </div>
        </div>
    </div>

    <!-- Edition Mode -->
    <?php if($data['action']=='editsurveysettings'):?>

        <!-- Panel integration -->
        <div class="panel panel-default">
            <div class="panel-heading" role="tab" id="headingSix">
                <h4 class="panel-title">
                    <a class="btn btn-default btn-xs hide-button hidden-xs opened handleAccordion hidden-sm">
                        <span class="glyphicon glyphicon-chevron-left"></span>
                    </a>
                    <a class="collapsed" role="button" data-toggle="collapse" href="#integrationoptions" aria-expanded="false" aria-controls="integrationoptions">
                        <?php  eT("Panel integration"); ?>
                    </a>
                </h4>
            </div>
            <div id="integrationoptions" class="panel-collapse collapse" role="tabpanel" aria-labelledby="integrationoptions">
                <div class="panel-body">
                    <?php $this->renderPartial('/admin/survey/subview/accordion/_integration_panel', $data); ?>
                </div>
            </div>
        </div>

        <!-- PLugin settings -->
        <div class="panel panel-default">
            <div class="panel-heading" role="tab" id="headingEight">
                <h4 class="panel-title">
                    <a class="btn btn-default btn-xs hide-button hidden-xs opened handleAccordion hidden-sm">
                        <span class="glyphicon glyphicon-chevron-left"></span>
                    </a>
                    <a class="collapsed" role="button" data-toggle="collapse" href="#pluginsoptions" aria-expanded="false" aria-controls="pluginsoptions">
                        <?php  eT("Plugins"); ?>
                    </a>
                </h4>
            </div>
            <div id="pluginsoptions" class="panel-collapse collapse" role="tabpanel" aria-labelledby="pluginoptions">
                <div class="panel-body">
                    <?php $this->renderPartial('/admin/survey/subview/accordion/_plugins_panel', $data); ?>
                </div>
            </div>
        </div>

        <!-- Resources -->
        <div class="panel panel-default">
            <div class="panel-heading" role="tab" id="headingSeven">
                <h4 class="panel-title">
                    <a class="btn btn-default btn-xs hide-button hidden-xs opened handleAccordion hidden-sm">
                        <span class="glyphicon glyphicon-chevron-left"></span>
                    </a>
                    <a class="collapsed" role="button" data-toggle="collapse" href="#resourcesoptions" aria-expanded="false" aria-controls="resourcesoptions">
                        <?php  eT("Resources"); ?>
                    </a>
                </h4>
            </div>
            <div id="resourcesoptions" class="panel-collapse collapse" role="tabpanel" aria-labelledby="resourcesoptions">
                <div class="panel-body">
                    <?php $this->renderPartial('/admin/survey/subview/accordion/_resources_panel', $data); ?>
                </div>
            </div>
        </div>
    <?php endif;?>
</div>
