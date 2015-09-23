<div class="row" style="height: 100%;">
    <div class="col-md-3" style="position: fixed; top: 70px; bottom: 0px; overflow-y:scroll; background-color: white;">

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
                    'data' => !$question->hasAnswers ? "{$subQuestion->getCode()} == \"{VALUE}\"" : null
                ];

                $subQuestions[] = $node;
            }
            $groupTree[] = [
                'text' => $question->displayLabel,
                'data' => [
                    'type' => get_class($question)
                ],
                'children' => $subQuestions
            ];
        } elseif ($question->hasAnswers) {
            $children = array_values(array_map(function (\ls\interfaces\iAnswer $answer) use ($question) {
                return [
                    'text' => $answer->getLabel(),
                    'icon' => 'asterisk',
                    'data' => "{$question->title} == \"{$answer->getCode()}\""
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
                'data' => "{$question->title} == \"{VALUE}\""
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
    <div class="col-md-6 col-md-offset-3 expressions" id="expressions">
<?php



?>

    </div>
</div>
<script>
    $(document).ready(function() {
        console.log('adding event');
        $tree = $('#tree');
        function popupText(node) {
            bootbox.prompt("Please enter a value for " + node.text + ":", function (result) {
                if (result == null) {
                    $tree.treeview('unselectNode', node.nodeId, {silent: true});
                } else {
                    addExpression(node.data.replace('{VALUE}', result), node.nodeId);
                }
            });
        }

        function createClause(expression, type) {
            if (typeof type == 'undefined') {
                type = 'or';
            }
            return $('<span>').addClass(type + '-clause').html(expression);
        }

        function addExpression(expression, nodeId) {
            console.log('Adding expression');
            var id = "expression" + nodeId;

            $('#' + id).remove();
            createClause(expression, 'and').attr('id', id).appendTo('#expressions');
        }

        function constructExpression(node) {
            var clause = createClause('', 'and');

            clause.append($tree.treeview('getNode', node.parentId).nodes.filter(function(node) {
                return node.state.selected;
            }).map(function(node) {
                return createClause(node.data, 'or');
            }));
            return clause;
        }
        $tree.on('nodeSelected', function (e, node) {
            if (node.data.indexOf('{VALUE}') > -1) {
                popupText(node);
            } else {
                // Get parent node.
                addExpression(constructExpression(node).html(), node.parentId);
            }
        });
        $tree.on('nodeUnselected', function (e, node) {
            if (node.data.indexOf('{VALUE}') > -1) {
                $('#expression' + node.nodeId).remove();
            } else {
                $('#expression' + node.parentId).remove();
                console.log(node);
                addExpression(constructExpression(node).html(), node.parentId);
            }
        });



    });
</script>
<style>
    .expressions .and-clause::before {
        content: "( ";
    }
    .expressions .and-clause::after {
        content: " )";
    }

    .expressions .or-clause:not(:first-child)::before {
        content: " || ";
    }
    .expressions .and-clause:not(:first-child)::before {
        content: " && (";
    }

</style>