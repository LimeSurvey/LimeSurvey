<div class="row">
<div class="col-md-6 col-md-offset-3">
<?php
    echo TbHtml::tag('h1', [], 'The following new versions are available:');
    $versions = array_reverse($versions);
    foreach($versions as $i => $version) {
        echo TbHtml::linkButton('Show details for version: ' . $version, [
            'url' => App()->createUrl('upgrade/info', ['version' => $version, 'check' => false]),
            'style' => 'display: block; margin-bottom: 20px;',
            'color' => ($i == 0) ? TbHtml::BUTTON_COLOR_PRIMARY : TbHtml::BUTTON_COLOR_DEFAULT
        ]);
    }
?></div></div>