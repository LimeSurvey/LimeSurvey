<?php
/**
 * Web Installer Sidebar (Progressbar and Step-Listing) Viewscript
 */
?>
<h2 class="maintitle"><?php eT("Progress"); ?></h2>
<p><?php printf(gT("%s%% completed"), $this->progress); ?></p>
<?php
Yii::app()->bootstrap->init();
echo TbHtml::animatedProgressBar($this->progress, ['color' => TbHtml::PROGRESS_COLOR_SUCCESS]);

$steps = [
    'index' => gT("Welcome"),
    'license' => gT('License'),
    'precheck' => gT("Pre-installation check"),
    'config' => gT('Database Configuration'),
//    'dbconfig' => gT('Database settings'),
    'optional' => gT('Settings')
];
echo CHtml::openTag('ol');
foreach($steps as $name => $title) {
    echo CHtml::tag('li', [
        'class' => $this->action->id == $name ? 'on' : ''
    ], $title);
}
echo CHtml::closeTag('ol');
?>