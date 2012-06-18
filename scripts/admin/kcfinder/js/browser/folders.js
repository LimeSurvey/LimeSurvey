<?php

/** This file is part of KCFinder project
  *
  *      @desc Folder related functionality
  *   @package KCFinder
  *   @version 2.51
  *    @author Pavel Tzonkov <pavelc@users.sourceforge.net>
  * @copyright 2010, 2011 KCFinder Project
  *   @license http://www.opensource.org/licenses/gpl-2.0.php GPLv2
  *   @license http://www.opensource.org/licenses/lgpl-2.1.php LGPLv2
  *      @link http://kcfinder.sunhater.com
  */?>

browser.initFolders = function() {
    $('#folders').scroll(function() {
        browser.hideDialog();
    });
    $('div.folder > a').unbind();
    $('div.folder > a').bind('click', function() {
        browser.hideDialog();
        return false;
    });
    $('div.folder > a > span.brace').unbind();
    $('div.folder > a > span.brace').click(function() {
        if ($(this).hasClass('opened') || $(this).hasClass('closed'))
            browser.expandDir($(this).parent());
    });
    $('div.folder > a > span.folder').unbind();
    $('div.folder > a > span.folder').click(function() {
        browser.changeDir($(this).parent());
    });
    $('div.folder > a > span.folder').rightClick(function(e) {
        _.unselect();
        browser.menuDir($(this).parent(), e);
    });

    if ($.browser.msie && $.browser.version &&
        (parseInt($.browser.version.substr(0, 1)) < 8)
    ) {
        var fls = $('div.folder').get();
        var body = $('body').get(0);
        var div;
        $.each(fls, function(i, folder) {
            div = document.createElement('div');
            div.style.display = 'inline';
            div.style.margin = div.style.border = div.style.padding = '0';
            div.innerHTML='<table style="border-collapse:collapse;border:0;margin:0;width:0"><tr><td nowrap="nowrap" style="white-space:nowrap;padding:0;border:0">' + $(folder).html() + "</td></tr></table>";
            body.appendChild(div);
            $(folder).css('width', $(div).innerWidth() + 'px');
            body.removeChild(div);
        });
    }
};

browser.setTreeData = function(data, path) {
    if (!path)
        path = '';
    else if (path.length && (path.substr(path.length - 1, 1) != '/'))
        path += '/';
    path += data.name;
    var selector = '#folders a[href="kcdir:/' + _.escapeDirs(path) + '"]';
    $(selector).data({
        name: data.name,
        path: path,
        readable: data.readable,
        writable: data.writable,
        removable: data.removable,
        hasDirs: data.hasDirs
    });
    $(selector + ' span.folder').addClass(data.current ? 'current' : 'regular');
    if (data.dirs && data.dirs.length) {
        $(selector + ' span.brace').addClass('opened');
        $.each(data.dirs, function(i, cdir) {
            browser.setTreeData(cdir, path + '/');
        });
    } else if (data.hasDirs)
        $(selector + ' span.brace').addClass('closed');
};

browser.buildTree = function(root, path) {
    if (!path) path = "";
    path += root.name;
    var html = '<div class="folder"><a href="kcdir:/' + _.escapeDirs(path) + '"><span class="brace">&nbsp;</span><span class="folder">' + _.htmlData(root.name) + '</span></a>';
    if (root.dirs) {
        html += '<div class="folders">';
        for (var i = 0; i < root.dirs.length; i++) {
            cdir = root.dirs[i];
            html += browser.buildTree(cdir, path + '/');
        }
        html += '</div>';
    }
    html += '</div>';
    return html;
};

browser.expandDir = function(dir) {
    var path = dir.data('path');
    if (dir.children('.brace').hasClass('opened')) {
        dir.parent().children('.folders').hide(500, function() {
            if (path == browser.dir.substr(0, path.length))
                browser.changeDir(dir);
        });
        dir.children('.brace').removeClass('opened');
        dir.children('.brace').addClass('closed');
    } else {
        if (dir.parent().children('.folders').get(0)) {
            dir.parent().children('.folders').show(500);
            dir.children('.brace').removeClass('closed');
            dir.children('.brace').addClass('opened');
        } else if (!$('#loadingDirs').get(0)) {
            dir.parent().append('<div id="loadingDirs">' + this.label("Loading folders...") + '</div>');
            $('#loadingDirs').css('display', 'none');
            $('#loadingDirs').show(200, function() {
                $.ajax({
                    type: 'POST',
                    dataType: 'json',
                    url: browser.baseGetData('expand'),
                    data: {dir:path},
                    async: false,
                    success: function(data) {
                        $('#loadingDirs').hide(200, function() {
                            $('#loadingDirs').detach();
                        });
                        if (browser.check4errors(data))
                            return;

                        var html = '';
                        $.each(data.dirs, function(i, cdir) {
                            html += '<div class="folder"><a href="kcdir:/' + _.escapeDirs(path + '/' + cdir.name) + '"><span class="brace">&nbsp;</span><span class="folder">' + _.htmlData(cdir.name) + '</span></a></div>';
                        });
                        if (html.length) {
                            dir.parent().append('<div class="folders">' + html + '</div>');
                            var folders = $(dir.parent().children('.folders').first());
                            folders.css('display', 'none');
                            $(folders).show(500);
                            $.each(data.dirs, function(i, cdir) {
                                browser.setTreeData(cdir, path);
                            });
                        }
                        if (data.dirs.length) {
                            dir.children('.brace').removeClass('closed');
                            dir.children('.brace').addClass('opened');
                        } else {
                            dir.children('.brace').removeClass('opened');
                            dir.children('.brace').removeClass('closed');
                        }
                        browser.initFolders();
                        browser.initDropUpload();
                    },
                    error: function() {
                        $('#loadingDirs').detach();
                        browser.alert(browser.label("Unknown error."));
                    }
                });
            });
        }
    }
};

browser.changeDir = function(dir) {
    if (dir.children('span.folder').hasClass('regular')) {
        $('div.folder > a > span.folder').removeClass('current');
        $('div.folder > a > span.folder').removeClass('regular');
        $('div.folder > a > span.folder').addClass('regular');
        dir.children('span.folder').removeClass('regular');
        dir.children('span.folder').addClass('current');
        $('#files').html(browser.label("Loading files..."));
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: browser.baseGetData('chDir'),
            data: {dir:dir.data('path')},
            async: false,
            success: function(data) {
                if (browser.check4errors(data))
                    return;
                browser.files = data.files;
                browser.orderFiles();
                browser.dir = dir.data('path');
                browser.dirWritable = data.dirWritable;
                var title = "KCFinder: /" + browser.dir;
                document.title = title;
                if (browser.opener.TinyMCE)
                    tinyMCEPopup.editor.windowManager.setTitle(window, title);
                browser.statusDir();
            },
            error: function() {
                $('#files').html(browser.label("Unknown error."));
            }
        });
    }
};

browser.statusDir = function() {
    for (var i = 0, size = 0; i < this.files.length; i++)
        size += parseInt(this.files[i].size);
    size = this.humanSize(size);
    $('#fileinfo').html(this.files.length + ' ' + this.label("files") + ' (' + size + ')');
};

browser.menuDir = function(dir, e) {
    var data = dir.data();
    var html = '<div class="menu">';
    if (this.clipboard && this.clipboard.length) {
        if (this.access.files.copy)
            html += '<a href="kcact:cpcbd"' + (!data.writable ? ' class="denied"' : '') + '>' +
                this.label("Copy {count} files", {count: this.clipboard.length}) + '</a>';
        if (this.access.files.move)
            html += '<a href="kcact:mvcbd"' + (!data.writable ? ' class="denied"' : '') + '>' +
                this.label("Move {count} files", {count: this.clipboard.length}) + '</a>';
        if (this.access.files.copy || this.access.files.move)
            html += '<div class="delimiter"></div>';
    }
    html +=
        '<a href="kcact:refresh">' + this.label("Refresh") + '</a>';
    if (this.support.zip) html+=
        '<div class="delimiter"></div>' +
        '<a href="kcact:download">' + this.label("Download") + '</a>';
    if (this.access.dirs.create || this.access.dirs.rename || this.access.dirs['delete'])
        html += '<div class="delimiter"></div>';
    if (this.access.dirs.create)
        html += '<a href="kcact:mkdir"' + (!data.writable ? ' class="denied"' : '') + '>' +
            this.label("New Subfolder...") + '</a>';
    if (this.access.dirs.rename)
        html += '<a href="kcact:mvdir"' + (!data.removable ? ' class="denied"' : '') + '>' +
            this.label("Rename...") + '</a>';
    if (this.access.dirs['delete'])
        html += '<a href="kcact:rmdir"' + (!data.removable ? ' class="denied"' : '') + '>' +
            this.label("Delete") + '</a>';
    html += '</div>';

    $('#dialog').html(html);
    this.showMenu(e);
    $('div.folder > a > span.folder').removeClass('context');
    if (dir.children('span.folder').hasClass('regular'))
        dir.children('span.folder').addClass('context');

    if (this.clipboard && this.clipboard.length && data.writable) {

        $('.menu a[href="kcact:cpcbd"]').click(function() {
            browser.hideDialog();
            browser.copyClipboard(data.path);
            return false;
        });

        $('.menu a[href="kcact:mvcbd"]').click(function() {
            browser.hideDialog();
            browser.moveClipboard(data.path);
            return false;
        });
    }

    $('.menu a[href="kcact:refresh"]').click(function() {
        browser.hideDialog();
        browser.refreshDir(dir);
        return false;
    });

    $('.menu a[href="kcact:download"]').click(function() {
        browser.hideDialog();
        browser.post(browser.baseGetData('downloadDir'), {dir:data.path});
        return false;
    });

    $('.menu a[href="kcact:mkdir"]').click(function(e) {
        if (!data.writable) return false;
        browser.hideDialog();
        browser.fileNameDialog(
            e, {dir: data.path},
            'newDir', '', browser.baseGetData('newDir'), {
                title: "New folder name:",
                errEmpty: "Please enter new folder name.",
                errSlash: "Unallowable characters in folder name.",
                errDot: "Folder name shouldn't begins with '.'"
            }, function() {
                browser.refreshDir(dir);
                browser.initDropUpload();
                if (!data.hasDirs) {
                    dir.data('hasDirs', true);
                    dir.children('span.brace').addClass('closed');
                }
            }
        );
        return false;
    });

    $('.menu a[href="kcact:mvdir"]').click(function(e) {
        if (!data.removable) return false;
        browser.hideDialog();
        browser.fileNameDialog(
            e, {dir: data.path},
            'newName', data.name, browser.baseGetData('renameDir'), {
                title: "New folder name:",
                errEmpty: "Please enter new folder name.",
                errSlash: "Unallowable characters in folder name.",
                errDot: "Folder name shouldn't begins with '.'"
            }, function(dt) {
                if (!dt.name) {
                    browser.alert(browser.label("Unknown error."));
                    return;
                }
                var currentDir = (data.path == browser.dir);
                dir.children('span.folder').html(_.htmlData(dt.name));
                dir.data('name', dt.name);
                dir.data('path', _.dirname(data.path) + '/' + dt.name);
                if (currentDir)
                    browser.dir = dir.data('path');
                browser.initDropUpload();
            },
            true
        );
        return false;
    });

    $('.menu a[href="kcact:rmdir"]').click(function() {
        if (!data.removable) return false;
        browser.hideDialog();
        browser.confirm(
            "Are you sure you want to delete this folder and all its content?",
            function(callBack) {
                 $.ajax({
                    type: 'POST',
                    dataType: 'json',
                    url: browser.baseGetData('deleteDir'),
                    data: {dir: data.path},
                    async: false,
                    success: function(data) {
                        if (callBack) callBack();
                        if (browser.check4errors(data))
                            return;
                        dir.parent().hide(500, function() {
                            var folders = dir.parent().parent();
                            var pDir = folders.parent().children('a').first();
                            dir.parent().detach();
                            if (!folders.children('div.folder').get(0)) {
                                pDir.children('span.brace').first().removeClass('opened');
                                pDir.children('span.brace').first().removeClass('closed');
                                pDir.parent().children('.folders').detach();
                                pDir.data('hasDirs', false);
                            }
                            if (pDir.data('path') == browser.dir.substr(0, pDir.data('path').length))
                                browser.changeDir(pDir);
                            browser.initDropUpload();
                        });
                    },
                    error: function() {
                        if (callBack) callBack();
                        browser.alert(browser.label("Unknown error."));
                    }
                });
            }
        );
        return false;
    });
};

browser.refreshDir = function(dir) {
    var path = dir.data('path');
    if (dir.children('.brace').hasClass('opened') || dir.children('.brace').hasClass('closed')) {
        dir.children('.brace').removeClass('opened');
        dir.children('.brace').addClass('closed');
    }
    dir.parent().children('.folders').first().detach();
    if (path == browser.dir.substr(0, path.length))
        browser.changeDir(dir);
    browser.expandDir(dir);
    return true;
};
