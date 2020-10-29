<!-- Temporary top bar while working on question editor
<div id="vue-topbar-container" class="container-fluid vue-general-topbar" style="width: 100%; padding: 0px; margin: 0px;">
    <div class="topbarpanel">
        <nav class="navbar navbar-default scoped-topbar-nav" style="border: none;">
            <div id="itm-9127598" class="ls-flex ls-flex-row ls-space padding top-5">
                <ul class="nav navbar-nav scoped-topbar-nav ls-flex-item ls-flex-row grow-2 text-left">
                    <li>
                        <div class="topbarbutton">
<a type="button" href="/index.php?r=surveyAdministration/activate&amp;iSurveyID=829174" id="ls-activate-survey" data-btntype="1" class="btn navbar-btn button  white btn-success">
&nbsp;Activate this survey&nbsp;</a>
                        </div>
                    </li>
                    <li>
                        <div class="topbarbutton">
                            <a id="save-button" type="button" href="#" class="btn navbar-btn button btn-success">Save</a>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</div>  -->
<?php
/**  @var string $closeBtnUrl */
/** @var int $surveyId */

?>

<div class='menubar surveybar' id="questiongroupbarid">
    <div class='row container-fluid'>

        <div class="col-sm-4 ">
            <a class="btn btn-default"
               href="<?php echo Yii::App()->createUrl('questionAdministration/importView', ['surveyid' => $surveyId]); ?>"
               role="button">
                <span class="icon-import"></span>
                <?php eT('Import a question'); ?>
            </a>
        </div>

        <!-- Right Buttons -->
        <div class="col-sm-4 pull-right text-right">

            <!-- Save and close -->
            <a id="save-and-close-button-create-question" class="btn btn-default" role="button">
                <i class="fa fa-check-square"></i>
                <?php eT("Save and close");?>
            </a>

            <!-- Close -->
            <a class="btn btn-danger" href="<?php echo $closeBtnUrl; ?>" role="button">
                <span class="fa fa-close"></span>
                <?php eT("Close");?>
            </a>

        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $("#save-and-close-button-create-question").click(function() {
            document.getElementById("submit-create-question").click();
        });
    });
</script>
