
function hideSection(section) {
    var collapsible = document.getElementById(section);
    // Try to get the bootstrap collapse instance
    var bsCollapse = bootstrap.Collapse.getInstance(collapsible);
    // If there is no previous instance, create a new one
    if (!bsCollapse) {
        bsCollapse = new bootstrap.Collapse(collapsible);
    }
    bsCollapse.hide();
}

/**
 * Display chartjs graph
 */
var chartjs = new Array();
var COLORS_FOR_SURVEY = new Array('20,130,200', '232,95,51', '34,205,33', '210,211,28', '134,179,129', '201,171,131', '251,231,221', '23,169,161', '167,187,213', '211,151,213', '147,145,246', '147,39,90', '250,250,201', '201,250,250', '94,0,94', '250,125,127', '0,96,201', '201,202,250', '0,0,127', '250,0,250', '250,250,0', '0,250,250', '127,0,127', '127,0,0', '0,125,127', '0,0,250', '0,202,250', '201,250,250', '201,250,201', '250,250,151', '151,202,250', '251,149,201', '201,149,250', '250,202,151', '45,96,250', '45,202,201', '151,202,0', '250,202,0', '250,149,0', '250,96,0', '184,230,115', '102,128,64', '220,230,207', '134,191,48', '184,92,161', '128,64,112', '230,207,224', '191,48,155', '230,138,115', '128,77,64', '230,211,207', '191,77,48', '80,161,126', '64,128,100', '207,230,220', '48,191,130', '25,25,179', '18,18,125', '200,200,255', '145,145,255', '255,178,0', '179,125,0', '255,236,191', '255,217,128', '255,255,0', '179,179,0', '255,255,191', '255,255,128', '102,0,153', '71,0,107', '234,191,255', '213,128,255');
var initChartGraph = function (element, type, qid) {
    if (typeof chartjs[qid] == "undefined" || typeof chartjs == "undefined") // typeof chartjs[$qid] == "undefined" || typeof chartjs == "undefined"
    {
        if (type === 'Bar' || type === 'Radar' || type === 'Line' || type === 'Doughnut' || type === 'Pie' || type === 'PolarArea') {
            init_chart_js_graph_with_datasets(type, qid);
        } else {
            init_chart_js_graph_with_datas(type, qid);
        }
    }
};
/**
 * loadGraphOnScroll jQuery plugin
 * This plugin will load graph on scroll
 */
(function ($) {
    $.fn.loadGraphOnScroll = function () {
        this.each(function () {
            var $elem = $(this);
            var $type = $elem.data('type');
            var $qid = $elem.data('qid');

            $(window).scroll(function () {
                var $window = $(window);
                var docViewTop = $window.scrollTop();
                var docViewBottom = docViewTop + $window.height();
                var elemTop = $elem.offset().top;
                var elemBottom = elemTop + $elem.height();

                if ((elemBottom <= docViewBottom) && (elemTop >= docViewTop)) {
                    // chartjs
                    initChartGraph($elem, $type, $qid);
                };
            });
        });
        return this;
    };
})(jQuery);

(function ($) {
    $.fn.loadGraph = function () {
        this.each(function () {
            var $elem = $(this);
            var $type = $elem.data('type');
            var $qid = $elem.data('qid');
            // chartjs
            initChartGraph($elem, $type, $qid);
        });
        return this;
    };
})(jQuery);

function parseType(typeDef) {
    switch(typeDef) {
        case "Bar":
        case "bar":
            return "bar";
        
        case "Pie":
        case "pie":
            return "pie";

        case "Radar":
        case "radar":
            return "radar";
        
        case "Line":
        case "line":
            return "line";
        
        case "PolarArea":
        case "polarArea":
        case "polararea":
            return "polarArea";
        
        case "Doughnut":
        case "doughnut":
            return "doughnut";

    }
}

/**
 * This function load the graph needing datasets (bars, etc.)
 */
function init_chart_js_graph_with_datasets($type, $qid) {
    var canvasId = 'chartjs-' + $qid;
    var $canvas = document.getElementById(canvasId).getContext("2d");
    var $canva = $('#' + canvasId);
    var $statistics = statisticsData['quid' + $qid];
    if ($statistics == undefined) return;
    var $labels = $statistics.labels
    var $grawdata = $statistics.grawdata
    var $color = $canva.data('color');

    if (typeof chartjs != "undefined") {
        if (typeof chartjs[$qid] != "undefined") {
            window.chartjs[$qid].destroy();
        }
    }

    var dataDefinition = {
        labels: $labels,
    };

    dataDefinition.datasets = [{
        label: $qid,
        data: $grawdata,
        backgroundColor: [],
        borderColor: [],
        hoverBackgroundColor: [],
        pointBackgroundColor: "#fff",
        pointHoverBackgroundColor: "#fff",
        pointHoverBorderColor: []
    }];

    // different color for each bar
    LS.ld.forEach($labels, function (label, key) {
        var colorIndex = (parseInt(key) + $color);
        dataDefinition.datasets[0].backgroundColor.push("rgba(" + COLORS_FOR_SURVEY[colorIndex] + ",0.6)");
        dataDefinition.datasets[0].borderColor.push("rgba(" + COLORS_FOR_SURVEY[colorIndex] + ",1)");
        dataDefinition.datasets[0].hoverBackgroundColor.push("rgba(" + COLORS_FOR_SURVEY[colorIndex] + ",0.9)");
        dataDefinition.datasets[0].pointHoverBorderColor.push("rgba(" + COLORS_FOR_SURVEY[colorIndex] + ",1)");
    });

    var parsedType = parseType($type);
    var options = {};

    if (parsedType == 'bar' || parsedType == 'line') {
        options.scales = {
            yAxes: [{
                ticks: {
                    suggestedMin: 0,
                }
            }]
        };
    }

    console.ls.log("Creating chart with definition: ", dataDefinition);

    window.chartjs[$qid] = new Chart($canvas, {
        type: parsedType,
        data: dataDefinition,
        options: options,
    });
}

/**
 * This function load the graphs needing datas (pie chart, polar, Doughnut)
 */
function init_chart_js_graph_with_datas($type, $qid) {
    var canvasId = 'chartjs-' + $qid;
    var $canvas = document.getElementById(canvasId).getContext("2d");
    var $canva = $('#' + canvasId);
    var $color = $canva.data('color');
    var $statistics = statisticsData['quid' + $qid];
    if ($statistics == undefined) return;
    var $labels = $statistics.labels
    var $grawdata = $statistics.grawdata
    var $chartDef = {
        labels: $labels,
        datasets: [{
            data: [],
            backgroundColor: [],
            hoverBackgroundColor: [],
        }],
    };
    var $max = 0;

    $.each($labels, function($i, $label) {
        $max = $max + parseInt($grawdata[$i]);
    });

    $.each($labels, function ($i, $label) {
        var colorIndex = (parseInt($i) + $color);
        $chartDef.datasets[0].data.push(Math.round($grawdata[$i]/$max * 100 * 100) / 100);
        $chartDef.datasets[0].backgroundColor.push("rgba(" + COLORS_FOR_SURVEY[colorIndex] + ",0.6)");
        $chartDef.datasets[0].hoverBackgroundColor.push("rgba(" + COLORS_FOR_SURVEY[colorIndex] + ",0.9)");
    });

    var parsedType = parseType($type);
    var $options = {
        tooltipTemplate: "<%if (label){%><%=label %>: <%}%><%= value + '%' %>",
    };

    if (parsedType == 'bar' || parsedType == 'line') {
        options.scales = {
            yAxes: [{
                ticks: {
                    suggestedMin: 0,
                }
            }]
        };
    }

    if (typeof chartjs != "undefined") {
        if (typeof chartjs[$qid] != "undefined") {
            window.chartjs[$qid].destroy();
        }
    }
    
    console.ls.log("Creating chart with definition: ", $chartDef);

    window.chartjs[$qid] = new Chart($canvas, {
        type: parsedType,
        data: $chartDef,
        options: $options
    });
}

LS.Statistics2 = function () {

    Chart.defaults.global.legend.display = false;

    if ($('#completionstateSimpleStat').length > 0) {
        $actionUrl = $('#completionstateSimpleStat').data('grid-display-url');

        $(document).on("change", '#completionstate', function () {
            $that = $(this);
            $actionUrl = $(this).data('url');
            $display = $that.val();
            $postDatas = { state: $display };

            $.ajax({
                url: $actionUrl,
                type: 'POST',
                data: $postDatas,

                // html contains the buttons
                success: function (html, statut) {
                    // Reload page
                    location.reload();
                },
                error: function (html, statut) {
                    console.ls.error(html);
                }
            });

        });
    }

    if ($('.chartjs-container').length > 0) {
        $elChartJsContainer = $('.chartjs-container').first();
        $('.canvas-chart').width($elChartJsContainer.width());
    }

    if ($('#showGraphOnPageLoad').length > 0) {
        $('#statisticsoutput .row').first().find('.chartjs-container').loadGraph();
    }

    $('#generate-statistics').submit(function () {
        hideSection('general-filters-item-body');
        hideSection('response-filters-item-body');
        $('#statisticsoutput').show();
        $('#view-stats-alert-info').hide();
        $('#statsContainerLoading').show();
        if ($('input[name=outputtype]:checked').val() != 'html') {
            var data = new FormData($(this).get(0));
            var url = $(this).attr('action');
            ajaxDownloadStats(url, data);
            return false;
        }
        //alert('ok');
    });

    // If the graph are displayed
    if ($('.chartjs-container').length > 0) {

        // On scroll, display the graph
        $('.chartjs-container').loadGraphOnScroll();

        // Buttons changing the graph type
        $('.chart-type-control').click(function () {

            $type = $(this).data('type');
            $qid = $(this).data('qid');

            // chartjs
            if ($type === 'Bar' || $type === 'Radar' || $type === 'Line' || $type === 'Doughnut' || $type === 'Pie' || $type === 'PolarArea') {
                init_chart_js_graph_with_datasets($type, $qid);
            } else {
                init_chart_js_graph_with_datas($type, $qid);
            }
        });

    }

    /**
     * Load responses for one question.
     * Used at question summary.
     */
    var loadBrowse = (function () {

        // Static variable for function loadBrowse, catched through closure
        // Use this to track if we should hide/show responses
        var toggle = {};

        var fn = function loadBrowse(id, extra) {

            var destinationdiv = $('#columnlist_' + id);

            // First time initialization
            if (toggle[id] === undefined) {
                toggle[id] = 0;
            }
            toggle[id] = 1 - toggle[id]; // Switch between 1 and 0

            if (toggle[id] === 0) {
                $('#' + id).parent().find('.statisticscolumndata, .statisticscolumnid').remove();
                return;
            }

            if (extra == '') {
                destinationdiv.parents("td:first").toggle();
            } else {
                destinationdiv.parents("td:first").show();
            }

            if (destinationdiv.parents("td:first").css("display") != "none") {
                $.get(listColumnUrl + '/' + id + '/' + extra, function (data) {
                    $('#' + id).parent().append(data);
                });
            }
        };

        // Closure return function
        return fn;
    })();

    if (showTextInline == 1) {
        /* Enable all the browse divs, and fill with data */
        $('.statisticsbrowsebutton').each(function () {
            if (!$(this).hasClass('numericalbrowse')) {
                loadBrowse(this.id, '');
            }
        });
    }
    $('.statisticsbrowsebutton').click(function () {
        if ($(this).hasClass('numericalbrowse')) {
            var destinationdiv = $('#columnlist_' + this.id);
            var extra = '';
            if (destinationdiv.parents("td:first").css("display") == "none") {
                extra = 'sortby/' + this.id + '/sortmethod/asc/sorttype/N/';
            }
            loadBrowse(this.id, extra);
        } else {
            loadBrowse(this.id, '');
        }

    });
    $(".sortorder").click(function (e) {
        var details = this.id.split('_');
        var order = 'sortby/' + details[2] + '/sortmethod/' + details[3] + '/sorttype/' + details[4];
        loadBrowse(details[1], order);
    });

    $('#usegraph').click(function () {
        if ($('#grapherror').length > 0) {
            $('#grapherror').show();
            $('#usegraph_2').prop('checked', true);
        }
    });

    /***
     * Select all questions
     */
    let viewsummaryallbuttons = document.querySelectorAll('input[name="viewsummaryall"]');
    for (let viewsummaryallbutton of viewsummaryallbuttons) {
        viewsummaryallbutton.addEventListener("change", () => {
            if (viewsummaryallbutton.value === '1') {
                let filterchoices = document.querySelectorAll('#filterchoices input[type=checkbox]');
                filterchoices.forEach((filterchoice) => {
                    filterchoice.checked = true;
                });
            } else {
                let filterchoices = document.querySelectorAll('#filterchoices input[type=checkbox]');
                filterchoices.forEach((filterchoice) => {
                    filterchoice.checked = false;
                });
            }
        });
    }

    /* Show and hide the three major sections of the statistics page */
    /* The response filters */
    $('#hidefilter').click(function () {
        $('#statisticsresponsefilters').hide();
        $('#filterchoices').hide();
        $('#filterchoice_state').val('1');
        $('#vertical_slide2').hide();
    });
    $('#showfilter').click(function () {
        $('#statisticsresponsefilters').show();
        $('#filterchoices').show();
        $('#filterchoice_state').val('');
        $('#vertical_slide2').show();
    });
    /* The general settings/filters */
    $('#hidegfilter').click(function () {
        $('#statisticsgeneralfilters').hide();
    });
    $('#showgfilter').click(function () {
        $('#statisticsgeneralfilters').show();
    });
    /* The actual statistics results */
    $('#hidesfilter').click(function () {
        $('#statisticsoutput').hide(1000);
    });
    $('#showsfilter').click(function () {
        $('#statisticsoutput').show(1000);
    });

    function showhidefilters(value) {
        if (value == true) {
            hide('filterchoices');
        } else {
            show('filterchoices');
        }
    }
    /* End of show/hide sections */

    if (typeof aGMapData == "object") {
        for (var i in aGMapData) {
            gMapInit("statisticsmap_" + i, aGMapData[i]);
        }
    }

    if (typeof aStatData == "object") {
        for (var i in aStatData) {
            statInit(aStatData[i]);
        }
    }

    $(".stats-hidegraph").click(function () {

        var id = statGetId(this.parentNode);
        if (!id) {
            return;
        }

        $("#statzone_" + id).html(getWaiter());
        graphQuery(id, 'hidegraph', function (res) {
            if (!res) {
                ajaxError();
                return;
            }

            data = JSON.parse(res);

            if (!data || !data.ok) {
                ajaxError();
                return;
            }

            isWaiting[id] = false;
            aStatData[id].sg = false;
            statInit(aStatData[id]);
        });
    });

    $(".stats-showgraph").click(function () {
        var id = statGetId(this.parentNode);
        if (!id) {
            return;
        }

        $("#statzone_" + id).html(getWaiter()).show();
        graphQuery(id, 'showgraph', function (res) {
            if (!res) {
                ajaxError();
                return;
            }
            data = JSON.parse(res);

            if (!data || !data.ok || !data.chartdata) {
                ajaxError();
                return;
            }

            isWaiting[id] = false;
            aStatData[id].sg = true;
            statInit(aStatData[id]);

            $("#statzone_" + id).append("<img border='1' src='" + temppath + "/" + data.chartdata + "' />");

            if (aStatData[id].sm) {
                if (!data.mapdata) {
                    ajaxError();
                    return;
                }

                $("#statzone_" + id).append("<div id=\"statisticsmap_" + id + "\" class=\"statisticsmap\"></div>");
                gMapInit('statisticsmap_' + id, data.mapdata);
            }

            $("#statzone_" + id + " .wait").remove();

        });
    });

    $(".stats-hidemap").click(function () {
        var id = statGetId(this.parentNode);
        if (!id) {
            return;
        }

        $("#statzone_" + id + ">div").replaceWith(getWaiter());

        graphQuery(id, 'hidemap', function (res) {
            if (!res) {
                ajaxError();
                return;
            }

            data = JSON.parse(res);

            if (!data || !data.ok) {
                ajaxError();
                return;
            }

            isWaiting[id] = false;
            aStatData[id].sm = false;
            statInit(aStatData[id]);

            $("#statzone_" + id + " .wait").remove();
        });
    });

    $(".stats-showmap").click(function () {
        var id = statGetId(this.parentNode);
        if (!id) {
            return;
        }

        $("#statzone_" + id).append(getWaiter());

        graphQuery(id, 'showmap', function (res) {
            if (!res) {
                ajaxError();
                return;
            }

            data = JSON.parse(res);

            if (!data || !data.ok || !data.mapdata) {
                ajaxError();
                return;
            }

            isWaiting[id] = false;
            aStatData[id].sm = true;
            statInit(aStatData[id]);

            $("#statzone_" + id + " .wait").remove();
            $("#statzone_" + id).append("<div id=\"statisticsmap_" + id + "\" class=\"statisticsmap\"></div>");

            gMapInit('statisticsmap_' + id, data.mapdata);
        });
    });

    $(".stats-showbar").click(function () {
        changeGraphType('showbar', this.parentNode);
    });

    $(".stats-showpie").click(function () {
        changeGraphType('showpie', this.parentNode);
    });

    var ajaxDownloadStats = function (url, data) {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', url, true);
        xhr.responseType = 'blob';
        xhr.onload = () => {
            const contentDisposition = xhr.getResponseHeader('Content-Disposition');
            const fileName = contentDisposition ? contentDisposition.match(/filename[^;=\n]*=['"](.*?|[^;\n]*)['"]/)[1] : '';
            if (fileName.length > 0) {
                // saveAs is implemented by jszip/fileSaver.js
                saveAs(xhr.response, fileName);
            } else {
                ajaxError();
            }
            $('#statsContainerLoading').hide();
        };
        xhr.onerror = () => {
            ajaxError();
            $('#statsContainerLoading').hide();
        };
        xhr.send(data);
    };
};

var isWaiting = {};

function getWaiter() {
    return "<img style='margin:auto;display:block;'class='wait' src='" + imgpath + "/ajax-loader.gif'/>";
}

function graphQuery(id, cmd, success) {
    $.ajax({
        type: "POST",
        url: graphUrl,
        data: {
            'id': id,
            'cmd': cmd,
            'sStatisticsLanguage': sStatisticsLanguage
        },
        success: success,
        error: function (res) {
            ajaxError();
        }
    });
}

function ajaxError() {
    // TODO: Use NotifyFader?
    alert("An error occured! Please reload the page!");
}

function selectCheckboxes(Div, CheckBoxName, Button) {
    var aDiv = document.getElementById(Div);
    var nInput = aDiv.getElementsByTagName("input");
    var Value = document.getElementById(Button).checked;
    //alert(Value);

    for (var i = 0; i < nInput.length; i++) {
        if (nInput[i].getAttribute("name") == CheckBoxName)
            nInput[i].checked = Value;
    }
}

function nographs() {
    document.getElementById('usegraph_2').checked = false;
}

function gMapInit(id, data) {
    if (!data || !data["coord"] || !data["zoom"] ||
        !data.width || !data.height || typeof google == "undefined") {
        return;
    }

    $("#" + id).width(data.width);
    $("#" + id).height(data.height);

    var latlng;
    if (data["coord"].length > 0) {
        var c = data["coord"][0].split(" ");
        latlng = new google.maps.LatLng(parseFloat(c[0]), parseFloat(c[1]));
    } else {
        latlng = new google.maps.LatLng(0.1, 0.1);
    }

    var myOptions = {
        zoom: parseFloat(data["zoom"]),
        center: latlng,
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };

    var map = new google.maps.Map(document.getElementById(id), myOptions);

    for (var i = 0; i < data["coord"].length; ++i) {
        var c = data["coord"][i].split(" ");

        var marker = new google.maps.Marker({
            position: new google.maps.LatLng(parseFloat(c[0]), parseFloat(c[1])),
            map: map
        });
    }
}

function statGetId(elem) {
    var id = $(elem).attr("id");

    if (id.substr(0, 6) == "stats_") {
        return id.substr(6, id.length);
    }

    if (id == '' || isWaiting[id]) {
        return false;
    }

    isWaiting[id] = true;
    return id;
}

function statInit(data) {
    var elem = $("#stats_" + data.id);

    elem.children().hide();

    if (data.sg) {
        $("#statzone_" + data.id).show();
        $(".stats-hidegraph", elem).show();

        if (data.ap) {
            $(".stats-" + (data.sp ? "showbar" : "showpie"), elem).show();
        }

        if (data.am) {
            $(".stats-" + (data.sm ? "hidemap" : "showmap"), elem).show();
        }
    } else {
        $("#statzone_" + data.id).hide();
        $(".stats-showgraph", elem).show();
    }
}

function changeGraphType(cmd, id) {
    id = statGetId(id);
    if (!id) {
        return;
    }

    if (!aStatData[id]) {
        alert('Error');
    }

    if (!aStatData[id].ap) {
        return;
    }

    $("#statzone_" + id).append(getWaiter());

    graphQuery(id, cmd, function (res) {
        if (!res) {
            ajaxError();
            return;
        }

        data = JSON.parse(res);

        if (!data || !data.ok || !data.chartdata) {
            ajaxError();
            return;
        }

        isWaiting[id] = false;
        aStatData[id].sp = (cmd == 'showpie');
        statInit(aStatData[id]);

        $("#statzone_" + id + " .wait").remove();
        $("#statzone_" + id + ">img:first").attr("src", temppath + "/" + data.chartdata);
    });

}


var createPDFworker = function (tableArray) {
    "use strict";
    return new Promise(function (res, rej) {
        var createPDF = new CreatePDF();

        $.each(tableArray, function (i, table) {
            var sizes = { h: $(table).height(), w: $(table).width() };
            var answerObject = createPDF('sendImg', { html: table, sizes: sizes });
        });

        createPDF('getParseHtmlPromise').then(function (resolve) {
            var answerObject = createPDF('exportPdf');
            console.ls.log(answerObject);
            var a = document.createElement('a');
            if(typeof a.download != "undefined") {
                $('body').append("<a id='exportPdf-download-link' style='display:none;' href='" + answerObject.msg + "' download='pdf-survey.pdf'></a>");// Must add sid and other info
                $("#exportPdf-download-link").get(0).click();
                $("#exportPdf-download-link").remove();
                res('done');
                return;
            } 
            var newWindow = window.open("about:blank", 600, 800);
            newWindow.document.write("<html style='height:100%;width:100%'><iframe style='width:100%;height:100%;' src='"+answerObject.msg+"' border=0></iframe></html>");
            res('done');
        }, function (reject) {
            rej(arguments);
        });
    });
};

var createOverlay = function () {
    var overlay = $('<div></div>')
        .attr('id', 'overlay')
        .css({
            position: 'fixed',
            width: "100%",
            height: "100%",
            top: 0,
            "z-index": 5000,
            "pointer-events": 'none',
            left: 0,
            right: 0,
            bottom: 0,
            "background-color": "hsla(0,0%,65%,0.6)"
        });
    $('#statsContainerLoading').clone().css({ display: 'block', position: 'fixed', top: "25%", left: 0, width: "100%" }).appendTo(overlay);
    overlay.appendTo('body');
    return overlay;
};

var exportImages = function () {
    var zip = new JSZip(),
        overlay = createOverlay();

    $('.chartjs-container').loadGraph();
    $('.chartjs-container').each(function (i, container) {
        var canvasElements = $(container).find('canvas');
        if (canvasElements.length > 0) {
            var canvas = canvasElements.get(0);
            var imgData = canvas.toDataURL();
            imgData = imgData.replace(/^data:image\/(png|jpg);base64,/, "");
            zip.file($(container).data('qid') + '.png', imgData, { base64: true });
        }
    });
    zip.generateAsync({ type: "blob" })
        .then(function (content) {
            // see FileSaver.js
            saveAs(content, "allChartImages.zip");
            overlay.remove();
        });
};

$(document).on('ready  pjax:scriptcomplete', function () {
    LS.Statistics2();
    $('body').addClass('onStatistics');
    var exportImagesButton = $('#statisticsExportImages');
    exportImagesButton.on('click', exportImages);
    exportImagesButton.wrap('<div class="col-12 text-center"></div>')
    $('#statisticsview').children('div.row').last().append(exportImagesButton);
    $('body').on('click', '.action_js_export_to_pdf', function () {

        // var thisTable = $('#'+$(this).data('questionId'));
        // domtoimage.toPng(thisTable[0]).then(
        //     function(image){
        //         $('body').prepend($('<img/>').attr('src',image));
        //     }
        // )

        // var thisTable = $('#'+$(this).data('questionId'));
        // console.ls.log(thisTable.html());

        var $self = $(this),
            overlay = createOverlay(),
            thisTable = $('#' + $self.data('questionId'));

        $self.css({ display: 'none' });
        thisTable.find('.chartjs-buttons').closest('tr').css({ display: 'none' });
        createPDFworker.call(null, thisTable).then(
            function (success) {
                overlay.remove();
                thisTable.find('.chartjs-buttons').closest('tr').css({ display: '' });
                $self.css({ display: '' });
            },
            function () { console.ls.error(arguments); }
        )
    });
});

$(document).on('triggerReady', LS.Statistics2);
