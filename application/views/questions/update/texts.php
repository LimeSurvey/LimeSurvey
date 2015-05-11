<div class="form-vertical">
<?php
foreach ($question->survey->languages as $language) {
    $tabs[] = [
        'label' => App()->locale->getLanguage($language),
        'active' => $language == $question->survey->language,
        'id' => "texts-$language",
        'content' => $this->renderPartial('update/textTab', ['question' => $question, 'language' => $language], true)
    ];
}
$this->widget(TbTabs::class, [
    'tabs' => $tabs
]);
?></div>