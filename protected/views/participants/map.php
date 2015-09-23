<div class="row form-vertical" id="mapForm">
<?php
/** @var CActiveDataProvider $attributes */

echo TbHtml::openTag('div', ['class' => 'clearfix col-md-offset-2 col-md-8']);
    echo TbHtml::openTag('div', ['class' => 'col-md-4']);
        echo TbHtml::tag('h3', [], gT("Unmapped / ignored columns"));
        // Placeholder
        echo TbHtml::openTag('div', ['id' => 'columnPlaceholder', 'style' => 'display: none;']);
        echo TbHtml::textFieldControlGroup("Column", '', [
            'label' => '',
            'placeholder' => gT('Attribute name'),
            'formLayout' => TbHtml::FORM_LAYOUT_HORIZONTAL,
            'labelWidthClass' => 'col-sm-4',
            'controlWidthClass' => 'col-sm-8',
        ]);
        echo TbHtml::closeTag('div');

        echo TbHtml::tag('div', ['class' => 'sortable hide-inputs form-control', 'id' => 'csvColumns'], '');
    echo TbHtml::closeTag('div');

    echo TbHtml::openTag('div', ['class' => 'col-md-4']);
        echo TbHtml::tag('h3', [], 'Create new attributes below:');
        echo TbHtml::tag('div', ['class' => 'sortable form-control', 'data-required' => 1], '');
    echo TbHtml::closeTag('div');

    echo TbHtml::openTag('div', ['class' => 'col-md-4', 'id' => 'existingAttributes']);
        echo TbHtml::tag('h3', [], 'Map to existing attributes:');
        $participant = new Participant();
        foreach($participant->getSafeAttributeNames() as $name) {
            echo TbHtml::customControlGroup(TbHtml::tag('div', [
                'class' => 'sortable1 form-control hide-inputs',
                'data-attribute' => $name
            ], ''), $name, [
                'label' => $participant->getAttributeLabel($name),
                'formLayout' => TbHtml::FORM_LAYOUT_HORIZONTAL,
                'labelWidthClass' => 'col-sm-4',
                'controlWidthClass' => 'col-sm-8',
            ]);
        }
    echo TbHtml::closeTag('div');
echo TbHtml::closeTag('div');
App()->clientScript->registerScript('sort', '
$(".sortable").sortable({
    connectWith: ".sortable, .sortable1",
    receive: function(event, ui) {
        ui.item.find("input").val($(this).attr("data-attribute"));
        if ($(this).attr("data-required") == 1) {
            ui.item.find("input").attr("required", "required");
        } else {
            ui.item.find("input").attr("required", "");
        }
    }
});
$(".sortable1").sortable({
    connectWith: ".sortable, .sortable1",
    receive: function(event, ui) {
        if ($(this).children().length > 1) {
            $(ui.sender).sortable("cancel");
        } else {
           ui.item.find("input").val($(this).attr("data-attribute"));
        }
    }
});

');
//var_dump($firstRow);
?>
</div>