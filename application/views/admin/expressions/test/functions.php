<?php
/* @var $this AdminController */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('expressionsFunctions');
$aFunctions = array_map(
    function ($val, $key) {
        $val['name'] = $key;
        $val['id'] = $key;
        return $val;
    },
    ExpressionManager::GetAllowableFunctions(),
    array_keys(ExpressionManager::GetAllowableFunctions())
);

?>
<div class="row">
    <div class="col-12">
        <h3>Functions available within ExpressionScript Engine</h3>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <?php $this->widget(
            'application.extensions.admin.grid.CLSGridView',
            [
                /*
                'dataProvider' => new class ($aFunctions) extends CDataProvider {
                    private $data;
                    public function __construct($data) { $this->data = $data; }
                    public function getData($refresh = false) { return $this->data; }
                    public function fetchData() { return array_values($this->data); }
                    public function fetchKeys() { return array_keys($this->data); }
                    public function calculateTotalItemCount() { return count($this->data); }
                    //public function getSort() { return null; }
                    //public function getPagination() { return null; }
                },
                 */
                'dataProvider' => new CArrayDataProvider($aFunctions),
                'columns' => [
                    [
                        'header' => gT('Function'),
                        'value' => '$data["name"]',
                    ],
                    [
                        'header' => gT('Meaning'),
                        'value' => '$data[2]',
                    ],
                    [
                        'header' => gT('Syntax'),
                        'value' => '$data[3]',
                    ],
                    [
                        'header' => gT('Reference'),
                        'type' => 'raw',
                        'value' => function($data) { return sprintf('<a target="_blank" href="%s">%s</a>', $data[4], $data[4]); }
                    ]
                ]
            ]
        ); ?>
    </div>
</div>
