<?php

/**
 * Class CsvImportWidget
 *
 * A widget that supports client side parsing of a CSV file and mapping its columns to model attributes.
 *
 */
class CsvImportWidget extends CWidget
{
    /**
     * @var CModel
     */
    public $model;

    /**
     * The attributes available for mapping, by default all safe attributes are used.
     * - Pass array to set specific attributes.
     * @var
     */
    public $attributes;

    /**
     * Allow user to create new fields from the CSV file.
     * @var bool
     */
    public $enableFieldCreation = true;

    public function init()
    {
        parent::init();
        if (!isset($this->attributes)) {
            $this->attributes = $this->model->getSafeAttributeNames();
        }
    }


    public function registerClientScript() {
        /** @var \CClientScript $cs */
        $cs = \Yii::app()->clientScript;
        /** @var \CAssetManager $am */
        $am = \Yii::app()->assetManager;


        $assetUrl = $am->publish(__DIR__ . '/assets', false, -1, true);
        $cs->registerPackage('ajaxq');
        $cs->registerScriptFile($assetUrl . '/CsvImporter.js');
        $cs->registerCssFile($assetUrl . '/CsvImporter.css');


        $config = \CJavaScript::encode([
            'mapCallback' => new CJavaScriptExpression('function() { var result = {}; $(".csvColumn input").filter(function(i, elem) { return $(elem).val().length > 0; }).each(function(i, elem) {
        var key = $(elem).val();
        if (key.length > 0) {
            result[$(elem).val()] = $(elem).attr("name");
        }
    });
    return result; }')
        ]);
        $scriptParts = [
            "$('#{$this->getId()}').data('importer', new CsvImporter($('#{$this->getId()}'), {$config}));",


        ];
        $cs->registerScript($this->getId(), implode("\n", $scriptParts), $cs::POS_READY);


    }

    /**
     * Executes the widget.
     * This method is called by {@link CBaseController::endWidget}.
     */
    public function run()
    {
        $this->registerClientScript();
        echo \CHtml::openTag('div', [
            'id' => $this->id,
            'class' => 'CsvImportWidget'
        ]);

        echo \CHtml::tag('div', ['class' => 'overlay'], '');
        $this->widget(TbTabs::class, [
            'tabs' => [
                [
                    'label' => gT('Source / upload configuration'),
                    'content' => $this->render('form', null, true),
                    'active' => true
                ],
                [
                    'label' => gT('Column configuration'),
                    'content' => $this->render('mapper', null, true)
                ],
                [
                    'label' => gT('Progress'),
                    'content' => $this->render('progress', null, true)
                ],
            ]
        ]);
        echo \CHtml::closeTag('div');

    }


}