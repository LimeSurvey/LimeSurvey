<?php

 /* @var string $editorUrl **/
 /* @var bool   $enableEditorButton **/

// new question editor
if (isset($editorEnabled) && $editorEnabled && $editorUrl) {
    $disabled = '';
    $tooltip = '';
    if (!$enableEditorButton) {
        $disabled = 'disabled';
        $tooltip = gT('The new question editor is only available for surveys using the FruityTwentyThree theme.');
    }
    ?>
    <span data-bs-toggle="tooltip" data-bs-original-title="<?=$tooltip?>">
        <?php
        $this->widget(
            'ext.ButtonWidget.ButtonWidget',
            [
                'name' => 'editor-link-button',
                'id' => 'editor-link-button',
                'text' => gT('Open in GititSurvey editor'),
                'link' => '',
                'htmlOptions' => [
                    'class' => 'btn btn-info',
                    'role' => 'button',
                    'disabled' => $disabled,
                    'data-url' => $editorUrl,
                ],
            ]
        );
        ?>
    </span>
    <?php
}
?>
