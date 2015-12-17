<?php
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

    $data = [array_values(CHtml::listData($columns, 'label', 'label'))];

?>

<script>
    var
        tpl = ['A1', 0],
        data = <?=json_encode($data); ?>,
        container = document.getElementById('labels'),
        languages = <?=json_encode(array_values(CHtml::listData(App()->getLocale()->data(), 'code', 'description'))); ?>;




    function isEmptyRow(instance, row) {
        var rowData = instance.getData()[row];

        for (var i = 0, ilen = rowData.length; i < ilen; i++) {
            if (rowData[i] !== null) {
                return false;
            }
        }

        return true;
    }

    function defaultValueRenderer(instance, td, row, col, prop, value, cellProperties) {
        var args = arguments;
        if (args[5] === null && isEmptyRow(instance, row)) {
            if (col === 0) {
                args[5] = 'A' + row;
            } else {
                args[5] = tpl[col];
            }
            td.style.color = '#999';
        }
        else {
            td.style.color = '';
        }

        if (cellProperties.readOnly === true) {
            console.log('setting');
            td.style.background = '#555';
        }
        Handsontable.renderers.TextRenderer.apply(this, args);
    }

    hot1 = new Handsontable(container, {
//        startRows: 8,
//        columns: <?//=json_encode($columns); ?>//,
        startCols: <?=count($columns) ?>,
        minSpareRows: 1,
        colHeaders: false,
        contextMenu: true,
        cells: function (row, col, prop) {
            var cellProperties = {};
            cellProperties.readOnly = (row === 0);
            if (row === 0 && (this.instance.countCols() - 1) === col) {
                cellProperties.type = 'dropdown';
                cellProperties.readOnly = false;
                var current = this.instance.getDataAtRow(0);
                cellProperties.source = languages.filter(function(element) {
                    return current.indexOf(element) == -1;
                });

                cellProperties.source.unshift('Add language');

            } else if ((this.instance.countCols() - 1) === col) {
                cellProperties.readOnly = true;
                cellProperties.renderer = defaultValueRenderer;
            } else {

                cellProperties.renderer = defaultValueRenderer;
            }

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
//        afterOnCellMouseDown: function (e, coords, TD) {
//            debugger;
//        },
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

                if (changes[i][0] === 0 && (changes[i][1] === clen - 1)) {
                    if (languages.indexOf(changes[i][3]) !== - 1) {
                        console.log('setting');
                        this.setDataAtCell(0, clen, 'Add language');
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
        autoWrapRow: true,
        allowInsertColumn: false,
        allowInvalid: false,
        rowHeaders: true,
        colHeaders: function(index) {
            console.log(this);
            return this.data[0][index];
        },
        manualRowMove: true,
        contextMenu: {
            items: {
                row_above: {},
                row_below: {},
                "hsep1": "---------",
                remove_row: {},
                remove_col: {

                    disabled: function () {
                        var col = this.getSelected()[1];
                        return col < 2 || col === (this.countCols() - 1);
                    }
                }

            }
        }




    });

    hot1.loadData(data);




</script>
