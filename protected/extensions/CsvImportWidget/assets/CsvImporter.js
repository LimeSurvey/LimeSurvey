
function CsvImporter(elem, options) {
    var that = this;
    var $elem = $(elem);
    var readerProgress = 0.0;
    var fileSize = 0;
    var stopped = false;
    var workerActive = false;
    this.completeHandler = function() {
        workerActive = false;
        if (!stopped) {
            $elem.trigger('parseComplete', arguments);
        } else {
            $elem.trigger('stopped', arguments);
        }

    };
    /**
     * Handle data chunk from parser.
     * @param result.data An array of rows. If header is false, rows are arrays; otherwise they are objects of data keyed by the field name.
     * @param result.errors An array of errors.
     * @param result.meta Extra info.
     */
    this.chunkHandler = function(result, reader) {
        if (stopped) {
            reader.abort();
            return;
        }
        try {
            var records = 0;
            var rows = [];
            var oldReaderProgress = readerProgress;
            readerProgress = (result.meta.cursor / fileSize * 100);
            $elem.trigger('readProgress', readerProgress);


            for (var i = 0; i < result.data.length; i++) {
                var row = translateRow(result.data[i], map);
                var skip = false;
                for (var j = 0; j < that.config.filterBlanks.length; j++) {
                    if (row[that.config.filterBlanks[i]].length == 0) {
                        skip = true;
                        break;
                    }
                }
                if (skip) {
                    continue;
                }


                rows.push(row);

                if (rows.length >= that.config.batchSize) {
                    records += rows.length;
                    var data = JSON.stringify({
                        items: rows,
                        map: map,
                        querySize: that.config.querySize,
                    });
                    rows = [];
                    // Progress after this request is completed
                    sendData(data, oldReaderProgress + (records / result.data.length) * (readerProgress - oldReaderProgress));
                }
            }
            records += rows.length;
            var data = JSON.stringify({
                'items': rows,
                'map': map,
                querySize: that.config.querySize,
            });
            sendData(data, oldReaderProgress + (records / result.data.length) * (readerProgress - oldReaderProgress));
        } catch (exception) {
            that.stop("Stopping because of exception during parsing.");
            reader.abort();
            throw exception;

        }
    };

    /**
     * Handle ajax error.
     * @param jqXHR
     * @param textStatus
     * @param errorThrown
     */
    this.errorHandler = function(jqXHR, textStatus, errorThrown) {
        that.stop(errorThrown);

    };

    /**
     * Handle ajax success.
     * @param data
     */
    this.successHandler = function(data) {
        try {
            $elem.trigger('uploadProgress', [this.progress, data]);
            // Not stopped and not running so we are done!
            if (!stopped && !$.ajaxq.isRunning(that.config.ajaxQueue) && !workerActive) {
                $elem.trigger('complete');
            }
        } catch (exception) {
            that.stop("Stopping because of exception during AJAX callback.");
            throw exception;
        }
    };

    this.defaults = {
        parser: {
            chunkSize: 1024 * 1024 * 5, // 5mb
            dynamicTyping: false,
            worker: true,
            encoding: "utf-8",
            // Empty means autodetect
            delimiter: "",
            header: true,
            skipEmptyLines: true,
            preview: 0,
            chunk: this.chunkHandler,
            complete: this.completeHandler
        },

        ajax: {
            url: '#',
            method: 'post',
            timeout: 0,
            contentType: 'application/json',
            dataType: 'json',
            error: that.errorHandler,
            success: that.successHandler

        },
        // Fields to check for empty values.
        filterBlanks: [],
        // Number of records to upload per ajax request.
        batchSize: 5000,

        // Number of records to insert per query (server side limitation may apply).
        querySize: 1000,
        // Called each time before data is sent.
        beforeSendData: function(data) {
            data.YII_CSRF_TOKEN = $('input[name=YII_CSRF_TOKEN]').val();
            return data;
        },
        ajaxQueue: 'CsvImporter',
        mapCallback : function() {}
    };

    this.setConfig = function(config) {
        if (typeof config != 'undefined') {
            that.config = $.extend(true, {}, that.defaults, config);
        } else {
            that.config = $.extend(true, {}, that.defaults);
        }
    }

    this.setConfig(options);


    var map = {};




    /**
     * Translates an input row using map.
     * @param row
     * @param map
     * @returns {{}}
     */
    function translateRow(row, map) {
        var result = {};
        for (var k in map) {
            result[k] = row[map[k]];
        }
        return result;
    }


    var sendData = function (data, progress) {
        if (!stopped) {
            var ajaxOpts = $.extend(true, {}, that.config.ajax, {
                data: that.config.beforeSendData(data),
                progress: progress
            });
            $.ajaxq('CsvImporter', ajaxOpts);
        }
    }

    /**
     *
     * @param File file
     * @param fieldMap Map that specifies how to map source fields to target fields. Keys are the target fields, values the source fields.
     */
    this.importFile = function importFile(file) {
        $elem.trigger('start', file);
        map  = that.config.mapCallback();
        if (Object.keys(that.config.mapCallback()).length == 0) {
            throw "Fieldmap cannot be empty";
        }
        var start = Date.now();
        stopped = false;
        workerActive = true;
        fileSize = file.size;
        parserConfig = $.extend(true, {}, that.config.parser);
        Papa.parse(file, parserConfig);
        return true;
    };

    this.stop = function(message) {
        if (!stopped) {
            stopped = true;
            $.ajaxq.abort(this.config.ajaxQueue);
            console.error(message);
            if (workerActive) {
                $elem.trigger('stopping', message);
            } else {
                $elem.trigger('stopped', message);
            }

        }
    };

    this.previewFile = function previewFile(file, callback, count) {
        // Get map.
        var parserConfig = $.extend(true, {}, that.config.parser);
        if (typeof count != 'undefined') {
            parserConfig.preview = count;
        } else {
            parserConfig.preview = 5;
        }
        parserConfig.worker = false;
        parserConfig.chunk = null;
        parserConfig.step = null;

        parserConfig.complete = callback;
        Papa.parse(file, parserConfig);
        return true;
    };




    /**
     * Helper function that constructs a table from (preview) data.
     * @param data
     * @returns {*|jQuery|HTMLElement}
     */
    this.constructTable = function constructTable(data) {
        var $table = $('<table/>');
        // Create headers.

        if (!Array.isArray(data[0])) {
            var $tr = $('<tr/>');

            for (var k in data[0]) {
                $tr.append('<th>' + k + '</th>');
            }
            $table.append($('<thead/>').append($tr));
        }
        $tbody = $('<tbody/>');
        for (var i = 0; i < data.length; i++) {
            var $tr = $('<tr/>');
            for (var k in data[i]) {
                $tr.append('<td>' + data[i][k] + '</td>');
            }
            $tr.appendTo($tbody);
        }
        $tbody.appendTo($table);
        return $table;
    };

    this.preview = function preview (file) {
        var $container = $elem.find('.csvColumns');
        var $placeholder = $elem.find('.placeholder').clone().removeClass('placeholder');
        var addColumn = function(name) {
            var $column = $placeholder.clone();



            $column.attr('data-column', name);
            if (name.length > 0) {
                $column.find('label').text(name);
                $column.find('input').attr('name', name);
            } else {
                $column.find('label').text("Empty");
            }

            $('#existingAttributes [data-attribute]').each(function(i, elem) {
                if ($(elem).data('attribute').toUpperCase() == name.toUpperCase()) {
                    $column.find('input').val($(elem).data('attribute'));
                    $container = $(elem);
                }
            });
            $column.appendTo($container);


        };
        that.previewFile(file, function(result) {
            $elem.find('.preview').html(that.constructTable(result.data).html());

            var $errors = $elem.find('.errors');
            if (result.errors.length > 0) {
                var text = '';
                for(var i = 0; i < result.errors.length; i++) {
                    text += "[" + result.errors[i].type + "] " + result.errors[i].message + " in line " + result.errors[i].row + "\n";

                }
                $errors.text(text);
                $errors.closest('.form-group').removeClass('has-success').addClass('has-error');
            } else {
                $errors.closest('.form-group').removeClass('has-error').addClass('has-success');
            }

            for (var k in result.data[0]) {
                addColumn(k);
            }


        });
    };

    /**
     * Reset mapper, progress and preview.
     */
    this.reset = function() {
        $elem.find('.preview').html('');
        $elem.find('.errors').text('');
        $elem.find('.progress-bar').css('width', 0);
        $elem.find('.csvColumn:not(.placeholder)').remove();
    }


    // Bind events.
    $elem.on('input change', function(e) {
        // Check if file is set.
        var file = $elem.find('input[type=file]')[0].files[0];
        if (typeof file == 'undefined') {
            that.reset();
        } else {
            that.reset();
            that.preview(file);
        }
    });

    $elem.on('click', '.start', function() {
        var file = $elem.find('input[type=file]')[0].files[0];
        if (typeof file != 'undefined') {
            that.importFile(file);
            $elem.find('a[href="#tab_3"]').tab('show');
        }

    });

    $elem.on('click', '.stop', function() {
        that.stop('Aborted by user');
    });

    $elem.on('stopped complete', function() {
        $elem.removeClass('stopping busy');
    });
    $elem.on('stopping', function() {
        $elem.addClass('stopping');
    });
    $elem.on('start', function() {
        $elem.addClass('busy');
    })






}

