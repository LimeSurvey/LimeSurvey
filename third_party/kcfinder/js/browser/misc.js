<?php

/** This file is part of KCFinder project
  *
  *      @desc Miscellaneous functionality
  *   @package KCFinder
  *   @version 2.51
  *    @author Pavel Tzonkov <pavelc@users.sourceforge.net>
  * @copyright 2010, 2011 KCFinder Project
  *   @license http://www.opensource.org/licenses/gpl-2.0.php GPLv2
  *   @license http://www.opensource.org/licenses/lgpl-2.1.php LGPLv2
  *      @link http://kcfinder.sunhater.com
  */?>

browser.drag = function(ev, dd) {
    var top = dd.offsetY,
        left = dd.offsetX;
    if (top < 0) top = 0;
    if (left < 0) left = 0;
    if (top + $(this).outerHeight() > $(window).height())
        top = $(window).height() - $(this).outerHeight();
    if (left + $(this).outerWidth() > $(window).width())
        left = $(window).width() - $(this).outerWidth();
    $(this).css({
        top: top,
        left: left
    });
};

browser.showDialog = function(e) {
    $('#dialog').css({left: 0, top: 0});
    this.shadow();
    if ($('#dialog div.box') && !$('#dialog div.title').get(0)) {
        var html = $('#dialog div.box').html();
        var title = $('#dialog').data('title') ? $('#dialog').data('title') : "";
        html = '<div class="title"><span class="close"></span>' + title + '</div>' + html;
        $('#dialog div.box').html(html);
        $('#dialog div.title span.close').mousedown(function() {
            $(this).addClass('clicked');
        });
        $('#dialog div.title span.close').mouseup(function() {
            $(this).removeClass('clicked');
        });
        $('#dialog div.title span.close').click(function() {
            browser.hideDialog();
            browser.hideAlert();
        });
    }
    $('#dialog').drag(browser.drag, {handle: '#dialog div.title'});
    $('#dialog').css('display', 'block');

    if (e) {
        var left = e.pageX - parseInt($('#dialog').outerWidth() / 2);
        var top = e.pageY - parseInt($('#dialog').outerHeight() / 2);
        if (left < 0) left = 0;
        if (top < 0) top = 0;
        if (($('#dialog').outerWidth() + left) > $(window).width())
            left = $(window).width() - $('#dialog').outerWidth();
        if (($('#dialog').outerHeight() + top) > $(window).height())
            top = $(window).height() - $('#dialog').outerHeight();
        $('#dialog').css({
            left: left + 'px',
            top: top + 'px'
        });
    } else
        $('#dialog').css({
            left: parseInt(($(window).width() - $('#dialog').outerWidth()) / 2) + 'px',
            top: parseInt(($(window).height() - $('#dialog').outerHeight()) / 2) + 'px'
        });
    $(document).unbind('keydown');
    $(document).keydown(function(e) {
        if (e.keyCode == 27)
            browser.hideDialog();
    });
};

browser.hideDialog = function() {
    this.unshadow();
    if ($('#clipboard').hasClass('selected'))
        $('#clipboard').removeClass('selected');
    $('#dialog').css('display', 'none');
    $('div.folder > a > span.folder').removeClass('context');
    $('#dialog').html('');
    $('#dialog').data('title', null);
    $('#dialog').unbind();
    $('#dialog').click(function() {
        return false;
    });
    $(document).unbind('keydown');
    $(document).keydown(function(e) {
        return !browser.selectAll(e);
    });
    browser.hideAlert();
};

browser.showAlert = function(shadow) {
    $('#alert').css({left: 0, top: 0});
    if (typeof shadow == 'undefined')
        shadow = true;
    if (shadow)
        this.shadow();
    var left = parseInt(($(window).width() - $('#alert').outerWidth()) / 2),
        top = parseInt(($(window).height() - $('#alert').outerHeight()) / 2);
    var wheight = $(window).height();
    if (top < 0)
        top = 0;
    $('#alert').css({
        left: left + 'px',
        top: top + 'px',
        display: 'block'
    });
    if ($('#alert').outerHeight() > wheight) {
        $('#alert div.message').css({
            height: wheight - $('#alert div.title').outerHeight() - $('#alert div.ok').outerHeight() - 20 + 'px'
        });
    }
    $(document).unbind('keydown');
    $(document).keydown(function(e) {
        if (e.keyCode == 27) {
            browser.hideDialog();
            browser.hideAlert();
            $(document).unbind('keydown');
            $(document).keydown(function(e) {
                return !browser.selectAll(e);
            });
        }
    });
};

browser.hideAlert = function(shadow) {
    if (typeof shadow == 'undefined')
        shadow = true;
    if (shadow)
        this.unshadow();
    $('#alert').css('display', 'none');
    $('#alert').html('');
    $('#alert').data('title', null);
};

browser.alert = function(msg, shadow) {
    msg = msg.replace(/\r?\n/g, "<br />");
    var title = $('#alert').data('title') ? $('#alert').data('title') : browser.label("Attention");
    $('#alert').html('<div class="title"><span class="close"></span>' + title + '</div><div class="message">' + msg + '</div><div class="ok"><button>' + browser.label("OK") + '</button></div>');
    $('#alert div.ok button').click(function() {
        browser.hideAlert(shadow);
    });
    $('#alert div.title span.close').mousedown(function() {
        $(this).addClass('clicked');
    });
    $('#alert div.title span.close').mouseup(function() {
        $(this).removeClass('clicked');
    });
    $('#alert div.title span.close').click(function() {
        browser.hideAlert(shadow);
    });
    $('#alert').drag(browser.drag, {handle: "#alert div.title"});
    browser.showAlert(shadow);
};

browser.confirm = function(question, callBack) {
    $('#dialog').data('title', browser.label("Question"));
    $('#dialog').html('<div class="box"><div class="question">' + browser.label(question) + '<div class="buttons"><button>' + browser.label("No") + '</button> <button>' + browser.label("Yes") + '</button></div></div></div>');
    browser.showDialog();
    $('#dialog div.buttons button').first().click(function() {
        browser.hideDialog();
    });
    $('#dialog div.buttons button').last().click(function() {
        if (callBack)
            callBack(function() {
                browser.hideDialog();
            });
        else
            browser.hideDialog();
    });
    $('#dialog div.buttons button').get(1).focus();
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
    $('#dialog').css({
        left: left + 'px',
        top: top + 'px',
        display: 'none'
    });
    $('#dialog').fadeIn();
};

browser.fileNameDialog = function(e, post, inputName, inputValue, url, labels, callBack, selectAll) {
    var html = '<form method="post" action="javascript:;">' +
        '<div class="box">' +
        '<input name="' + inputName + '" type="text" /><br />' +
        '<div style="text-align:right">' +
        '<input type="submit" value="' + _.htmlValue(this.label("OK")) + '" /> ' +
        '<input type="button" value="' + _.htmlValue(this.label("Cancel")) + '" onclick="browser.hideDialog(); browser.hideAlert(); return false" />' +
    '</div></div></form>';
    $('#dialog').html(html);
    $('#dialog').data('title', this.label(labels.title));
    $('#dialog input[name="' + inputName + '"]').attr('value', inputValue);
    $('#dialog').unbind();
    $('#dialog').click(function() {
        return false;
    });
    $('#dialog form').submit(function() {
        var name = this.elements[0];
        name.value = $.trim(name.value);
        if (name.value == '') {
            browser.alert(browser.label(labels.errEmpty), false);
            name.focus();
            return;
        } else if (/[\/\\]/g.test(name.value)) {
            browser.alert(browser.label(labels.errSlash), false);
            name.focus();
            return;
        } else if (name.value.substr(0, 1) == ".") {
            browser.alert(browser.label(labels.errDot), false);
            name.focus();
            return;
        }
        eval('post.' + inputName + ' = name.value;');
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: url,
            data: post,
            async: false,
            success: function(data) {
                if (browser.check4errors(data, false))
                    return;
                if (callBack) callBack(data);
                browser.hideDialog();
            },
            error: function() {
                browser.alert(browser.label("Unknown error."), false);
            }
        });
        return false;
    });
    browser.showDialog(e);
    $('#dialog').css('display', 'block');
    $('#dialog input[type="submit"]').click(function() {
        return $('#dialog form').submit();
    });
    var field = $('#dialog input[type="text"]');
    var value = field.attr('value');
    if (!selectAll && /^(.+)\.[^\.]+$/ .test(value)) {
        value = value.replace(/^(.+)\.[^\.]+$/, "$1");
        _.selection(field.get(0), 0, value.length);
    } else {
        field.get(0).focus();
        field.get(0).select();
    }
};

browser.orderFiles = function(callBack, selected) {
    var order = _.kuki.get('order');
    var desc = (_.kuki.get('orderDesc') == 'on');

    if (!browser.files || !browser.files.sort)
        browser.files = [];

    browser.files = browser.files.sort(function(a, b) {
        var a1, b1, arr;
        if (!order) order = 'name';

        if (order == 'date') {
            a1 = a.mtime;
            b1 = b.mtime;
        } else if (order == 'type') {
            a1 = _.getFileExtension(a.name);
            b1 = _.getFileExtension(b.name);
        } else if (order == 'size') {
            a1 = a.size;
            b1 = b.size;
        } else
            eval('a1 = a.' + order + '.toLowerCase(); b1 = b.' + order + '.toLowerCase();');

        if ((order == 'size') || (order == 'date')) {
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
        data += "&act=" + act;
    if (this.cms)
        data += "&cms=" + this.cms;
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

browser.check4errors = function(data, shadow) {
    if (!data.error)
        return false;
    var msg;
    if (data.error.join)
        msg = data.error.join("\n");
    else
        msg = data.error;
    browser.alert(msg, shadow);
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
    $('#files > div').css({
        opacity: '0.4',
        filter: 'alpha(opacity:40)'
    });
};
