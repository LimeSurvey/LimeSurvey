<div class="form-horizontal" id="progress">
<?php
echo TbHtml::progressBar(0, [
    'class' => 'readProgress',
]);

echo TbHtml::progressBar(0, [
    'class' => 'uploadProgress',
]);
echo TbHtml::customControlGroup(TbHtml::tag('div', [
    'class' => 'resourceUsage'
], ''), '', [
    'label' => gT("PHP Resource usage (memory / time)"),
    'formLayout' => TbHtml::FORM_LAYOUT_HORIZONTAL
]);

    echo Html::buttonGroup([
        [
            'label' => gT('Stop import'),
            'type' => Html::BUTTON_TYPE_HTML,
            'color' => 'danger',
            'class' => 'stop'
        ]
    ], [
        'class' => 'pull-right'
    ]);

?>
<script type="text/javascript">
    (function() {
        var $widget = $('#<?= $this->id; ?>');
        $widget.on('readProgress', function (e, progress) {
            $widget.find('.readProgress .progress-bar').css('width', progress + '%');
        });
        $widget.on('uploadProgress', function (e, progress, data) {
            console.log(progress);
            console.log(data);
            $resourceUsage = $widget.find('.resourceUsage');
            $widget.find('.uploadProgress .progress-bar').css('width', progress + '%');
            $('<div/>').addClass('memory').css('height', (data.memory * 100).toPrecision(2)).appendTo($resourceUsage);
            $('<div/>').addClass('time').css('height', (data.time * 100).toPrecision(2)).appendTo($resourceUsage);
            $resourceUsage.children().css('width', (100 / $resourceUsage.children().length) + '%');

        });
        $widget.on('complete', function (e) {
            $widget.find('.readProgress').css('width', '100%');
            console.log(arguments);
        });
        $widget.on('stopped', function (e) {
            $widget.find('.progress-bar').css('width', 0);
            $widget.find('.resourceUsage').empty();
        });
    })();


</script>