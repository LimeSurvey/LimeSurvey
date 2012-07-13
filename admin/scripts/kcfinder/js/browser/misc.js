<?php

/** This file is part of KCFinder project
  *
  *      @desc Miscellaneous methods
  *   @package KCFinder
  *   @version 2.21
  *    @author Pavel Tzonkov <pavelc@users.sourceforge.net>
  * @copyright 2010 KCFinder Project
  *   @license http://www.opensource.org/licenses/gpl-2.0.php GPLv2
  *   @license http://www.opensource.org/licenses/lgpl-2.1.php LGPLv2
  *      @link http://kcfinder.sunhater.com
  */?>

browser.showDialog = function(e) {
    this.shadow();
    if (e) {
        var left = e.pageX - parseInt($('#dialog').outerWidth() / 2);
        var top = e.pageY - parseInt($('#dialog').outerHeight() / 2);
        if (left < 15) left = 15;
        if (top < 15) top = 15;
        if (($('#dialog').outerWidth() + left) > $(window).width() - 30)
            left = $(window).width() - $('#dialog').outerWidth() - 15;
        if (($('#dialog').outerHeight() + top) > $(window).height() - 30)
            top = $(window).height() - $('#dialog').outerHeight() - 15;
        $('#dialog').css('left', left + "px");
        $('#dialog').css('top', top + "px");
    } else {
        $('#dialog').css('left', parseInt(($(window).width() - $('#dialog').outerWidth()) / 2) + 'px');
        $('#dialog').css('top', parseInt(($(window).height() - $('#dialog').outerHeight()) / 2) + 'px');
        $('#dialog').css('display', 'block');
    }

};

browser.hideDialog = function() {
    this.unshadow();
    if ($('#clipboard').hasClass('selected'))
        $('#clipboard').removeClass('selected');
    $('#dialog').css('display', 'none');
    $('div.folder > a > span.folder').removeClass('context');
    $('#dialog').html('');
};

browser.shadow = function() {
    $('#shadow').css('display', 'block');
};

browser.unshadow = function() {
    $('#shadow').css('display', 'none');
};

browser.showMenu = function(e) {
    var left = e.pageX;
    var top = e.pageY;
    if (($('#dialog').outerWidth() + left) > $(window).width())
        left = $(window).width() - $('#dialog').outerWidth();
    if (($('#dialog').outerHeight() + top) > $(window).height())
        top = $(window).height() - $('#dialog').outerHeight();
    $('#dialog').css('left', left + "px");
    $('#dialog').css('top', top + "px");
    $('#dialog').css('display', 'none');
    $('#dialog').fadeIn();
};

browser.fileNameDialog = function(e, post, inputName, inputValue, url, labels, callBack) {
    var html = '<form method="post" action="javascript:;">' +
        '<div class="box"><b>' + this.label(labels.title) + '</b><br />' +
        '<input name="' + inputName + '" value="' + _.htmlValue(inputValue) + '" type="text" /><br />' +
        '<div style="text-align:right">' +
        '<input type="submit" value="' + _.htmlValue(this.label("OK")) + '" />' +
        '<input type="button" value="' + _.htmlValue(this.label("Cancel")) + '" onclick="browser.hideDialog(); return false" />' +
    '</div></div></form>';
    $('#dialog').html(html);
    $('#dialog').unbind();
    $('#dialog').click(function() {
        return false;
    });
    $('#dialog form').submit(function() {
        var name = this.elements[0];
        name.value = $.trim(name.value);
        if (name.value == '') {
            alert(browser.label(labels.errEmpty));
            name.focus();
            return;
        } else if (/[\/\\]/g.test(name.value)) {
            alert(browser.label(labels.errSlash))
            name.focus();
            return;
        } else if (name.value.substr(0, 1) == ".") {
            alert(browser.label(labels.errDot))
            name.focus();
            return;
        }
        eval('post.' + inputName + ' = name.value;');
        $.ajax({
            type: 'POST',
            url: url,
            data: post,
            async: false,
            success: function(xml) {
                if (browser.errors(xml)) return;
                if (callBack) callBack(xml);
                browser.hideDialog();
            },
            error: function(request, error) {
                alert(browser.label("Unknown error."));
            }
        });
        return false;
    });
    browser.showDialog(e);
    $('#dialog').css('display', 'block');
    $('#dialog input[type="submit"]').click(function() {
        return $('#dialog form').submit();
    });
    $('#dialog input[type="text"]').get(0).focus();
    $('#dialog input[type="text"]').get(0).select();
    $('#dialog input[type="text"]').keypress(function(e) {
        if (e.keyCode == 27) browser.hideDialog();
    });
};

browser.orderFiles = function(callBack, selected) {
    var order = _.kuki.get('order');
    var desc = (_.kuki.get('orderDesc') == 'on');

    browser.files = browser.files.sort(function(a, b) {
        var a1, b1, arr;
        if (!order) order = 'name';

        if (order == 'date') {
            a1 = a.mtime;
            b1 = b.mtime;
        } else if (order == 'type') {
            a1 = _.getFileExtension(a.name);
            b1 = _.getFileExtension(b.name);
        } else
            eval('a1 = a.' + order + '.toLowerCase(); b1 = b.' + order + '.toLowerCase();');

        if ((order == 'size') || (order == 'date')) {
            a1 = parseInt(a1 ? a1 : '');
            b1 = parseInt(b1 ? b1 : '');
            if (a1 < b1) return desc ? 1 : -1;
            if (a1 > b1) return desc ? -1 : 1;
        }

        if (a1 == b1) {
            a1 = a.name.toLowerCase();
            b1 = b.name.toLowerCase();
            arr = [a1, b1];
            arr = arr.sort();
            return (arr[0] == a1) ? -1 : 1;
        }

        arr = [a1, b1];
        arr = arr.sort();
        if (arr[0] == a1) return desc ? 1 : -1;
        return desc ? -1 : 1;
    });

    browser.showFiles(callBack, selected);
    browser.initFiles();
};

browser.humanSize = function(size) {
    if (size < 1024) {
        size = size.toString() + ' B';
    } else if (size < 1048576) {
        size /= 1024;
        size = parseInt(size).toString() + ' KB';
    } else if (size < 1073741824) {
        size /= 1048576;
        size = parseInt(size).toString() + ' MB';
    } else if (size < 1099511627776) {
        size /= 1073741824;
        size = parseInt(size).toString() + ' GB';
    } else {
        size /= 1099511627776;
        size = parseInt(size).toString() + ' TB';
    }
    return size;
};

browser.baseGetData = function(act) {
    var data = 'browse.php?type=' + encodeURIComponent(this.type) + '&lng=' + this.lang;
    if (act)
        data += "&act=" + act
    return data;
};

browser.label = function(index, data) {
    var label = this.labels[index] ? this.labels[index] : index;
    if (data)
        $.each(data, function(key, val) {
            label = label.replace('{' + key + '}', val);
        });
    return label;
};

browser.errors = function(xml) {
    if (!xml.getElementsByTagName('error').length)
        return false;
    var alertMsg = '';
    $.each(xml.getElementsByTagName('error'), function(i, error) {
        alertMsg += browser.xmlData(error.childNodes) + "\n";
    });
    alertMsg = alertMsg.substr(0, alertMsg.length - 1);
    alert(alertMsg);
    return true;
};

browser.post = function(url, data) {
    var html = '<form id="postForm" method="POST" action="' + url + '">';
    $.each(data, function(key, val) {
        if ($.isArray(val))
            $.each(val, function(i, aval) {
                html += '<input type="hidden" name="' + _.htmlValue(key) + '[]" value="' + _.htmlValue(aval) + '" />';
            });
        else
            html += '<input type="hidden" name="' + _.htmlValue(key) + '" value="' + _.htmlValue(val) + '" />';
    });
    html += '</form>';
    $('#dialog').html(html);
    $('#dialog').css('display', 'block');
    $('#postForm').get(0).submit();
};

browser.fadeFiles = function() {
    $('#files > div').css('opacity', '0.4');
    $('#files > div').css('filter', 'alpha(opacity:40)');
};

browser.xmlData = function(nodes) {
    var data = '';
    $.each(nodes, function(i) {
        data += nodes[i].nodeValue;
    });
    return data;
};