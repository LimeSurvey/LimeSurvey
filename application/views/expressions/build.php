<div class="row">
    <div class="col-md-3">

<?php
$tree = [];
/** @var Survey $survey */

foreach($survey->groups as $group) {
    $groupTree = [];
    foreach ($group->questions as $question) {
        // Check subquestions.
        if ($question->hasSubQuestions) {
            $subQuestions = [];
            /** @var \ls\interfaces\iSubQuestion $subQuestion */
            foreach ($question->getSubQuestions() as $subQuestion) {
                if ($question->hasAnswers) {
                    $children = array_values(array_map(function (\ls\interfaces\iAnswer $answer) use ($subQuestion) {
                        return [
                            'text' => $answer->getLabel(),
                            'icon' => 'asterisk',
                            'data' => "({$subQuestion->getCode()} == \"{$answer->getCode()}\")"
                        ];

                    }, $question->getAnswers()));
                }
                $node = [
                    'text' => $subQuestion->getLabel(),
                    'children' => $question->hasAnswers ? $children : null,
                    'icon' => !$question->hasAnswers ? 'pencil' : 'th-list',
                    'data' => !$question->hasAnswers ? "({$subQuestion->getCode()} == \"{VALUE}\")" : null
                ];

                $subQuestions[] = $node;
            }
            $groupTree[] = [
                'text' => $question->displayLabel,
                'children' => $subQuestions
            ];
        } elseif ($question->hasAnswers) {
            $children = array_values(array_map(function (\ls\interfaces\iAnswer $answer) use ($question) {
                return [
                    'text' => $answer->getLabel(),
                    'icon' => 'asterisk',
                    'data' => "({$question->title} == \"{$answer->getCode()}\")"
                ];

            }, $question->getAnswers()));
            $groupTree[] = [
                'text' => $question->getDisplayLabel(),
                'children' => $children,

            ];

        } else {
            $groupTree[] = [
                'text' => $question->getDisplayLabel(),
                'icon' => 'pencil',
                'data' => "({$question->title} == \"{VALUE}\")"
            ];

        }
    }
    $tree[] = [
        'children' => $groupTree,
        'text' => $group->title
    ];
}
//vdd($tree);
//
$this->widget(\SamIT\Yii1\Widgets\BootstrapTreeView::class, [
    'data' => $tree,
    'enableLinks' => false,
    'levels' => 1,
    'id' => 'tree',
    'multiSelect' => true,
    'htmlOptions' => [
        'data-test' => '123'
    ]
]);?>
    </div>
    <div class="col-md-6 expressions" id="expressions">
<?php



?>

    </div>
</div>
<script>
    $(document).ready(function() {
        console.log('adding event');
        function addExpression(expression, nodeId) {
            console.log('Adding expression');
            $('<span>').text(expression).attr('id', "expression" + nodeId).appendTo('#expressions');
        }
        $('#tree').on('nodeSelected', function (e, node) {
            if (node.data.indexOf('{VALUE}') > -1) {
                var $tree = $(this);
                bootbox.prompt("Please enter a value for " + node.text + ":", function (result) {
                    if (result == null) {
                        $tree.treeview('unselectNode', node.nodeId, {silent: true});
                    } else {
                        addExpression(node.data.replace('{VALUE}', result), node.nodeId);
                    }
                });
            } else {
                addExpression(node.data, node.nodeId);
            }
        });
        $('#tree').on('nodeUnselected', function (e, node) {
            $('#expression' + node.nodeId).remove();
        });



    });
</script>
<style>
    .expressions span:not(:first-child)::before {
        content: " && ";
    }

</style>