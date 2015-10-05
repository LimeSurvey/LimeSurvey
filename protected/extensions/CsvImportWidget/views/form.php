<div class="form-horizontal">
    <div class="col-md-3">
        <?php
        /** @var CActiveDataProvider $attributes */
        App()->clientScript->registerPackage('papaparse');
        echo TbHtml::fileFieldControlGroup('file', null, [
            'label' => gT("CSV File"),
            'required' => true,
            'formLayout' => TbHtml::FORM_LAYOUT_HORIZONTAL,
            'labelWidthClass' => 'col-sm-6',
            'controlWidthClass' => 'col-sm-6',
        ]);

        echo TbHtml::dropDownListControlGroup('encoding', '', aEncodingsArray(), [
            'empty' => gT("Automatic"),
            'label' => 'File encoding',
            'formLayout' => TbHtml::FORM_LAYOUT_HORIZONTAL,
            'labelWidthClass' => 'col-sm-6',
            'controlWidthClass' => 'col-sm-6',
        ]);
        echo TbHtml::dropDownListControlGroup('separator', '', [
            "," => gT("Comma"),
            ";" => gT("Semicolon"),
            "\t" => gT("Tab"),
        ], [
            'empty' => gT("Automatic"),
            'label' => gT("Separator"),
            'formLayout' => TbHtml::FORM_LAYOUT_HORIZONTAL,
            'labelWidthClass' => 'col-sm-6',
            'controlWidthClass' => 'col-sm-6',
        ]);

        echo TbHtml::numberFieldControlGroup('batchSize', 1000, [
            'label' => gT("Batch size for uploading"),
            'required' => true,
            'formLayout' => TbHtml::FORM_LAYOUT_HORIZONTAL,
            'labelWidthClass' => 'col-sm-6',
            'controlWidthClass' => 'col-sm-6',
//            'help' => gT("Bigger chunks will give less frequent status updates but have a (slightly) better performance."),

        ]);
        echo TbHtml::numberFieldControlGroup('querySize', 250, [
            'label' => gT("Batch size for queries"),
            'required' => true,
            'formLayout' => TbHtml::FORM_LAYOUT_HORIZONTAL,
            'labelWidthClass' => 'col-sm-6',
            'controlWidthClass' => 'col-sm-6',
//            'help' => gT("Bigger batches will increase memory usage for better performance."),
        ]);
        echo TbHtml::numberFieldControlGroup('chunkSize', 1024*1024, [
            'label' => gT("Chunk size for reading"),
            'required' => true,
            'formLayout' => TbHtml::FORM_LAYOUT_HORIZONTAL,
//            'help' => gT("Bigger chunks will give less frequent status updates but have a (slightly) better performance."),
            'labelWidthClass' => 'col-sm-6',
            'controlWidthClass' => 'col-sm-6',
        ]);

        echo TbHtml::checkBoxControlGroup("filterBlanks", true, [
            'formLayout' => TbHtml::FORM_LAYOUT_HORIZONTAL,
            'label' => gT("Filter blank email addresses"),
            'help' => gT("If not enabled, empty addresses will throw an error during import."),
            'labelWidthClass' => 'col-sm-6',
            'controlWidthClass' => 'col-sm-6',
        ]);
        ?>
    </div>
    <div class="col-md-9">
        <?php
        echo TbHtml::checkBoxControlGroup("headerColumns", true, [
            'label' => gT("File has headers"),
            'formLayout' => TbHtml::FORM_LAYOUT_HORIZONTAL
        ]);

        echo TbHtml::customControlGroup(TbHtml::tag('div', ['style' => 'overflow-x: auto;'], TbHtml::tag('table', ['class' => 'preview table'], '')), 'preview', [
            'label' => 'Data preview:',
            'formLayout' => TbHtml::FORM_LAYOUT_HORIZONTAL,
        ]);
        echo TbHtml::textAreaControlGroup('errors', '', [
            'label' => 'Data errors:',
            'class' => 'errors',
            'formLayout' => TbHtml::FORM_LAYOUT_HORIZONTAL
        ]);

        ?>
    </div>
</div>