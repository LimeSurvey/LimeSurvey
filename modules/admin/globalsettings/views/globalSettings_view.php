<?php
/**
 * @var $tgis AdminController
 */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('globalsettings');

?>
<script type="text/javascript">
    var msgCantRemoveDefaultLanguage = '<?php eT("You can't remove the default language.", 'js'); ?>';
</script>
<ul class="nav nav-tabs" id="settingTabs">
    <li role="presentation" class="nav-item"><a role="tab" class="nav-link active" data-bs-toggle="tab" href='#overview'><?php eT("Overview"); ?></a></li>
    <li role="presentation" class="nav-item"><a role="tab" class="nav-link" data-bs-toggle="tab" href='#general'><?php eT("General"); ?></a></li>
    <li role="presentation" class="nav-item"><a role="tab" class="nav-link" data-bs-toggle="tab" href='#email'><?php eT("Email settings"); ?></a></li>
    <li role="presentation" class="nav-item"><a role="tab" class="nav-link" data-bs-toggle="tab" href='#bounce'><?php eT("Bounce settings"); ?></a></li>
    <li role="presentation" class="nav-item"><a role="tab" class="nav-link" data-bs-toggle="tab" href='#security'><?php eT("Security"); ?></a></li>
    <li role="presentation" class="nav-item"><a role="tab" class="nav-link" data-bs-toggle="tab" href='#useradmin'><?php eT("User administration"); ?></a></li>
    <li role="presentation" class="nav-item"><a role="tab" class="nav-link" data-bs-toggle="tab" href='#presentation'><?php eT("Presentation"); ?></a></li>
    <li role="presentation" class="nav-item"><a role="tab" class="nav-link" data-bs-toggle="tab" href='#language'><?php eT("Language"); ?></a></li>
    <li role="presentation" class="nav-item"><a role="tab" class="nav-link" data-bs-toggle="tab" href='#interfaces'><?php eT("Interfaces"); ?></a></li>
    <li role="presentation" class="nav-item"><a role="tab" class="nav-link" data-bs-toggle="tab" href='#storage'><?php eT("Storage"); ?></a></li>
</ul>
<?php echo CHtml::form(["admin/globalsettings"], 'post', ['class' => '', 'id' => 'frmglobalsettings', 'name' => 'frmglobalsettings', 'autocomplete' => 'off']); ?>
<div class="tab-content">
    <div id="overview" class="tab-pane show active col-lg-6 offset-lg-1">
        <?php $this->renderPartial(
            "./globalsettings/_overview",
            [
                'usercount'          => $usercount,
                'surveycount'        => $surveycount,
                'activesurveycount'  => $activesurveycount,
                'deactivatedsurveys' => $deactivatedsurveys,
                'activetokens'       => $activetokens,
                'deactivatedtokens'  => $deactivatedtokens,

                // Here, we pass to the subview the new parameter
                'myNewParam'         => $myNewParam,
            ]
        ); ?>
    </div>

    <div id="general" class="tab-pane col-lg-10 offset-lg-1">
        <?php $this->renderPartial(
            "./globalsettings/_general",
            [
                'aListOfThemeObjects' => $aListOfThemeObjects,
                'aEncodings'          => $aEncodings,
                'thischaracterset'    => $thischaracterset,
                'sideMenuBehaviour'   => $sideMenuBehaviour
            ]
        ); ?>
    </div>

    <div id="email" class="tab-pane col-lg-10 offset-lg-1">
        <?php $this->renderPartial("./globalsettings/_email", [
            'emailPlugins' => $emailPlugins,
        ]); ?>
    </div>

    <div id="bounce" class="tab-pane col-lg-10 offset-lg-1">
        <?php $this->renderPartial("./globalsettings/_bounce"); ?>
    </div>

    <div id="security" class="tab-pane col-lg-10 offset-lg-1">
        <?php $this->renderPartial("./globalsettings/_security"); ?>
    </div>
    <div id="useradmin" class="tab-pane col-lg-10 offset-lg-1">
        <?php $this->renderPartial(
            "./globalsettings/_useradministration",
            [
                'sSendAdminCreationEmail'     => $sGlobalSendAdminCreationEmail,
                'sAdminCreationEmailSubject'  => $sGlobalAdminCreationEmailSubject,
                'sAdminCreationEmailTemplate' => $sGlobalAdminCreationEmailTemplate,
                'nameDisplayType'        => $nameDisplayType,
            ]
        ); ?>
    </div>

    <div id="presentation" class="tab-pane col-lg-10 offset-lg-1">
        <?php $this->renderPartial("./globalsettings/_presentation"); ?>
    </div>

    <div id="language" class="tab-pane col-lg-10 offset-lg-1">
        <?php $this->renderPartial(
            "./globalsettings/_language",
            [
                'restrictToLanguages' => $restrictToLanguages,
                'allLanguages'        => $allLanguages,
                'excludedLanguages'   => $excludedLanguages
            ]
        ); ?>
    </div>

    <div id="interfaces" class="tab-pane col-lg-6 offset-lg-1">
        <?php $this->renderPartial("./globalsettings/_interfaces"); ?>
    </div>

    <div id="storage" class="tab-pane col-lg-6 offset-lg-1">
        <?php $this->renderPartial("./globalsettings/_storage"); ?>
    </div>
</div>
<input type='hidden' name='restrictToLanguages' id='restrictToLanguages' value='<?php implode(' ', $restrictToLanguages); ?>'/>
<input type='hidden' name='action' value='globalsettingssave'/>
<input type='submit' class="d-none"/>
<?php echo CHtml::endForm() ?>
