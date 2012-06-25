<?php

/** This file is part of KCFinder project
  *
  *      @desc Object initializations
  *   @package KCFinder
  *   @version 2.51
  *    @author Pavel Tzonkov <pavelc@users.sourceforge.net>
  * @copyright 2010, 2011 KCFinder Project
  *   @license http://www.opensource.org/licenses/gpl-2.0.php GPLv2
  *   @license http://www.opensource.org/licenses/lgpl-2.1.php LGPLv2
  *      @link http://kcfinder.sunhater.com
  */?>

browser.init = function() {
    if (!this.checkAgent()) return;

    $('body').click(function() {
        browser.hideDialog();
    });
    $('#shadow').click(function() {
        return false;
    });
    $('#dialog').unbind();
    $('#dialog').click(function() {
        return false;
    });
    $('#alert').unbind();
    $('#alert').click(function() {
        return false;
    });
    this.initOpeners();
    this.initSettings();
    this.initContent();
    this.initToolbar();
    this.initResizer();
    this.initDropUpload();
};

browser.checkAgent = function() {
    if (!$.browser.version ||
        ($.browser.msie && (parseInt($.browser.version) < 7) && !this.support.chromeFrame) ||
        ($.browser.opera && (parseInt($.browser.version) < 10)) ||
        ($.browser.mozilla && (parseFloat($.browser.version.replace(/^(\d+(\.\d+)?)([^\d].*)?$/, "$1")) < 1.8))
    ) {
        var html = '<div style="padding:10px">Your browser is not capable to display KCFinder. Please update your browser or install another one: <a href="http://www.mozilla.com/firefox/" target="_blank">Mozilla Firefox</a>, <a href="http://www.apple.com/safari" target="_blank">Apple Safari</a>, <a href="http://www.google.com/chrome" target="_blank">Google Chrome</a>, <a href="http://www.opera.com/browser" target="_blank">Opera</a>.';
        if ($.browser.msie)
            html += ' You may also install <a href="http://www.google.com/chromeframe" target="_blank">Google Chrome Frame ActiveX plugin</a> to get Internet Explorer 6 working.';
        html += '</div>';
        $('body').html(html);
        return false;
    }
    return true;
};

browser.initOpeners = function() {
    if (this.opener.TinyMCE && (typeof(tinyMCEPopup) == 'undefined'))
        this.opener.TinyMCE = null;

    if (this.opener.TinyMCE)
        this.opener.callBack = true;

    if ((!this.opener.name || (this.opener.name == 'fckeditor')) &&
        window.opener && window.opener.SetUrl
    ) {
        this.opener.FCKeditor = true;
        this.opener.callBack = true;
    }

    if (this.opener.CKEditor) {
        if (window.parent && window.parent.CKEDITOR)
            this.opener.CKEditor.object = window.parent.CKEDITOR;
        else if (window.opener && window.opener.CKEDITOR) {
            this.opener.CKEditor.object = window.opener.CKEDITOR;
            this.opener.callBack = true;
        } else
            this.opener.CKEditor = null;
    }

    if (!this.opener.CKEditor && !this.opener.FCKEditor && !this.TinyMCE) {
        if ((window.opener && window.opener.KCFinder && window.opener.KCFinder.callBack) ||
            (window.parent && window.parent.KCFinder && window.parent.KCFinder.callBack)
        )
            this.opener.callBack = window.opener
                ? window.opener.KCFinder.callBack
                : window.parent.KCFinder.callBack;

        if ((
                window.opener &&
                window.opener.KCFinder &&
                window.opener.KCFinder.callBackMultiple
            ) || (
                window.parent &&
                window.parent.KCFinder &&
                window.parent.KCFinder.callBackMultiple
            )
        )
            this.opener.callBackMultiple = window.opener
                ? window.opener.KCFinder.callBackMultiple
                : window.parent.KCFinder.callBackMultiple;
    }
};

browser.initContent = function() {
    $('div#folders').html(this.label("Loading folders..."));
    $('div#files').html(this.label("Loading files..."));
    $.ajax({
        type: 'GET',
        dataType: 'json',
        url: browser.baseGetData('init'),
        async: false,
        success: function(data) {
            if (browser.check4errors(data))
                return;
            browser.dirWritable = data.dirWritable;
            $('#folders').html(browser.buildTree(data.tree));
            browser.setTreeData(data.tree);
            browser.initFolders();
            browser.files = data.files ? data.files : [];
            browser.orderFiles();
        },
        error: function() {
            $('div#folders').html(browser.label("Unknown error."));
            $('div#files').html(browser.label("Unknown error."));
        }
    });
};

browser.initResizer = function() {
    var cursor = ($.browser.opera) ? 'move' : 'col-resize';
    $('#resizer').css('cursor', cursor);
    $('#resizer').drag('start', function() {
        $(this).css({opacity:'0.4', filter:'alpha(opacity:40)'});
        $('#all').css('cursor', cursor);
    });
    $('#resizer').drag(function(e) {
        var left = e.pageX - parseInt(_.nopx($(this).css('width')) / 2);
        left = (left >= 0) ? left : 0;
        left = (left + _.nopx($(this).css('width')) < $(window).width())
            ? left : $(window).width() - _.nopx($(this).css('width'));
		$(this).css('left', left);
	});
	var end = function() {
        $(this).css({opacity:'0', filter:'alpha(opacity:0)'});
        $('#all').css('cursor', '');
        var left = _.nopx($(this).css('left')) + _.nopx($(this).css('width'));
        var right = $(window).width() - left;
        $('#left').css('width', left + 'px');
        $('#right').css('width', right + 'px');
        _('files').style.width = $('#right').innerWidth() - _.outerHSpace('#files') + 'px';
        _('resizer').style.left = $('#left').outerWidth() - _.outerRightSpace('#folders', 'm') + 'px';
        _('resizer').style.width = _.outerRightSpace('#folders', 'm') + _.outerLeftSpace('#files', 'm') + 'px';
        browser.fixFilesHeight();
    };
    $('#resizer').drag('end', end);
    $('#resizer').mouseup(end);
};

browser.resize = function() {
    _('left').style.width = '25%';
    _('right').style.width = '75%';
    _('toolbar').style.height = $('#toolbar a').outerHeight() + "px";
    _('shadow').style.width = $(window).width() + 'px';
    _('shadow').style.height = _('resizer').style.height = $(window).height() + 'px';
    _('left').style.height = _('right').style.height =
        $(window).height() - $('#status').outerHeight() + 'px';
    _('folders').style.height =
        $('#left').outerHeight() - _.outerVSpace('#folders') + 'px';
    browser.fixFilesHeight();
    var width = $('#left').outerWidth() + $('#right').outerWidth();
    _('status').style.width = width + 'px';
    while ($('#status').outerWidth() > width)
        _('status').style.width = _.nopx(_('status').style.width) - 1 + 'px';
    while ($('#status').outerWidth() < width)
        _('status').style.width = _.nopx(_('status').style.width) + 1 + 'px';
    if ($.browser.msie && ($.browser.version.substr(0, 1) < 8))
        _('right').style.width = $(window).width() - $('#left').outerWidth() + 'px';
    _('files').style.width = $('#right').innerWidth() - _.outerHSpace('#files') + 'px';
    _('resizer').style.left = $('#left').outerWidth() - _.outerRightSpace('#folders', 'm') + 'px';
    _('resizer').style.width = _.outerRightSpace('#folders', 'm') + _.outerLeftSpace('#files', 'm') + 'px';
};

browser.fixFilesHeight = function() {
    _('files').style.height =
        $('#left').outerHeight() - $('#toolbar').outerHeight() - _.outerVSpace('#files') -
        (($('#settings').css('display') != "none") ? $('#settings').outerHeight() : 0) + 'px';
};
