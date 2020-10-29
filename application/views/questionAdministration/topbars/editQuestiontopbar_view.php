<?php
/** @var string $closeBtnUrl */
/** @var int $surveyId */
/** @var Question $question */
?>

<style>
/* TODO: Move to CSS */
.topbarbutton {
    margin-left: 1px;
    margin-right: 1px;
}
.navbar ul {
    padding-top: 4px;
}
</style>

<?php if ($question->qid === 0): ?>
<div id="question-create-topbar" class="container-fluid vue-general-topbar" style="width: 100%; padding: 0px; margin: 0px;">
    <div class="topbarpanel">
        <nav class="navbar navbar-default scoped-topbar-nav" style="border: none;">
            <div class="ls-flex ls-flex-row ls-space padding top-5">
                <ul class="col-sm-4 nav navbar-nav scoped-topbar-nav ls-flex-item ls-flex-row grow-2 text-left">
                    <li>
                        <div class="topbarbutton">
                            <a class="btn btn-default"
                               href="<?php echo Yii::App()->createUrl('questionAdministration/importView', ['surveyid' => $surveyId]); ?>"
                               role="button">
                                <span class="icon-import"></span>
                                <?php eT('Import a question'); ?>
                            </a>
                        </div>
                    </li>
                </ul>
                <ul class="nav navbar-nav scoped-topbar-nav ls-flex-item ls-flex-row align-content-flex-end text-right padding-left scoped-switch-floats">

                    <!-- Right Buttons -->
                    <li>
                        <div class="topbarbutton">
                            <!-- Save and close -->
                            <a id="save-and-close-button-create-question" class="btn btn-default" role="button">
                                <i class="fa fa-check-square"></i>
                                <?php eT("Save and close");?>
                            </a>
                        </div>
                    </li>
                    <li>
                        <div class="topbarbutton">
                            <!-- Close -->
                            <a class="btn btn-danger" href="<?php echo $closeBtnUrl; ?>" role="button">
                                <span class="fa fa-close"></span>
                                <?php eT("Close");?>
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</div>
<?php else: ?>
<div id="question-edit-topbar" class="container-fluid vue-general-topbar" style="width: 100%; padding: 0px; margin: 0px;">
    <div class="topbarpanel">
        <nav class="navbar navbar-default scoped-topbar-nav" style="border: none;">
            <div class="ls-flex ls-flex-row ls-space padding top-5">
                <ul class="col-sm-4 nav navbar-nav scoped-topbar-nav ls-flex-item ls-flex-row grow-2 text-left">
                    <li>
                        <div class="topbarbutton">
                            <a class="btn btn-default"
                               href="<?php echo Yii::App()->createUrl('questionAdministration/importView', ['surveyid' => $surveyId]); ?>"
                               role="button">
                                <span class="icon-import"></span>
                                <?php eT('Import a question'); ?>
                            </a>
                        </div>
                    </li>
                </ul>
                <ul class="nav navbar-nav scoped-topbar-nav ls-flex-item ls-flex-row align-content-flex-end text-right padding-left scoped-switch-floats">

                    <!-- Right Buttons -->
                    <li>
                        <div class="topbarbutton">
                            <!-- Save and close -->
                            <a id="save-and-close-button-create-question" class="btn btn-default" role="button">
                                <i class="fa fa-check-square"></i>
                                <?php eT("Save and close");?>
                            </a>
                        </div>
                    </li>
                    <li>
                        <div class="topbarbutton">
                            <!-- Close -->
                            <a class="btn btn-danger" href="<?php echo $closeBtnUrl; ?>" role="button">
                                <span class="fa fa-close"></span>
                                <?php eT("Close");?>
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</div>
<?php endif; ?>

<?php if ($question->qid !== 0): ?>
<div id="question-summary-topbar" class="container-fluid vue-general-topbar" style="width: 100%; padding: 0px; margin: 0px;">
    <div class="topbarpanel">
        <nav class="navbar navbar-default scoped-topbar-nav" style="border: 0;">
            <div id="itm-7192564" class="ls-flex ls-flex-row ls-space padding top-5">
                <ul class="nav navbar-nav scoped-topbar-nav ls-flex-item ls-flex-row grow-2 text-left">
                    <li>
                        <div class="topbarbuttongroup btn-group">
                            <div class="topbardropdown">
                                <div class="topbarbutton">
                                    <button type="button" data-toggle="dropdown"
                                        aria-haspopup="true" data-btntype="2" class="btn btn-default navbar-btn button dropdown-toggle"><i 
                                            class="fa fa-cog icon"></i>&nbsp;Preview
                                        survey&nbsp;<i class="caret icon"></i></button></div>
                            </div>
                        </div>
                    </li>
                    <li>
                        <div class="topbarbuttongroup btn-group">
                            <div class="topbardropdown">
                                <div class="topbarbutton">
                                    <button type="button" data-toggle="dropdown"
                                        aria-haspopup="true" data-btntype="2" class="btn btn-default navbar-btn button dropdown-toggle"><i 
                                            class="fa fa-cog icon"></i>&nbsp;Preview question
                                        group&nbsp;<i class="caret icon"></i></button></div>
                            </div>
                        </div>
                    </li>
                    <li>
                        <div class="topbarbuttongroup btn-group">
                            <div class="topbardropdown">
                                <div class="topbarbutton">
                                    <button type="button" data-toggle="dropdown"
                                        aria-haspopup="true" data-btntype="2" class="btn btn-default navbar-btn button dropdown-toggle"><i 
                                            class="fa fa-cog icon"></i>&nbsp;Preview
                                        question&nbsp;<i class="caret icon"></i></button></div>
                            </div>
                        </div>
                    </li>
                    <li>
                        <div class="topbarbuttongroup btn-group">
                            <div class="topbardropdown">
                                <div class="topbarbutton">
                                    <button type="button" id="check_logic_button"
                                        data-toggle="dropdown" aria-haspopup="true" data-btntype="2" class="btn btn-default navbar-btn button dropdown-toggle"><i 
                                            class="icon-expressionmanagercheck icon"></i>&nbsp;Check
                                        logic&nbsp;<i class="caret icon"></i></button></div>
                            </div>
                        </div>
                    </li>
                    <li>
                        <div class="topbarbutton">
                            <a type="button" href="#" id="delete_button"
                                data-btntype="1" class="btn navbar-btn button white btn-danger"><i 
                                    class="fa fa-trash text-danger icon"></i>&nbsp;Delete&nbsp;</a></div>
                    </li>
                    <li>
                        <div class="topbarbutton">
                            <a type="button" href=
                                "/_limesurvey/index.php/admin/export/sa/question/surveyid/355832/gid/10/qid/77"
                                id="export_button" data-btntype="1" class="btn navbar-btn button btn-default btn-default"><i 
                                    class="icon-export icon"></i>&nbsp;Export&nbsp;</a></div>
                    </li>
                    <li>
                        <div class="topbarbutton">
                            <a type="button" href=
                                "/_limesurvey/index.php/questionAdministration/copyQuestion?surveyId=355832&amp;questionGroupId=10&amp;questionId=77"
                                id="copy_button" data-btntype="1" class="btn navbar-btn button btn-default btn-default"><i 
                                    class="icon-copy icon"></i>&nbsp;Copy&nbsp;</a></div>
                    </li>
                    <li>
                        <div class="topbarbutton">
                            <a type="button" href=
                                "/_limesurvey/index.php/admin/conditions/sa/index/subaction/editconditionsform/surveyid/355832/gid/10/qid/77"
                                id="conditions_button" data-btntype="1" class="btn navbar-btn button btn-default btn-default"><i 
                                    class="icon-conditions icon"></i>&nbsp;Condition
                                designer&nbsp;</a></div>
                    </li>
                    <li>
                        <div class="topbarbutton">
                            <a type="button" href=
                                "/_limesurvey/index.php/questionAdministration/importView?surveyid=355832"
                                id="import-button" data-btntype="1" class="btn navbar-btn button btn-default btn-default"><i 
                                    class="icon-import icon"></i>&nbsp;Import
                                question&nbsp;</a></div>
                    </li>
                    <li class="slotbutton-content"></li>
                </ul>
            </div>
        </nav>
    </div>
</div>
<?php endif; ?>

<script type="text/javascript">
    $(document).ready(function() {
        $("#save-and-close-button-create-question").click(function() {
            document.getElementById("submit-create-question").click();
        });
    });
</script>
