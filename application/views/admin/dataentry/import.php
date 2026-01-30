<?php

/**
 * @var Survey $survey
 * @var array $settings
 */

?>
<div class="side-body">
    <h3><?php eT("Import responses from an archived reponse table"); ?></h3>
    <div class="row">
        <div class="col-12 content-right">
            <?php
            $this->widget('ext.SettingsWidget.SettingsWidget', [
                'settings' => $settings,
                'method'   => 'post',
                'buttons'  => [
                    gT('Import responses') => [
                        'name'  => 'ok',
                        'class' => ['d-none']
                    ],
                    gT('Cancel')           => [
                        'type'  => 'link',
                        'class' => ['d-none'],
                        'href'  => App()->createUrl('plugins/index')
                    ]
                ]
            ]);

            $message = gT("Please be aware that tables including encryption should not be restored if they have been created in LimeSurvey 4 before version 4.6.1");
            $this->widget('ext.AlertWidget.AlertWidget', [
                'text' => $message,
                'type' => 'info',
            ]);
            $message = gT(
                "You can import all old responses that are compatible with your current survey. Compatibility is determined by comparing column types and names, the ID field is always ignored."
            );
            $message .= "<p></p>";
            $message .= "<p>" . gT("Using type coercion may break your data; use with care or not at all if possible.") . "</p>";
            $message .= "<p>" . gT("Currently we detect and handle the following changes:") . "</p>";
            $list = [
                gT("Question is moved to another group (result is imported correctly)."),
                gT("Question is removed from target (result is ignored)."),
                gT("Question is added to target (result is set to database default value).")
            ];
            $message .= "<ul>";
            foreach ($list as $item) {
                $message .= CHtml::tag('li', [], $item);
            }
            $message .= "</ul>";
            $this->widget('ext.AlertWidget.AlertWidget', [
                'text' => $message,
                'type' => 'danger',
            ]);
            ?>
        </div>
    </div>
</div>
