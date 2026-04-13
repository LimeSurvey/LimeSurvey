<?php
if ($exist) {
    if ($hasResponsesUpdatePermission && isset($rlanguage)) {
        $this->widget(
            'ext.ButtonWidget.ButtonWidget',
            [
                'name' => 'boxes-close-button',
                'id' => 'boxes-close-button',
                'text' => gT('Edit this entry'),
                'icon' => 'ri-pencil-fill text-success',
                'link' => Yii::App()->createUrl("admin/dataentry/sa/editdata/subaction/edit/surveyid/{$surveyid}/id/{$id}/lang/$rlanguage"),
                'htmlOptions' => [
                    'class' => 'btn btn-outline-secondary',
                ],
            ]
        );
    }
    ?>
    <?php if ($hasResponsesDeletePermission && isset($rlanguage)) {
        $confirmQuestion = gT("Are you sure you want to delete this entry?", "js");
        $deleteLink = convertGETtoPOST(Yii::App()->createUrl(
            "admin/dataentry/sa/delete/",
            ['id' => $id, 'sid' => $surveyid]
        ));
        $this->widget(
            'ext.ButtonWidget.ButtonWidget',
            [
                'name' => 'boxes-close-button',
                'id' => 'boxes-close-button',
                'text' => gT('Delete this entry'),
                'icon' => 'ri-delete-bin-fill text-danger',
                'link' => '',
                'htmlOptions' => [
                    'class' => 'btn btn-outline-secondary',
                    'onclick' => "if (confirm('$confirmQuestion')) { $deleteLink}"
                ],
            ]
        );
    }
    if ($bHasFile) {
        $this->widget(
            'ext.ButtonWidget.ButtonWidget',
            [
                'name' => 'boxes-close-button',
                'id' => 'boxes-close-button',
                'text' => gT('Download files'),
                'icon' => 'ri-download-fill',
                'link' => Yii::app()->createUrl(
                    "responses/downloadfiles",
                    ["surveyId" => $surveyid,
                        "responseIds" => $id]
                ),
                'htmlOptions' => [
                    'class' => 'btn btn-outline-secondary',
                ],
            ]
        );
    }

    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'boxes-close-button',
            'id' => 'boxes-close-button',
            'text' => gT('Export this response'),
            'icon' => 'ri-download-fill downloadfile',
            'link' => Yii::App()->createUrl("admin/export/sa/exportresults/surveyid/$surveyid/id/$id"),
            'htmlOptions' => [
                'class' => 'btn btn-outline-secondary',
            ],
        ]
    );
}

$disablePrevious = '';
if (!$previous) {
    $disablePrevious = 'disabled';
}

$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'export-response',
        'id' => 'export-response',
        'text' => gT('Show previous...'),
        'icon' => 'ri-arrow-left-circle-fill',
        'link' => Yii::App()->createUrl("responses/view/", ['surveyId' => $surveyid, 'id' => $previous]),
        'htmlOptions' => [
            'class' => "btn btn-outline-secondary $disablePrevious",
        ],
    ]
);

$disableNext = '';
if (!$next) {
    $disableNext =  'disabled';
}

$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'export-response',
        'id' => 'export-response',
        'text' => gT('Show next...'),
        'icon' => 'ri-arrow-right-circle-fill',
        'link' => Yii::App()->createUrl("responses/view/", ['surveyId' => $surveyid, 'id' => $next]),
        'htmlOptions' => [
            'class' => "btn btn-outline-secondary $disableNext",
        ],
    ]
);

$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => 'export-response',
        'id' => 'export-response',
        'text' => gT('Close'),
        'icon' => 'ri-close-fill',
        'link' => $closeUrl,
        'htmlOptions' => [
            'class' => "btn btn-danger",
        ],
    ]
);
