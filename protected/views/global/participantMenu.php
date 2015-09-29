<?php
$menu = [[ // Left side
    [
        'title' => gT('Summary'),
        'url' => ['participants/summary'],
        'icon' => 'info-sign',
    ], [
        'title' => gT('List'),
        'url' => ['participants/index'],
        'icon' => 'list',
    ], [
        'title' => gT('Import'),
        'url' => ['participants/import'],
        'icon' => 'import',
    ], [
        'title' => gT('Export'),
        'url' => ['participants/export'],
        'icon' => 'export',
    ], [
        'title' => gT('Settings'),
        'url' => ['participants/settings'],
        'icon' => 'wrench',
    ], [
        'title' => gT('Attribute management'),
        'url' => ['participants/manageAttributes'],
        'icon' => 'text-background',
    ], [
        'title' => gT('Share panel'),
        'url' => ['participants/share'],
        'icon' => 'share',
    ],

], [ // Right side

],
'brandLabel' => gT("ls\models\Participant database")];

$event = new PluginEvent('afterParticipantMenuLoad', $this);
$event->set('menu', $menu);
$event->dispatch();
return $event->get('menu');