<div class="col-md-12 form-horizontal">
    <div class="col-md-<?= $this->enableFieldCreation ? 4 : 6; ?>">
        <?php
        echo TbHtml::tag('h3', [], gT("Unmapped / ignored columns"));
        // Placeholder

        echo TbHtml::textFieldControlGroup("", '', [

            'label' => 'Placeholder',
            'placeholder' => gT('Attribute name'),
            'formLayout' => TbHtml::FORM_LAYOUT_HORIZONTAL,
            'labelWidthClass' => 'col-sm-4',
            'controlWidthClass' => 'col-sm-8',
            'groupOptions' => [
                'class' => 'placeholder csvColumn'
            ]
        ]);

        echo TbHtml::tag('div', ['class' => 'sortable hide-inputs form-control csvColumns'], '');

        ?>


    </div>
    <?php
    /** @var CsvImportWidget $this */
    if ($this->enableFieldCreation) {
        echo TbHtml::openTag('div', ['class' => 'col-md-4']);
        echo TbHtml::tag('h3', [], 'Create new attributes below:');
        echo TbHtml::tag('div', ['class' => 'sortable form-control', 'data-required' => 1], '');
        echo TbHtml::closeTag('div');
    }
    ?>
    <div class="col-md-<?= $this->enableFieldCreation ? 4 : 6; ?> hide-inputs" id="existingAttributes">
        <?php
        echo TbHtml::tag('h3', [], 'Map to existing attributes:');
        /** @var CsvImportWidget $this */
        foreach ($this->attributes as $attribute) {
            echo TbHtml::customControlGroup(TbHtml::tag('div', [
                    'class' => 'sortable1 form-control',
                    'data-attribute' => $attribute
                ], ''), $attribute, [
                    'label' => $this->model->getAttributeLabel($attribute),
                    'formLayout' => TbHtml::FORM_LAYOUT_HORIZONTAL,
                    'labelWidthClass' => 'col-sm-4',
                    'controlWidthClass' => 'col-sm-8',
                ]
            );
        }
        ?>
    </div>

    <?php

    echo Html::buttonGroup([
        [
            'label' => gT('Import'),
            'color' => 'primary',
            'class' => 'start'
        ]
    ], [
        'class' => 'pull-right'
    ]);

    ?>
</div>
<?php

$cs = App()->clientScript;
$cs->registerScript('sort', '
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
});');

