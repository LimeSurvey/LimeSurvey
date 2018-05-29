<div id='edit-question-body' class='side-body <?php echo getSideBodyClass(false); ?>'>
    <h3>
        <?php echo $pageTitle; ?> <small><em><?php echo $oQuestion->title;?></em> (ID: <?php echo $oQuestion->qid;?>)</small>
    </h3>

    <div class="row">
        <div class="col-lg-12 content-right">

            <!-- Result of modal actions (like replace labelset) -->
            <div id="dialog-result" title="Query Result" style='display:none;' class="alert alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span >&times;</span></button>
                <span id="dialog-result-content">
                </span>
            </div>

            <div id="dialog-duplicate" title="<?php eT('Duplicate label set name'); ?>" style='display:none;' class="alert alert-warning alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span >&times;</span></button>
                <p>
                    <?php eT('Sorry, the name you entered for the label set is already in the database. Please select a different name.'); ?>
                </p>
            </div>

            <?php echo CHtml::form(array("admin/database"), 'post', array('id'=>$formId, 'name'=>$formName)); ?>

            <?php
                foreach ($anslangs as $i => $anslang) {
                    $base_language[$i] = getLanguageNameFromCode($anslang, false).($anslang==Survey::model()->findByPk($surveyid)->language ? ' ('.gT("Base language").')':'');
                }

            $aData = array(
                'surveyid' => $surveyid,
                'gid' => $gid,
                'qid' => $qid,
                'viewType' => $viewType,
                'anslangs' => $anslangs,
                'scalecount' => $scalecount,
                'results' => $results,
                'tableId' => $tableId,
                'activated' => $activated,
                'assessmentvisible' => (empty($assessmentvisible)) ? false : $assessmentvisible,
                'base_language' => $base_language,
                'has_permissions' => Permission::model()->hasGlobalPermission('superadmin','read') || Permission::model()->hasGlobalPermission('labelsets','create'),
                'all_languages' => Survey::model()->findByPk($surveyid)->getAllLanguages()
            );

            echo App()->twigRenderer->renderAnswerOptions('/admin/survey/Question/answerOptionsEdit_view', $aData); ?> 

            </form>

        </div>
    </div>
</div>
