<?php
/** TODO: Can't find any implementation of this file #UNUSED
 * @var string $closeBtnUrl
 * @var int $surveyId
 * @var Question $question
 */
?>

<div id="question-create-edit-topbar" class="container-fluid vue-general-topbar">
    <div class="topbarpanel">
        <nav class="navbar navbar-default scoped-topbar-nav">
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
                            <!-- Save -->
                            <a
                                id="save-button-create-question"
                                class="btn btn-default"
                                role="button"
                                <?php if ($question->qid !== 0): // Only enable Ajax save for edit question, not create question. ?>
                                    data-save-with-ajax="true"
                                <?php endif; ?>
                                onclick="return LS.questionEditor.checkIfSaveIsValid(event, 'editor');"
                            >
                                <i class="fa fa-check-square"></i>
                                <?php eT("Save");?>
                            </a>
                        </div>
                    </li>
                    <li>
                        <div class="topbarbutton">
                            <!-- Save and close -->
                            <a
                                id="save-and-close-button-create-question"
                                class="btn btn-default"
                                role="button"
                                onclick="return LS.questionEditor.checkIfSaveIsValid(event, 'overview');"
                            >
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

<?php if ($question->qid !== 0): ?>
<div id="question-summary-topbar" class="container-fluid vue-general-topbar">
    <div class="topbarpanel">
        <nav class="navbar navbar-default scoped-topbar-nav">
            <div id="itm-7192564" class="ls-flex ls-flex-row ls-space padding top-5">
                <ul class="nav navbar-nav scoped-topbar-nav ls-flex-item ls-flex-row grow-2 text-left">
                    <li>
                        <div class="topbarbuttongroup btn-group">
                            <div class="topbardropdown">
                                <div class="topbarbutton">
                                    <button type="button" data-toggle="dropdown"
                                        aria-haspopup="true" data-btntype="2" class="btn btn-default navbar-btn button dropdown-toggle"><i 
                                            class="fa fa-cog icon">
                                        </i>&nbsp;Preview
                                        survey&nbsp;<i class="caret icon">
                                        </i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <?php foreach ($question->survey->allLanguages as $tmp_lang){ ?>
                                            <li>
                                                <a target='_blank' href='<?php echo $this->createUrl("survey/index",
                                                    array('sid'=>$question->sid,'newtest'=>"Y",'lang'=>$tmp_lang));?>'>
                                                    <?php echo getLanguageNameFromCode($tmp_lang,false); ?>
                                                </a>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                </div>
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
                                        group&nbsp;<i class="caret icon"></i>
                                    </button>
                                    <ul class="dropdown-menu" style="min-width : 252px;">
                                        <?php foreach ($question->survey->allLanguages as $tmp_lang): ?>
                                            <li>
                                                <a target="_blank"
                                                   href="<?php echo $this->createUrl("survey/index/action/previewgroup/sid/{$question->sid}/gid/{$question->gid}/lang/" . $tmp_lang); ?>" >
                                                    <?php echo getLanguageNameFromCode($tmp_lang,false); ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
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
                                        question&nbsp;<i class="caret icon"></i>
                                    </button>
                                    <ul class="dropdown-menu" style="min-width : 252px;">
                                        <?php foreach ($question->survey->allLanguages as $tmp_lang): ?>
                                            <li>
                                                <a target="_blank"
                                                   href='<?php echo $this->createUrl("survey/index/action/previewquestion/sid/" . $question->sid . "/gid/" . $question->gid . "/qid/" . $question->qid . "/lang/" . $tmp_lang); ?>' >
                                                    <?php echo getLanguageNameFromCode($tmp_lang,false); ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
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
                                        logic&nbsp;<i class="caret icon"></i>
                                    </button>
                                    <ul class="dropdown-menu" style="min-width : 252px;">
                                        <?php foreach ($question->survey->allLanguages as $tmp_lang): ?>
                                            <li>
                                                <a target="_blank"
                                                   href='<?php echo $this->createUrl("admin/expressions/sa/survey_logic_file/sid/" . $question->sid . "/gid/" . $question->gid . "/qid/" . $question->qid . "/lang/" . $tmp_lang); ?>' >
                                                    <?php echo getLanguageNameFromCode($tmp_lang,false); ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
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
                            <a type="button" href='<?php echo $this->createUrl('admin/export/sa/question/surveyid/' . $question->sid . "/gid/" . $question->gid . "/qid/" . $question->qid); ?>'
                                id="export_button" data-btntype="1" class="btn navbar-btn button btn-default btn-default"><i 
                                    class="icon-export icon"></i>&nbsp;Export&nbsp;</a></div>
                    </li>
                    <li>
                        <div class="topbarbutton">
                            <a type="button" href='<?php echo $this->createUrl("questionAdministration/copyQuestion/surveyId/" . $question->sid . "/questionGroupId/" . $question->gid. "/questionId/" . $question->qid);?>'
                                id="copy_button" data-btntype="1" class="btn navbar-btn button btn-default pjax"><i 
                                    class="icon-copy icon"></i>&nbsp;Copy&nbsp;</a></div>
                    </li>
                    <li>
                        <div class="topbarbutton">
                            <a type="button" href='<?php echo $this->createUrl('admin/conditions/sa/index/subaction/editconditionsform/surveyid/'. $question->sid .'/gid/' . $question->gid . '/qid/' . $question->qid);?>'
                                id="conditions_button" data-btntype="1" class="btn navbar-btn button btn-default btn-default"><i 
                                    class="icon-conditions icon"></i>&nbsp;Condition
                                designer&nbsp;</a></div>
                    </li>
                    <br>
                    <li>
                        <div class="topbarbutton">
                            <a type="button" href= <?php echo $this->createUrl('questionAdministration/importView/surveyid/' . $question->sid); ?>
                                id="import-button" data-btntype="1" class="btn navbar-btn button btn-default btn-default"><i 
                                    class="icon-import icon"></i>&nbsp;Import
                                question&nbsp;</a></div>
                    </li>
                    <?php if ($this->aData['hasdefaultvalues'] == 1) {?>
                    <li>
                        <div class="topbarbutton">
                            <a
                                type="button"
                                href= "<?php echo $this->createUrl("questionAdministration/editdefaultvalues",
                                ["surveyid" => $question->sid , "gid" => $question->gid , "qid" => $question->qid]); ?>"
                                id="default_value_button"
                                data-btntype="1"
                                class="btn navbar-btn button btn-default btn-default">
                                <i class="icon-defaultanswers"></i>&nbsp;Edit default values&nbsp;
                            </a>
                        </div>
                    </li>
                    <?php } ?>
                    <li class="slotbutton-content"></li>
                </ul>
            </div>
        </nav>
    </div>
</div>
<?php endif; ?>
