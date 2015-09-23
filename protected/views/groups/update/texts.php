<div class="form-vertical">
<?php
foreach ($group->survey->languages as $language) {
    $tabs[] = [
        'label' => App()->locale->getLanguage($language),
        'active' => $language == $group->survey->language,
        'id' => "texts-$language",
        'content' => $this->renderPartial('update/textTab', ['group' => $group, 'language' => $language], true)
    ];
}
$this->widget(TbTabs::class, [
    'tabs' => $tabs
]);
?></div>