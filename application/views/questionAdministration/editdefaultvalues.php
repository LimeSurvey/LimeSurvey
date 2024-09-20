<?php
/**
 * @var AdminController $this
 * @var Survey $oSurvey
 * @var stdClass $questionMetaData these are the settings from table question_theme (see function QuestionTheme::findQuestionMetaData)
 * @var array $questionrow
 * @var array $langopts
 * @var string $hasUpdatePermission
 */

?>
    <div id='edit-question-body' class='side-body'>
        <div class="pagetitle h1">
            <?php eT('Edit default answer values') ?>
        </div>
        <div class="row">
            <div class="col-xl-8 content-right">
                <?php echo CHtml::form(["admin/database/index"], 'post', ['class' => '', 'id' => 'frmdefaultvalues', 'name' => 'frmdefaultvalues']); ?>
                <ul class="nav nav-tabs">
                    <?php foreach ($oSurvey->allLanguages as $i => $language) : ?>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link <?= ($i == 0) ? "active" : "" ?>" data-bs-toggle="tab" href='#df_<?php echo $language ?>'><?php echo getLanguageNameFromCode($language, false) ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="tab-content">
                    <?php foreach ($oSurvey->allLanguages as $i => $language) : ?>
                        <div id='df_<?php echo $language ?>' class="tab-pane fade <?php echo $i == 0 ? 'show active' : '' ?> ps-3">
                            <?php if ((int)$questionMetaData->answerscales > 0) : ?>
                                <?php for ($scale_id = 0; $scale_id < (int)$questionMetaData->answerscales; $scale_id++) : ?>
                                    <?php $opts = $langopts[$language][$questionrow['type']][$scale_id]; ?>
                                    <div class="mb-3 col-12">
                                        <label class=" form-label" for='defaultanswerscale_<?php echo "{$scale_id}_{$language}" ?>'>
                                            <?php (int)$questionMetaData->answerscales > 1
                                                ? printf(gT('Default answer for scale %s:'), $scale_id)
                                                : printf(gT('Default answer value:'), $scale_id) ?>
                                        </label>
                                        <div class="col-12">
                                            <select class='form-select' name='defaultanswerscale_<?php echo "{$scale_id}_{$language}" ?>'
                                                    id='defaultanswerscale_<?php echo "{$scale_id}_{$language}" ?>'>
                                                <option value=''<?php echo is_null($opts['defaultvalue']) ? ' selected="selected"' : '' ?>>
                                                    <?php eT('(No default value)') ?>
                                                </option>
                                                <?php foreach ($opts['answers'] as $answer) {
                                                    $sAnswer          = $answer->answerl10ns[$language]->answer;
                                                    $answer           = $answer->attributes;
                                                    $answer['answer'] = $sAnswer;
                                                    ?>
                                                    <option <?php echo $answer['code'] == $opts['defaultvalue'] ? 'selected="selected"' : '' ?> value="<?php echo $answer['code'] ?>">
                                                        <?php echo $answer['answer'] ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <?php if ($questionrow['other'] == 'Y'): ?>
                                        <div class="mb-3">
                                            <label class="col-12 form-label" for='other_<?php echo "{$scale_id}_{$language}" ?>'>
                                                <?php eT("Default value for option 'Other':") ?>
                                            </label>
                                            <div class="col-12">
                                                <input type='text' name='other_<?php echo "{$scale_id}_{$language}" ?>'
                                                       value='<?php echo $langopts[$language][$questionrow['type']]['Ydefaultvalue'] ?>' id='other_<?php echo "{$scale_id}_{$language}" ?>'>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            <?php endif; ?>
                            <?php /* If there are subquestions and no answerscales */ ?>
                            <?php if ($questionMetaData->answerscales == 0 && (int)$questionMetaData->subquestions > 0) : ?>
                                <?php for ($scale_id = 0; $scale_id < (int)$questionMetaData->subquestions; $scale_id++) : ?>
                                    <?php $opts = $langopts[$language][$questionrow['type']][$scale_id]; ?>
                                    <?php if ((int)$questionMetaData->subquestions > 1) : ?>
                                        <div class='header ui-widget-header'>
                                            <?php echo sprintf(gT('Default answer for scale %s:'), $scale_id) ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php switch ($questionrow['type']) {
                                        case Question::QT_L_LIST:
                                        case Question::QT_M_MULTIPLE_CHOICE:
                                        case Question::QT_O_LIST_WITH_COMMENT:
                                        case Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS:
                                        case Question::QT_EXCLAMATION_LIST_DROPDOWN:
                                            $inputStyle = 'enum';
                                            break;
                                        case Question::QT_K_MULTIPLE_NUMERICAL:
                                        case Question::QT_Q_MULTIPLE_SHORT_TEXT:
                                            $inputStyle = 'text';
                                            break;
                                    } ?>
                                    <?php if ($inputStyle == 'enum') : ?>
                                        <?php foreach ($opts['sqresult'] as $aSubquestion) : ?>
                                            <div class="mb-3">
                                                <label class="col-12 form-label" for='defaultanswerscale_<?php echo "{$scale_id}_{$language}_{$aSubquestion['qid']}" ?>'>
                                                    <?php echo "{$aSubquestion['title']}: " . flattenText($aSubquestion['question']) ?>
                                                </label>
                                                <div class="col-12">
                                                    <select class='form-select' name='defaultanswerscale_<?php echo "{$scale_id}_{$language}_{$aSubquestion['qid']}" ?>'
                                                            id='defaultanswerscale_<?php echo "{$scale_id}_{$language}_{$aSubquestion['qid']}" ?>'>
                                                        <?php foreach ($aSubquestion['options'] as $value => $label) : ?>
                                                            <option value="<?php echo $value ?>"<?php echo($value == $aSubquestion['defaultvalue'] ? ' selected="selected"' : ''); ?>>
                                                                <?php echo $label ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>

                                    <?php if ($inputStyle == 'text') : ?>
                                        <?php foreach ($opts['sqresult'] as $aSubquestion) : ?>
                                            <div class="mb-3">
                                                <label class="col-12 form-label" for='defaultanswerscale_<?php echo "{$scale_id}_{$language}_{$aSubquestion['qid']}" ?>'>
                                                    <?php echo "{$aSubquestion['title']}: " . flattenText($aSubquestion['question']) ?>
                                                </label>
                                                <div class="col-12">
                                                        <textarea class="form-control" cols='50' name='defaultanswerscale_<?php echo "{$scale_id}_{$language}_{$aSubquestion['qid']}" ?>'
                                                                  id='defaultanswerscale_<?php echo "{$scale_id}_{$language}_{$aSubquestion['qid']}" ?>'><?php echo $aSubquestion['defaultvalue'] ?></textarea>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            <?php endif; ?>
                            <?php if ($questionMetaData->answerscales == 0 && $questionMetaData->subquestions == 0) : ?>
                                <?php
                                /*
                                case 'D':
                                case 'N':
                                case 'S':
                                case 'T':
                                case 'U':*
                                */
                                /**
                                 * Call default value widget for yes/no question type
                                 * This is fast insert rewrite of this view follows
                                 */
                                $widgetOptions = [
                                    'language' => $language,
                                    'questionrow' => $questionrow,
                                    'langopts' => $langopts,
                                ];
                                $this->widget('application.views.admin.survey.Question.yesNo_defaultvalue_widget', ['widgetOptions' => $widgetOptions]);
                                ?>
                                <?php if ($questionrow['type'] != Question::QT_Y_YES_NO_RADIO): //temporary solution - until everything is move to widgets?>
                                    <div class="mb-3">
                                        <label class="col-12 form-label" for='defaultanswerscale_<?php echo "0_{$language}_0" ?>'>
                                            <?php eT("Default value:") ?>
                                        </label>
                                        <?php
                                            $defaultValue = DefaultValue::model()->findByAttributes(['qid' => $questionrow['qid']]);
                                            if($defaultValue !== null){
                                                $defaultValueLanguage = DefaultValueL10n::model()->findByAttributes(['dvid' => $defaultValue->dvid, 'language' => $language]);
                                                // TODO: Small bug when importing survey with default answer, and then adding a new language after.
                                                if ($defaultValueLanguage) {
                                                    $defaultValueLanguageText = $defaultValueLanguage->defaultvalue;
                                                } else {
                                                    $defaultValueLanguageText = '';
                                                }
                                            }else{
                                                $defaultValueLanguageText = '';
                                            }
                                        ?>
                                        <div class="col-12">
                                            <textarea <?php echo $hasUpdatePermission; ?>
                                                class="form-control"
                                                cols='50'
                                                name='defaultanswerscale_<?php echo "0_{$language}_0" ?>'
                                                id='defaultanswerscale_<?php echo "0_{$language}_0" ?>'><?php echo $defaultValueLanguageText;?></textarea>
                                        </div>
                                    </div>
                                <?php endif;  //temporary solution?>
                            <?php endif; ?>

                            <?php if ($language == $oSurvey->language && count($oSurvey->allLanguages) > 1) { ?>
                                <div class="mb-3">
                                    <label class=" form-label" for='samedefault'>
                                        <?php eT('Use same default value across languages:') ?>
                                    </label>
                                    <div class="">
                                        <input type='checkbox' name='samedefault' id='samedefault' <?php echo $questionrow['same_default'] ? 'checked="checked"' : '' ?> />
                                    </div>
                                </div>
                            <?php } ?>

                        </div>
                    <?php endforeach; ?>
                </div>
                <input type='hidden' id='action' name='action' value='updatedefaultvalues'/>
                <input type='hidden' id='sid' name='sid' value='<?php echo $surveyid ?>'/>
                <input type='hidden' id='gid' name='gid' value='<?php echo $gid ?>'/>
                <input type='hidden' id='qid' name='qid' value='<?php echo $qid ?>'/>
                <p><input class="d-none" type='submit' value='<?php eT('Save') ?>'/></p>
                <?php echo CHtml::endForm(); ?>
            </div>
        </div>
    </div>

<?php Yii::app()->getClientScript()->registerScript(
    "defaultValuesShowBar",
    "$('#questiongroupbarid').slideDown()",
    LSYii_ClientScript::POS_POSTSCRIPT
);
