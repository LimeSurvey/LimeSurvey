<?php
/** @var bool $ownsAddParticipantsButton */

if (Permission::model()->hasGlobalPermission('participantpanel', 'read')) {
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => '',
            'text' => gT('Display CPDB participants'),
            'icon' => 'ri-list-check',
            'link' => $this->createUrl("admin/participants/sa/displayParticipants"),
            'htmlOptions' => [
                'class' => 'btn btn-outline-secondary',
            ],
        ]
    );
} elseif (
    Permission::model()->hasGlobalPermission('participantpanel', 'create')
    || ParticipantShare::model()->exists('share_uid = :userid', [':userid' => App()->user->id])
) {
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => '',
            'text' => gT('Display my CPDB participants'),
            'icon' => 'ri-list-check',
            'link' => $this->createUrl("admin/participants/sa/displayParticipants"),
            'htmlOptions' => [
                'class' => 'btn btn-outline-secondary',
            ],
        ]
    );
}

$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => '',
        'text' => gT("Summary"),
        'icon' => 'ri-list-unordered',
        'link' => $this->createUrl("admin/participants/sa/index"),
        'htmlOptions' => [
            'class' => 'btn btn-outline-secondary',
        ],
    ]
);

if (Permission::model()->hasGlobalPermission('participantpanel', 'import')) {
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => '',
            'text' => gT("Import"),
            'icon' => 'ri-download-2-fill',
            'link' => $this->createUrl("admin/participants/sa/importCSV"),
            'htmlOptions' => [
                'class' => 'btn btn-outline-secondary',
            ],
        ]
    );
}

if (Permission::model()->hasGlobalPermission('superadmin', 'read')) {
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => '',
            'text' => gT("Blocklist settings"),
            'icon' => 'ri-list-settings-line',
            'link' => $this->createUrl("admin/participants/sa/blacklistControl"),
            'htmlOptions' => [
                'class' => 'btn btn-outline-secondary',
            ],
        ]
    );
}

if (Permission::model()->hasGlobalPermission('participantpanel', 'read')) {
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => '',
            'text' => gT("Attributes"),
            'icon' => 'ri-price-tag-3-fill',
            'link' => $this->createUrl("admin/participants/sa/attributeControl"),
            'htmlOptions' => [
                'class' => 'btn btn-outline-secondary',
            ],
        ]
    );
}

$this->widget(
    'ext.ButtonWidget.ButtonWidget',
    [
        'name' => '',
        'text' => gT("Share panel"),
        'icon' => 'ri-share-forward-fill',
        'link' => $this->createUrl("admin/participants/sa/sharePanel"),
        'htmlOptions' => [
            'class' => 'btn btn-outline-secondary',
        ],
    ]
);

if (Permission::model()->hasGlobalPermission('participantpanel', 'export')) {
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'export',
            'id' => 'export',
            'text' => gT("Export all participants"),
            'icon' => 'ri-upload-2-fill',
            'htmlOptions' => [
                'class' => 'btn btn-outline-secondary',
            ],
        ]
    );
}

if (isset($ownsAddParticipantsButton) && ($ownsAddParticipantsButton)) {
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'addParticipantToCPP',
            'id' => 'addParticipantToCPP',
            'text' => gT("Add participant"),
            'icon' => 'ri-add-circle-fill',
            'htmlOptions' => [
                'class' => 'btn btn-outline-secondary',
            ],
        ]
    );
}
