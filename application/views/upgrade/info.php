<div class="row">
    <div class="col-md-6 col-md-offset-3">
        <?=TbHtml::tag('h1', [], "Update from " . App()->params['version'] . " to {$version}"); ?>
    </div>
    <div class="col-md-3 col-md-offset-3">
<?php
/* @var \SamIT\AutoUpdater\Executor\PreUpdate $preUpdate */
echo TbHtml::tag('h3', [], 'Steps');
echo TbHtml::openTag('table', ['style' => 'display:block;', 'class' => 'table upgrade']);
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
    echo TbHtml::openTag('tr', ['class' => $i > 0 ? "disabled" : "", 'id' => "step-{$step['name']}"]);
    echo TbHtml::tag('td', [], $i + 1);
    echo TbHtml::tag('td', [], $step['description']);
    echo TbHtml::tag('td', [], TbHtml::ajaxButton(TbHtml::icon(TbHtml::ICON_PLAY), $step['action'], ['success' => 'js:upgradeEvent']));
    echo TbHtml::closeTag('tr');
}
echo TbHtml::closeTag('tbody');        
echo TbHtml::closeTag('table');

?></div><div class="col-md-3"><?php
    echo TbHtml::tag('h3', [], 'Details');
    echo TbHtml::tag('div', ['id' => 'details', 'style' => 'white-space: pre;']);

        
        
?></div></div>
<script>
function upgradeEvent(json) {
//    debugger;
    if (json.success) {
//        debugger;
        $('#step-' + json.step).addClass('success');
        $('#step-' + json.step).removeClass('danger');
        $('#step-' + json.step).next().removeClass('disabled');
    } else {
        $('#step-' + json.step).addClass('danger');
    }
    
    var contents = '';
    if (typeof json.changeLog != 'undefined' && json.changeLog.length > 0) {
        contents += 'Changes in this version:';
        contents += '<ul>';
        for (var i = 0; i < json.changeLog.length; i++) {
            contents += '<li>' + json.changeLog[i] + '</li>';
        }
        contents += '</ul>';
    }
    contents += 'Upgrade step results:';
    contents += '<ul>';
    for (var i = 0; i < json.messages.length; i++) {
        contents += '<li>' + json.messages[i] + '</li>';
    }
    contents += '</ul>';
    $('#details').html(contents);
}

</script>