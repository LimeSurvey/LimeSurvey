<?php
    /** @var \ls\models\LabelSet $model */
//    $this->renderPartial('create', ['model' => $model]);
    App()->clientScript->registerPackage('handsontable');
    echo \TbHtml::tag('div', [
        'id' => 'labels'
    ]);
    $languages = CHtml::listData(App()->getLocale()->data(), 'code', 'description');
    $columns = [
        [
            'label' => 'Code',
            'type' => 'text'
        ], [
            'label' => 'Assessment value',
            'type' => 'numeric'
        ]
    ];
    foreach($model->languageArray as $language) {
        $columns[] = [
            'label' => $languages[$language],
            'type' => 'text'
        ];
    }

    $columns[] = [
        'label' => 'Add language',
        'type' => 'text',
        'readOnly' => true
    ];

    $data = [];
    foreach ($model->labels as $label) {
        $data[$label->code]['code'] = $label->code;
        $data[$label->code]['assessment_value'] = $label->assessment_value;
        $data[$label->code][$label->language] = $label->title;
    };



?>

<script>
    var
        tpl = ['A1', 0],
        data = <?=json_encode(array_values($data)); ?>,
        container = document.getElementById('labels'),
        languageMap = <?=json_encode(CHtml::listData(App()->getLocale()->data(), 'code', 'description')); ?>,
        activeLanguages = <?=json_encode($model->languageArray); ?>;





    function isEmptyRow(instance, row) {
        var rowData = instance.getData()[row];
        if (typeof rowData == 'undefined') {
            return true;
        }
        for (var i = 0, ilen = rowData.length; i < ilen; i++) {
            if (rowData[i] !== null) {
                return false;
            }
        }

        return true;
    }

    function defaultValueRenderer(instance, td, row, col, prop, value, cellProperties) {
        var args = arguments;
        if (cellProperties.readOnly === true) {
            td.style.setProperty('background-color','#eee', 'important');
            td.style.setProperty('pointer-events','none');
        }
        Handsontable.renderers.TextRenderer.apply(this, args);
    }

    $('#labels').on('mousedown', 'th', function(e) {
//        e.preventDefault;
//        e.stopPropagation();
        if ($(this).find('select').length > 0) {
            hot1.deselectCell();
        }
    });
    hot1 = new Handsontable(container, {
        beforeAutofill: function(start, end, data) {
            // Check how many rows.
            if (start.col === 0) {

                var pattern = data.slice();
                data.length = 0;
                for (var i = start.row; i <= end.row; i++) {

                    var template = pattern[(i - start.row) % (pattern.length)].slice();
                    template[0] = 'A' + (i + 1);
//
//                    for (var j = 1; j < template.length; j++) {
//                        // Check other columns.
//                        if (pattern.length > 1 &&  i > start.row
//                            && typeof prev != 'undefined'
//                            && !isNaN(template[j])
//                            && !isNaN(prev[j])
//                        ) {
//                            template[j] += template[j] - prev[j];
//                        }
//                    }
//                    var prev = pattern[(i - start.row) % (pattern.length)].slice();
                    data.push(template);

                }
                return data;
            }



//            if e
//            debugger;
        },
        minSpareRows: 1,
        contextMenu: true,
        startCols: <?=count($model->getLanguageArray()) + 3; ?>,
        cells: function (row, col, prop) {
            var cellProperties = {};
                cellProperties.renderer = defaultValueRenderer;

            if (col === 1) {
                cellProperties.type = 'numeric';
            }
            return cellProperties;
        },
        legend: [
            {
                match: function (row, col, data) {
                    return (row === 0);
                },
                style: {
                    color: '#666',
                    fontWeight: 'bold'
                },
                title: 'Heading',
                readOnly: true
            }
        ],
        beforeChange: function (changes) {
            var ilen = changes.length,
                clen = this.countCols(),
                rowColumnSeen = {},
                rowsToFill = {},
                i,
                c;
            for (i = 0; i < ilen; i++) {
                // if oldVal is empty
                if (changes[i][2] === null && changes[i][3] !== null) {
                    if (isEmptyRow(this, changes[i][0])) {
                        // add this row/col combination to cache so it will not be overwritten by template
                        rowColumnSeen[changes[i][0] + '/' + changes[i][1]] = true;
                        rowsToFill[changes[i][0]] = true;
                    }
                }


            }
            for (var r in rowsToFill) {
                if (rowsToFill.hasOwnProperty(r)) {
                    for (c = 0; c < clen; c++) {
                        // if it is not provided by user in this change set, take value from template
                        if (!rowColumnSeen[r + '/' + c]) {
                            changes.push([r, c, null, tpl[c]]);
                        }
                    }
                }
            }
        },
        columns: createColumns(),
        autoWrapRow: true,
        allowInsertColumn: false,
        allowInvalid: true,
        rowHeaders: true,
        colHeaders: function(index) {

            if (index >= 2 && (index - 2) < activeLanguages.length) {

                return languageMap[activeLanguages[index - 2]] + ' <button data-index="' + index + '" data-code="' + activeLanguages[index - 2] + '" class="btn btn-danger remove">X</button>' ;
//                activeLanguages;
            } else if (index === 1) {
                return 'Assessment';
            } else if (index === 0)
            {
                return 'Code';
            } else {
                return createDropdown();
            }
        },
        manualRowMove: true,
        contextMenu: {
            items: {
                row_above: {},
                row_below: {},
                "hsep1": "---------",
                remove_row: {},
            }
        },
        tabMoves: function(e) {
            if (!e.shiftKey && hot1.getSelected()[1] == (hot1.countCols() - 2)
                || (e.shiftKey && hot1.getSelected()[1] == 0)
            ) {
                return {
                    row: 1,
                    col: -hot1.countCols() + 2
                };
            }
            return {
                row: 0,
                col: 1
            };
        },





    });

    function createColumns() {
        var result = [
            {data: 'code'},
            {
                data: 'assessment_value',
                type: 'numeric'
            }
        ];
        for(var i = 0; i < activeLanguages.length; i++) {
            result.push({data: activeLanguages[i]});
        }

        result.push({
            data: '',
            width: '300px',
            readOnly: true,


        });
        return result;
    }

    function createDropdown() {
        var s = $('<select />').addClass('form-control');
        $('<option />', {value: "", text: "Add language..."}).appendTo(s);
        for(var languageCode in languageMap) {
            if (activeLanguages.indexOf(languageCode) === -1) {
                $('<option />', {value: languageCode, text: languageMap[languageCode]}).appendTo(s);
            }

        }

        return s.wrap('<div/>').parent().html();
    }

    hot1.loadData(data);

    $('#labels').on('click', '.remove', function() {

        var $button = $(this);
        var remove = function(instance, code) {
            activeLanguages.splice(activeLanguages.indexOf(code), 1);
            instance.updateSettings({columns: createColumns()});
        }
        if (hot1.getDataAtCol($button.attr('data-index')).filter(function(el) {return el !== null; }).length === 0) {
            remove(hot1, $button.attr('data-code'));
        } else {
            bootbox.confirm('This will delete all translated labels for ' + languageMap[$(this).attr('data-code')], function (ok) {
                if (ok) {
                    remove(hot1, $button.attr('data-code'));
                }
            });
        }
        hot1.deselectCell();
    });

    $('#labels').on('change', 'select', function() {
        activeLanguages.push($(this).val());
        hot1.updateSettings({columns: createColumns()});
    });


</script>
<style>
    th {
        pointer-events: none;
    }
    th button, th select {
        pointer-events: auto;
    }
</style>