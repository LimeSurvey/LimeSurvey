<?php

/*
 * Get user summary data.
 */

$overview = [
//    gT("Users") => 0,
    'surveyCount' => Survey::model()->count(),
    'activeSurveyCount' => Survey::model()->active()->count(),
//    gT("Deactivated result tables") => 0,
//    gT("Active token tables") => 0
];
$this->widget(WhDetailView::class, [
    'data' => $overview,
    'attributes' => [
        [
            'name' => 'surveyCount',
            'label' => gT("Surveys"),
        ],
        [
            'name' => 'activeSurveyCount',
            'label' => gT("Active surveys")
        ],
        [
            'label' => gT('Environment'),
            'type' => 'raw',
            'visible' => App()->user->checkAccess('superadmin'),
            'value' => TbHtml::link(gT('Show PHPInfo'), ['admin/globalsettings', 'sa'=>'showphpinfo'])
        ],
        [
            'label' => gT('Current version'),
            'value' => App()->params['version']
        ],
        [
            'label' => gT('Check for updates'),
            'type' => 'raw',
            'value' => TbHtml::link(gT('Check for updates'), ['upgrade/index'])
        ],
    ]
]);
//if(Yii::app()->getConfig('iFileUploadTotalSpaceMB')>0)
//{
//    $fUsed=calculateTotalFileUploadUsage();
//    $sContentSummary[gT("Used/free space for file uploads")] = sprintf('%01.2F',$fUsed)." MB / ".sprintf('%01.2F',Yii::app()->getConfig('iFileUploadTotalSpaceMB')-$fUsed);
//}
//
//
//if (App()->user->checkAccess('superadmin')) {
//    $sContentSummary['phpinfo'] = [
//        'type'=>'link',
//        'label'=> gT('Show PHPInfo'),
//        'link'=> ['admin/globalsettings', 'sa'=>'showphpinfo'],
//        'text'=>gT('PHPInfo'),
//        'htmlOptions' => ['target' => '_blank']
//    ];
//}
//echo $sContentSummary;