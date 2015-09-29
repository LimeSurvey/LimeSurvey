<?php
/** @var Survey $survey*/
use ls\models\Survey;

foreach ($survey->languages as $language) {
    $tabs[] = [
        'label' => App()->locale->getLanguage($language),
        'active' => $language == $survey->language,
        'id' => "texts-$language",
        'content' => $this->renderPartial('update/emailTab', ['survey' => $survey, 'language' => $language, 'form' => $form], true)
    ];
}
$this->widget(TbTabs::class, [
    'tabs' => $tabs
]);
?>