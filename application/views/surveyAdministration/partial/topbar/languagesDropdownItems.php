<?php
/** @var array $surveyLanguages */
/** @var string $type */
?>

<?php foreach ($surveyLanguages as $languageCode => $languageName) : ?>
    <?php
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
    ?>
    <li>
        <a class="dropdown-item" target="_blank" href="<?= $link; ?>" >
            <?php echo $languageName; ?>
        </a>
    </li>
<?php endforeach; ?>
