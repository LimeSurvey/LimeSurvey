<div class="row">
    <div class="col-md-6 col-md-offset-3">
        <?=TbHtml::tag('h1', [], "Update from " . App()->params['version'] . " to {$version}"); ?>
    </div>
    <div class="col-md-3 col-md-offset-3">
<?php
/* @var \SamIT\AutoUpdater\Executor\PreUpdate $preUpdate */
echo TbHtml::tag('h3', [], 'Steps');
echo TbHtml::openTag('table', ['style' => 'display:block;', 'class' => 'table']);
echo TbHtml::openTag('tr');
echo TbHtml::tag('th', ['style' => 'width: 15%;'], '#');
echo TbHtml::tag('th', ['style' => 'width: 70%;'], 'Description');
echo TbHtml::tag('th', ['style' => 'width: 15%;'], 'Status');
echo TbHtml::closeTag('tr');

$steps = [
    [
        'name' => 'precheck',
        'description' => 'Get & run the pre-update package.',
        'action' => ['upgrade/precheck', 'version' => $version]
    ],
    [
        'name' => 'download',
        'description' => 'Get the update package.',
        'action' => ['upgrade/download', 'version' => $version]
    ],
    [
        'name' => 'upgrade',
        'description' => 'Run the update package.',
        'action' => ['upgrade/execute', 'version' => $version]
    ],
    
];
echo TbHtml::openTag('tbody');
foreach($steps as $i => $step) {
    echo TbHtml::openTag('tr', ['class' => "disabled", 'id' => "step-{$step['name']}"]);
    echo TbHtml::tag('td', [], $i + 1);
    echo TbHtml::tag('td', [], $step['description']);
    echo TbHtml::tag('td', [], TbHtml::ajaxButton(TbHtml::icon(TbHtml::ICON_PLAY), $step['action'], ['success' => 'js:upgradeEvent']));
    echo TbHtml::closeTag('tr');
}
echo TbHtml::closeTag('tbody');        
echo TbHtml::closeTag('table');

//echo CHtml::tag('pre', [], "Change Log: " . implode("\n", $preUpdate->getChangeLog()));
//echo CHtml::tag('pre', [], "Changed files: " . count($preUpdate->getChangedFiles()));
//echo CHtml::tag('pre', [], "Created files: " . count($preUpdate->getCreatedFiles()));
//echo CHtml::tag('pre', [], "Removed files: " . count($preUpdate->getRemovedFiles()));
//echo TbHtml::well(implode('<br>', $preUpdate->getMessages()));
//echo TbHtml::buttonGroup([
//    [
//        'url' => App()->createUrl('upgrade/info', ['version' => $preUpdate->getToVersion(), 'check' => true]),
//        'label' => $check ? 'Re-run pre-check' : 'Run pre-check',
//        'color' => $preCheckSuccess ? TbHtml::BUTTON_COLOR_DEFAULT : TbHtml::BUTTON_COLOR_PRIMARY
//    ],
//    [
//        'label' => 'Upgrade to version ' . $preUpdate->getToVersion(),
//        'visible' => $preCheckSuccess,
//        'confirm' => 'Starting the upgrade will put limesurvey in maintenance mode',
//        'url' => App()->createUrl('upgrade/download', ['version' => $preUpdate->getToVersion()]),
//        'color' => TbHtml::BUTTON_COLOR_DANGER
//    ]
//]);
?></div><div class="col-md-3"><?php
    echo TbHtml::tag('h3', [], 'Details');
    echo TbHtml::tag('div', ['id' => 'details', 'style' => 'white-space: pre;']);

        
        
?></div></div>
<script>
function upgradeEvent(json) {
//    debugger;
    if (json.success) {
        $('#step-' + json.step).addClass('success');
    } else {
        $('#step-' + json.step).addClass('danger');
    }
    
        var contents = '';
        for (var i = 0; i < json.messages.length; i++) {
            contents += '<li>' + json.messages[i] + '</li>';
        }
        $('#details').html(contents);
}

</script>