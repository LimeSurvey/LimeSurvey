<div class="row" style="height: 100%;">
    <div class="col-md-3" style="position: fixed; top: 70px; bottom: 0px; overflow-y:scroll; background-color: white;">

<?php
use ls\models\Survey;

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
    <div class="col-md-6 col-md-offset-3 clause" id="expressions" data-operator="and">
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
            if (expression instanceof jQuery) {
                return expression;
            }
            var $clause = $('<span>').addClass('clause');
            if (typeof type != 'undefined') {
                $clause.attr('data-operator', type);
            }
            return $clause.html(expression);
        }

        function addExpression(expression, nodeId) {

            console.log('Adding expression');
            var id = "expression" + nodeId;

            $('#' + id).remove();
            createClause(expression).attr('id', id).appendTo('#expressions');
        }

        function constructExpression(node) {
            var children = $tree.treeview('getNode', node.parentId).nodes.filter(function(node) {
                return node.state.selected;
            });

            if (children.length == 1) {
                return createClause(children[0].data);
            } else {
                var $clause = createClause('', 'or');
                for (var i = 0; i < children.length; i++) {
                    $clause.append(createClause(children[i].data));
                }

                return $clause;
            }


        }
        $tree.on('nodeSelected', function (e, node) {
            if (node.data.indexOf('{VALUE}') > -1) {
                popupText(node);
            } else {
                // Get parent node.
                addExpression(constructExpression(node), node.parentId);
            }
        });
        $tree.on('nodeUnselected', function (e, node) {
            if (node.data.indexOf('{VALUE}') > -1) {
                $('#expression' + node.nodeId).remove();
            } else {
                $('#expression' + node.parentId).remove();
                console.log(node);
                addExpression(constructExpression(node), node.parentId);
            }
        });
    });

    window.expressionToJson = function expressionToJson(selector) {
        var $node = $(selector);
        if ($node.children().length == 0) {
            return $node.text();
        } else {
            return [
                $node.attr('data-operator'),
                $node.children().toArray().map(expressionToJson)
            ];
        }
    }


</script>
<style>
    .clause > .clause:first-child:not(:last-child)::before {
        content: "( " !important;
    }

    .clause > .clause:last-child:not(:first-child)::after {
        content: " )" !important;
    }

    .clause[data-operator]::before {
        display: block;

    }
    .clause[data-operator="or"] > .clause:not(:first-child)::before {
        content: " || ";
    }

    .clause[data-operator="and"] > .clause:not(:first-child)::before {
        content: " && ";
    }

    .clause > .clause {
        border: 2px solid red;
    }
    .clause[data-operator] > .clause {
        border-width: 0;
    }

</style>