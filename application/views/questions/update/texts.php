<?php
// This is an update view so we use PUT.
echo TbHtml::beginFormTb(TbHtml::FORM_LAYOUT_VERTICAL, ['questions/update', 'id' => $question->qid], 'put', []);

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
echo TbHtml::openTag('div', ['class' => 'pull-right btn-group']);
echo TbHtml::submitButton('Save texts', [
    'color' => 'primary',
    'class' => 'ajaxSubmit'
]);
echo TbHtml::closeTag('div');

echo TbHtml::endForm();