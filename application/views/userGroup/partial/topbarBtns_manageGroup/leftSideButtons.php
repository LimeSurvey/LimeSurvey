<?php

/** @var int $userGroupId */
/** @var bool $hasPermission */
if ($hasPermission) {
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'group-mail-button',
            'id' => 'group-mail-button',
            'text' => gT('Mail to all Members'),
            'icon' => 'ri-mail-send-fill',
            'link' => $this->createUrl("userGroup/mailToAllUsersInGroup/ugid/" . $userGroupId),
            'htmlOptions' => [
                'class' => 'btn btn-outline-secondary',
            ],
        ]
    );
}

if ($hasPermission) {
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'group-mail-button',
            'id' => 'group-mail-button',
            'text' => gT('Edit current user group'),
            'icon' => 'ri-pencil-fill',
            'link' => $this->createUrl("userGroup/edit/ugid/" . $userGroupId),
            'htmlOptions' => [
                'class' => 'btn btn-outline-secondary',
            ],
        ]
    );
}

if ($hasPermission) {
    $dataPost = json_encode(['ugid' => $userGroupId]);
    $this->widget(
        'ext.ButtonWidget.ButtonWidget',
        [
            'name' => 'group-mail-button',
            'id' => 'group-mail-button',
            'text' => gT('Delete current user group'),
            'icon' => 'ri-delete-bin-fill',
            'htmlOptions' => [
                'class' => 'btn btn-danger action_delete-group',
                'data-bs-toggle' => 'modal',
                'data-post-url' =>  $this->createUrl('userGroup/deleteGroup/'),
                'data-post-datas' => $dataPost,
                'data-message' => gt('Are you sure you want to delete this entry?'),
                'data-bs-target' => '#confirmation-modal',
                'data-btnclass' => 'btn btn-danger',
                'data-title' => gt('Delete group'),
                'data-btntext' => gt('Delete')
            ],
        ]
    );
}
