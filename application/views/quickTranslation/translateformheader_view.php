<?php

/* @var $viewData array */

extract($viewData ?? []);

/* @var $surveyid int */
/* @var $tolang string */
/* @var $baselang string */
/* @var $baselangdesc string */
/* @var $tolangdesc string */
/* @var $bReadOnly bool */
/* @var $canPreview bool */
/* @var $fieldCounts int[] number of records per field type */
/* @var $structure array see QuickTranslation::getSurveyStructure() */

$hasAutoTranslate = (bool) App()->getConfig('googletranslateapikey');
$commonData = [
    'baselang' => $baselang,
    'tolang' => $tolang,
    'tolangdesc' => $tolangdesc,
    'surveyid' => $surveyid,
    'canPreview' => $canPreview,
    'hasAutoTranslate' => $hasAutoTranslate,
    'bReadOnly' => $bReadOnly,
];
$isEmpty = empty($structure['surveyTexts']) && empty($structure['groups']) && empty($structure['emails']);
?>
<div id="translationloading" style="width: 100%; font-weight: bold; color: #000; text-align: center;">
    <br/>
    <?php eT("Loading translations..."); ?><br/><br/>
</div>

<?php echo CHtml::form(array("quickTranslation/index/surveyid/{$surveyid}/lang/{$tolang}"), 'post', array('name' => 'translateform', 'id' => 'translateform')); ?>
<input type='hidden' name='sid' value='<?php echo $surveyid; ?>'/>
<input type='hidden' name='action' value='translate'/>
<input type='hidden' name='actionvalue' value='translateSave'/>
<input type='hidden' name='tolang' value='<?php echo $tolang; ?>'/>
<input type='hidden' name='baselang' value='<?php echo $baselang; ?>'/>
<?php foreach ($fieldCounts as $type => $count) : ?>
    <input type='hidden' name='<?php echo $type; ?>_size' value='<?php echo $count - 1; ?>'/>
<?php endforeach; ?>

<script type="text/javascript">
    sGoogleApiError = "<?php eT("There was an error using the Google API.");?>";
    sDetailedError = "<?php eT("Error message: %s");?>";
    translateJsonUrl = "<?php echo $this->createUrl("quickTranslation/ajaxtranslategoogleapi", ['surveyid' => $surveyid]); ?>";
</script>

<?php
Yii::app()->loadHelper('admin.htmleditor');
echo PrepareEditorScript(true, Yii::app()->getController());
?>
<span id="quick-translation-auto-translate-unavailable" class="visually-hidden"><?php eT("Auto translation needs a Google Translate API key in the global settings."); ?></span>
<span id="quick-translation-target-language" class="visually-hidden"><?php echo CHtml::encode(sprintf(gT("Translation to %s"), $tolangdesc)); ?></span>

<div id="quick-translation" class="quick-translation"
     <?php if (!$bReadOnly) : ?>data-save-url="<?php echo $this->createUrl("quickTranslation/ajaxSave", ['surveyid' => $surveyid]); ?>"<?php endif; ?>
     data-save-error="<?php eT('An error occurred while saving.'); ?>">
    <?php if ($isEmpty) : ?>
        <p><?php eT("Nothing to translate on this page"); ?></p>
    <?php else : ?>
    <p class="quick-translation-languages">
        <?php echo CHtml::encode(sprintf(gT("From %s to %s"), $baselangdesc, $tolangdesc)); ?>
        <?php if ($hasAutoTranslate) : ?>
            <img src='<?php echo Yii::app()->getConfig("adminimageurl"); ?>/ajax-loader.gif' style='display: none'
                 class='ajax-loader' alt='<?php eT("Loading..."); ?>'/>
        <?php endif; ?>
    </p>
    <?php
    $surveyUntranslatedCount = $structure['surveyTextsUntranslatedCount']
        + array_sum(array_column($structure['groups'], 'untranslatedCount'));
    $tabs = [
        'survey' => [gT("Survey"), $surveyUntranslatedCount],
        'emails' => [gT("Email templates"), $structure['emailsUntranslatedCount']],
    ];
    ?>
    <ul class="nav nav-tabs mb-3" id="quick-translation-tabs" role="tablist" aria-label="<?php eT('Translation sections'); ?>">
        <?php foreach ($tabs as $tabName => [$tabTitle, $tabUntranslatedCount]) :
            $isActive = $tabName === 'survey'; ?>
            <li class="nav-item" role="presentation">
                <a class="nav-link<?php echo $isActive ? ' active' : ''; ?>" id="quick-translation-tab-<?php echo $tabName; ?>"
                   data-bs-toggle="tab" href="#quick-translation-pane-<?php echo $tabName; ?>" role="tab"
                   aria-controls="quick-translation-pane-<?php echo $tabName; ?>"
                   aria-selected="<?php echo $isActive ? 'true' : 'false'; ?>" tabindex="<?php echo $isActive ? '0' : '-1'; ?>">
                    <?php echo CHtml::encode($tabTitle); ?>
                    <?php if ($tabUntranslatedCount > 0) : ?>
                        <span class="quick-translation-count" aria-hidden="true"><?php echo (int) $tabUntranslatedCount; ?></span>
                        <span class="visually-hidden"><?php echo ngT("{n} untranslated text|{n} untranslated texts", $tabUntranslatedCount); ?></span>
                    <?php endif; ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
    <div class="tab-content">
    <div class="tab-pane fade show active" id="quick-translation-pane-survey" role="tabpanel" aria-labelledby="quick-translation-tab-survey">
    <?php if (empty($structure['surveyTexts']) && empty($structure['groups'])) : ?>
        <p><?php eT("Nothing to translate on this page"); ?></p>
    <?php else : ?>
    <div class="row">
        <div class="col-lg-3">
            <nav class="quick-translation-nav" aria-labelledby="quick-translation-nav-title">
                <h3 class="h6" id="quick-translation-nav-title"><?php eT("Survey structure"); ?></h3>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="quick-translation-only-untranslated" aria-describedby="quick-translation-only-untranslated-hint">
                    <label class="form-check-label" for="quick-translation-only-untranslated"><?php eT("Show only untranslated texts"); ?></label>
                </div>
                <p class="quick-translation-hint" id="quick-translation-only-untranslated-hint"><?php eT("Texts are untranslated if the translation is empty or the same as the source text when the page was loaded."); ?></p>
                <div class="d-flex gap-2 mb-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="quick-translation-expand-all"><?php eT("Expand all"); ?></button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="quick-translation-collapse-all"><?php eT("Collapse all"); ?></button>
                </div>
                <ul class="quick-translation-nav-list">
                    <?php if (!empty($structure['surveyTexts'])) : ?>
                        <?php $this->renderPartial('translateNavItem_view', [
                            'href' => '#quick-translation-survey-texts',
                            'text' => gT("Survey texts"),
                            'untranslatedCount' => $structure['surveyTextsUntranslatedCount'],
                        ]); ?>
                    <?php endif; ?>
                    <?php foreach ($structure['groups'] as $group) : ?>
                        <?php $this->renderPartial('translateNavItem_view', [
                            'href' => '#quick-translation-group-' . $group['gid'],
                            'text' => $group['name'] !== '' ? $group['name'] : $group['gid'],
                            'untranslatedCount' => $group['untranslatedCount'],
                            'children' => array_map(function ($question) {
                                // Like the question explorer: [code] › question text
                                $questionText = flattenText($question['text'], false, true);
                                return [
                                    'href' => '#quick-translation-question-' . $question['qid'],
                                    'text' => '[' . $question['code'] . '] › ' . ellipsize($questionText, 30, 1, '…'),
                                    'title' => $questionText,
                                    'untranslatedCount' => $question['untranslatedCount'],
                                ];
                            }, $group['questions']),
                        ]); ?>
                    <?php endforeach; ?>
                </ul>
            </nav>
        </div>
        <div class="col-lg-9">
            <?php if (!empty($structure['surveyTexts'])) : ?>
                <?php $this->renderPartial('translateSection_view', array_merge($commonData, [
                    'id' => 'quick-translation-survey-texts',
                    'title' => gT("Survey texts"),
                    'untranslatedCount' => $structure['surveyTextsUntranslatedCount'],
                    'fields' => $structure['surveyTexts'],
                ])); ?>
            <?php endif; ?>

            <?php foreach ($structure['groups'] as $group) : ?>
                <?php $this->renderPartial('translateSection_view', array_merge($commonData, [
                    'id' => 'quick-translation-group-' . $group['gid'],
                    'title' => $group['name'] !== '' ? $group['name'] : $group['gid'],
                    'untranslatedCount' => $group['untranslatedCount'],
                    'fields' => $group['fields'],
                    'questions' => $group['questions'],
                ])); ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    </div>
    <div class="tab-pane fade" id="quick-translation-pane-emails" role="tabpanel" aria-labelledby="quick-translation-tab-emails">
        <?php if (empty($structure['emails'])) : ?>
            <p><?php eT("Nothing to translate on this page"); ?></p>
        <?php endif; ?>
        <?php foreach ($structure['emails'] as $email) : ?>
            <?php $this->renderPartial('translateSection_view', array_merge($commonData, [
                'id' => 'quick-translation-email-' . $email['type'],
                'title' => $email['title'],
                'untranslatedCount' => $email['untranslatedCount'],
                'fields' => $email['fields'],
            ])); ?>
        <?php endforeach; ?>
    </div>
    </div>
    <?php endif; ?>
    <p>
        <input type='submit' class='standardbtn d-none' value='<?php eT("Save");?>' <?php if ($bReadOnly) {
            ?>disabled='disabled'<?php
                                                               }?>/>
    </p>
</div>
<?php
    echo CHtml::endForm();
?>
