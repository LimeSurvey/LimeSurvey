<?php

/** This file is part of KCFinder project
  *
  *      @desc Clipboard functionality
  *   @package KCFinder
  *   @version 2.51
  *    @author Pavel Tzonkov <pavelc@users.sourceforge.net>
  * @copyright 2010, 2011 KCFinder Project
  *   @license http://www.opensource.org/licenses/gpl-2.0.php GPLv2
  *   @license http://www.opensource.org/licenses/lgpl-2.1.php LGPLv2
  *      @link http://kcfinder.sunhater.com
  */?>

browser.initClipboard = function() {
    if (!this.clipboard || !this.clipboard.length) return;
    var size = 0;
    $.each(this.clipboard, function(i, val) {
        size += parseInt(val.size);
    });
    size = this.humanSize(size);
    $('#clipboard').html('<div title="' + this.label("Clipboard") + ' (' + this.clipboard.length + ' ' + this.label("files") + ', ' + size + ')" onclick="browser.openClipboard()"></div>');
    var resize = function() {
        $('#clipboard').css({
            left: $(window).width() - $('#clipboard').outerWidth() + 'px',
            top: $(window).height() - $('#clipboard').outerHeight() + 'px'
        });
    };
    resize();
    $('#clipboard').css('display', 'block');
    $(window).unbind();
    $(window).resize(function() {
        browser.resize();
        resize();
    });
};

browser.openClipboard = function() {
    if (!this.clipboard || !this.clipboard.length) return;
    if ($('.menu a[href="kcact:cpcbd"]').html()) {
        $('#clipboard').removeClass('selected');
        this.hideDialog();
        return;
    }
    var html = '<div class="menu"><div class="list">';
    $.each(this.clipboard, function(i, val) {
        icon = _.getFileExtension(val.name);
        if (val.thumb)
            icon = '.image';
        else if (!val.smallIcon || !icon.length)
            icon = '.';
        var icon = 'themes/' + browser.theme + '/img/files/small/' + icon + '.png';
        html += '<a style="background-image:url(' + _.escapeDirs(icon) + ')" title="' + browser.label("Click to remove from the Clipboard") + '" onclick="browser.removeFromClipboard(' + i + ')">' + _.htmlData(_.basename(val.name)) + '</a>';
    });
    html += '</div><div class="delimiter"></div>';
    if (this.support.zip) html+=
        '<a href="kcact:download">' + this.label("Download files") + '</a>';
    if (this.access.files.copy || this.access.files.move || this.access.files['delete'])
        html += '<div class="delimiter"></div>';
    if (this.access.files.copy)
        html += '<a href="kcact:cpcbd"' + (!browser.dirWritable ? ' class="denied"' : '') + '>' +
            this.label("Copy files here") + '</a>';
    if (this.access.files.move)
        html += '<a href="kcact:mvcbd"' + (!browser.dirWritable ? ' class="denied"' : '') + '>' +
            this.label("Move files here") + '</a>';
    if (this.access.files['delete'])
        html += '<a href="kcact:rmcbd">' + this.label("Delete files") + '</a>';
    html += '<div class="delimiter"></div>' +
        '<a href="kcact:clrcbd">' + this.label("Clear the Clipboard") + '</a>' + '</div>';

    setTimeout(function() {
        $('#clipboard').addClass('selected');
        $('#dialog').html(html);
        $('.menu a[href="kcact:download"]').click(function() {
            browser.hideDialog();
            browser.downloadClipboard();
            return false;
        });
        $('.menu a[href="kcact:cpcbd"]').click(function() {
            if (!browser.dirWritable) return false;
            browser.hideDialog();
            browser.copyClipboard(browser.dir);
            return false;
        });
        $('.menu a[href="kcact:mvcbd"]').click(function() {
            if (!browser.dirWritable) return false;
            browser.hideDialog();
            browser.moveClipboard(browser.dir);
            return false;
        });
        $('.menu a[href="kcact:rmcbd"]').click(function() {
            browser.hideDialog();
            browser.confirm(
                browser.label("Are you sure you want to delete all files in the Clipboard?"),
                function(callBack) {
                    if (callBack) callBack();
                    browser.deleteClipboard();
                }
            );
            return false;
        });
        $('.menu a[href="kcact:clrcbd"]').click(function() {
            browser.hideDialog();
            browser.clearClipboard();
            return false;
        });

        var left = $(window).width() - $('#dialog').outerWidth();
        var top = $(window).height() - $('#dialog').outerHeight() - $('#clipboard').outerHeight();
        var lheight = top + _.outerTopSpace('#dialog');
        $('.menu .list').css('max-height', lheight + 'px');
        var top = $(window).height() - $('#dialog').outerHeight() - $('#clipboard').outerHeight();
        $('#dialog').css({
            left: (left - 4) + 'px',
            top: top + 'px'
        });
        $('#dialog').fadeIn();
    }, 1);
};

browser.removeFromClipboard = function(i) {
    if (!this.clipboard || !this.clipboard[i]) return false;
    if (this.clipboard.length == 1) {
        this.clearClipboard();
        this.hideDialog();
        return;
    }

    if (i < this.clipboard.length - 1) {
        var last = this.clipboard.slice(i + 1);
        this.clipboard = this.clipboard.slice(0, i);
        this.clipboard = this.clipboard.concat(last);
    } else
        this.clipboard.pop();

    this.initClipboard();
    this.hideDialog();
    this.openClipboard();
    return true;
};

browser.copyClipboard = function(dir) {
    if (!this.clipboard || !this.clipboard.length) return;
    var files = [];
    var failed = 0;
    for (i = 0; i < this.clipboard.length; i++)
        if (this.clipboard[i].readable)
            files[i] = this.clipboard[i].dir + '/' + this.clipboard[i].name;
        else
            failed++;
    if (this.clipboard.length == failed) {
        browser.alert(this.label("The files in the Clipboard are not readable."));
        return;
    }
    var go = function(callBack) {
        if (dir == browser.dir)
            browser.fadeFiles();
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: browser.baseGetData('cp_cbd'),
            data: {dir: dir, files: files},
            async: false,
            success: function(data) {
                if (callBack) callBack();
                browser.check4errors(data);
                browser.clearClipboard();
                if (dir == browser.dir)
                    browser.refresh();
            },
            error: function() {
                if (callBack) callBack();
                $('#files > div').css({
                    opacity: '',
                    filter: ''
                });
                browser.alert(browser.label("Unknown error."));
            }
        });
    };

    if (failed)
        browser.confirm(
            browser.label("{count} files in the Clipboard are not readable. Do you want to copy the rest?", {count:failed}),
            go
        )
    else
        go();

};

browser.moveClipboard = function(dir) {
    if (!this.clipboard || !this.clipboard.length) return;
    var files = [];
    var failed = 0;
    for (i = 0; i < this.clipboard.length; i++)
        if (this.clipboard[i].readable && this.clipboard[i].writable)
            files[i] = this.clipboard[i].dir + "/" + this.clipboard[i].name;
        else
            failed++;
    if (this.clipboard.length == failed) {
        browser.alert(this.label("The files in the Clipboard are not movable."))
        return;
    }

    var go = function(callBack) {
        browser.fadeFiles();
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: browser.baseGetData('mv_cbd'),
            data: {dir: dir, files: files},
            async: false,
            success: function(data) {
                if (callBack) callBack();
                browser.check4errors(data);
                browser.clearClipboard();
                browser.refresh();
            },
            error: function() {
                if (callBack) callBack();
                $('#files > div').css({
                    opacity: '',
                    filter: ''
                });
                browser.alert(browser.label("Unknown error."));
            }
        });
    };

    if (failed)
        browser.confirm(
            browser.label("{count} files in the Clipboard are not movable. Do you want to move the rest?", {count: failed}),
            go
        );
    else
        go();
};

browser.deleteClipboard = function() {
    if (!this.clipboard || !this.clipboard.length) return;
    var files = [];
    var failed = 0;
    for (i = 0; i < this.clipboard.length; i++)
        if (this.clipboard[i].readable && this.clipboard[i].writable)
            files[i] = this.clipboard[i].dir + '/' + this.clipboard[i].name;
        else
            failed++;
    if (this.clipboard.length == failed) {
        browser.alert(this.label("The files in the Clipboard are not removable."))
        return;
    }
    var go = function(callBack) {
        browser.fadeFiles();
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: browser.baseGetData('rm_cbd'),
            data: {files:files},
            async: false,
            success: function(data) {
                if (callBack) callBack();
                browser.check4errors(data);
                browser.clearClipboard();
                browser.refresh();
            },
            error: function() {
                if (callBack) callBack();
                $('#files > div').css({
                    opacity: '',
                    filter:''
                });
                browser.alert(browser.label("Unknown error."));
            }
        });
    };
    if (failed)
        browser.confirm(
            browser.label("{count} files in the Clipboard are not removable. Do you want to delete the rest?", {count: failed}),
            go
        );
    else
        go();
};

browser.downloadClipboard = function() {
    if (!this.clipboard || !this.clipboard.length) return;
    var files = [];
    for (i = 0; i < this.clipboard.length; i++)
        if (this.clipboard[i].readable)
            files[i] = this.clipboard[i].dir + '/' + this.clipboard[i].name;
    if (files.length)
        this.post(this.baseGetData('downloadClipboard'), {files:files});
};

browser.clearClipboard = function() {
    $('#clipboard').html('');
    this.clipboard = [];
};
