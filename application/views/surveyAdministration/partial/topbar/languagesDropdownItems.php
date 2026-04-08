<?php
/** @var array $surveyLanguages */
/** @var string $type */
?>

<?php
    $allLanguageOptions = [];
    foreach ($surveyLanguages as $languageCode => $languageName) {
        switch ($type) {
            case 'questionGroup':
                $link = Yii::App()->createUrl("survey/index/action/previewgroup/sid/{$sid}/gid/{$gid}/lang/" . $languageCode);
                break;
            case 'question':
                $link = Yii::App()->createUrl("survey/index/action/previewquestion/sid/{$sid}/gid/{$gid}/qid/{$qid}/lang/{$languageCode}");
                break;
            default:
                $link = Yii::App()->createUrl("survey/index",array('sid'=>$sid,'newtest'=>"Y",'lang'=>$languageCode));
        }

        $allLanguageOptions[$languageCode] = [
            'href' => $link,
            'text' => $languageName,
            'target' => '_blank'
        ];
    }
?>

<?php if (count($allLanguageOptions) <= 5) : ?>
    <?php foreach ($allLanguageOptions as $languageCode => $languageData) : ?>
        <li>
            <a class="dropdown-item" target="_blank" href="<?= $languageData['href'] ?>" >
                <?= $languageData['text'] ?>
            </a>
        </li>
    <?php endforeach; ?>
<?php else: ?>
    <?php
        $firstLanguage = array_shift($allLanguageOptions);
    ?>
    <li>
        <a class="dropdown-item" target="_blank" href="<?= $firstLanguage['href'] ?>" >
            <?= $firstLanguage['text'] ?>
        </a>
    </li>
    <?php
        $languagesModal = $this->widget('ext.OptionsModalWidget.OptionsModalWidget', [
            'modalTitle' => gT("Select language"),
            'options' => $allLanguageOptions
        ]);
        $languagesModalId = $languagesModal->getModalId();
    ?>
    <li>
        <a class="dropdown-item" href="#<?= $languagesModalId ?>" data-bs-toggle="modal"><?= gT("Other languages") ?></a>
    </li>
<?php endif; ?>
