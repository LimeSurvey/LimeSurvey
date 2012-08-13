<?php

/** This file is part of KCFinder project
  *
  *      @desc Toolbar functionality
  *   @package KCFinder
  *   @version 2.51
  *    @author Pavel Tzonkov <pavelc@users.sourceforge.net>
  * @copyright 2010, 2011 KCFinder Project
  *   @license http://www.opensource.org/licenses/gpl-2.0.php GPLv2
  *   @license http://www.opensource.org/licenses/lgpl-2.1.php LGPLv2
  *      @link http://kcfinder.sunhater.com
  */?>

browser.initToolbar = function() {
    $('#toolbar a').click(function() {
        browser.hideDialog();
    });

    if (!_.kuki.isSet('displaySettings'))
        _.kuki.set('displaySettings', 'off');

    if (_.kuki.get('displaySettings') == 'on') {
        $('#toolbar a[href="kcact:settings"]').addClass('selected');
        $('#settings').css('display', 'block');
        browser.resize();
    }

    $('#toolbar a[href="kcact:settings"]').click(function () {
        if ($('#settings').css('display') == 'none') {
            $(this).addClass('selected');
            _.kuki.set('displaySettings', 'on');
            $('#settings').css('display', 'block');
            browser.fixFilesHeight();
        } else {
            $(this).removeClass('selected');
            _.kuki.set('displaySettings', 'off');
            $('#settings').css('display', 'none');
            browser.fixFilesHeight();
        }
        return false;
    });

    $('#toolbar a[href="kcact:refresh"]').click(function() {
        browser.refresh();
        return false;
    });

    if (window.opener || this.opener.TinyMCE || $('iframe', window.parent.document).get(0))
        $('#toolbar a[href="kcact:maximize"]').click(function() {
            browser.maximize(this);
            return false;
        });
    else
        $('#toolbar a[href="kcact:maximize"]').css('display', 'none');

    $('#toolbar a[href="kcact:about"]').click(function() {
        var html = '<div class="box about">' +
            '<div class="head"><a href="http://kcfinder.sunhater.com" target="_blank">KCFinder</a> ' + browser.version + '</div>';
        if (browser.support.check4Update)
            html += '<div id="checkver"><span class="loading"><span>' + browser.label("Checking for new version...") + '</span></span></div>';
        html +=
            '<div>' + browser.label("Licenses:") + ' GPLv2 & LGPLv2</div>' +
            '<div>Copyright &copy;2010, 2011 Pavel Tzonkov</div>' +
            '<button>' + browser.label("OK") + '</button>' +
        '</div>';
        $('#dialog').html(html);
        $('#dialog').data('title', browser.label("About"));
        browser.showDialog();
        var close = function() {
            browser.hideDialog();
            browser.unshadow();
        }
        $('#dialog button').click(close);
        var span = $('#checkver > span');
        setTimeout(function() {
            $.ajax({
                dataType: 'json',
                url: browser.baseGetData('check4Update'),
                async: true,
                success: function(data) {
                    if (!$('#dialog').html().length)
                        return;
                    span.removeClass('loading');
                    if (!data.version) {
                        span.html(browser.label("Unable to connect!"));
                        browser.showDialog();
                        return;
                    }
                    if (browser.version < data.version)
                        span.html('<a href="http://kcfinder.sunhater.com/download" target="_blank">' + browser.label("Download version {version} now!", {version: data.version}) + '</a>');
                    else
                        span.html(browser.label("KCFinder is up to date!"));
                    browser.showDialog();
                },
                error: function() {
                    if (!$('#dialog').html().length)
                        return;
                    span.removeClass('loading');
                    span.html(browser.label("Unable to connect!"));
                    browser.showDialog();
                }
            });
        }, 1000);
        $('#dialog').unbind();

        return false;
    });

    this.initUploadButton();
};

browser.initUploadButton = function() {
    var btn = $('#toolbar a[href="kcact:upload"]');
    if (!this.access.files.upload) {
        btn.css('display', 'none');
        return;
    }
    var top = btn.get(0).offsetTop;
    var width = btn.outerWidth();
    var height = btn.outerHeight();
    $('#toolbar').prepend('<div id="upload" style="top:' + top + 'px;width:' + width + 'px;height:' + height + 'px">' +
        '<form enctype="multipart/form-data" method="post" target="uploadResponse" action="' + browser.baseGetData('upload') + '">' +
            '<input type="file" name="upload[]" onchange="browser.uploadFile(this.form)" style="height:' + height + 'px" multiple="multiple" />' +
            '<input type="hidden" name="dir" value="" />' +
        '</form>' +
    '</div>');
    $('#upload input').css('margin-left', "-" + ($('#upload input').outerWidth() - width) + 'px');
    $('#upload').mouseover(function() {
        $('#toolbar a[href="kcact:upload"]').addClass('hover');
    });
    $('#upload').mouseout(function() {
        $('#toolbar a[href="kcact:upload"]').removeClass('hover');
    });
};

browser.uploadFile = function(form) {
    if (!this.dirWritable) {
        browser.alert(this.label("Cannot write to upload folder."));
        $('#upload').detach();
        browser.initUploadButton();
        return;
    }
    form.elements[1].value = browser.dir;
    $('<iframe id="uploadResponse" name="uploadResponse" src="javascript:;"></iframe>').prependTo(document.body);
    $('#loading').html(this.label("Uploading file..."));
    $('#loading').css('display', 'inline');
    form.submit();
    $('#uploadResponse').load(function() {
        var response = $(this).contents().find('body').html();
        $('#loading').css('display', 'none');
        response = response.split("\n");
        var selected = [], errors = [];
        $.each(response, function(i, row) {
            if (row.substr(0, 1) == '/')
                selected[selected.length] = row.substr(1, row.length - 1)
            else
                errors[errors.length] = row;
        });
        if (errors.length)
            browser.alert(errors.join("\n"));
        if (!selected.length)
            selected = null
        browser.refresh(selected);
        $('#upload').detach();
        setTimeout(function() {
            $('#uploadResponse').detach();
        }, 1);
        browser.initUploadButton();
    });
};

browser.maximize = function(button) {
    if (window.opener) {
        window.moveTo(0, 0);
        width = screen.availWidth;
        height = screen.availHeight;
        if ($.browser.opera)
            height -= 50;
        window.resizeTo(width, height);

    } else if (browser.opener.TinyMCE) {
        var win, ifr, id;

        $('iframe', window.parent.document).each(function() {
            if (/^mce_\d+_ifr$/.test($(this).attr('id'))) {
                id = parseInt($(this).attr('id').replace(/^mce_(\d+)_ifr$/, "$1"));
                win = $('#mce_' + id, window.parent.document);
                ifr = $('#mce_' + id + '_ifr', window.parent.document);
            }
        });

        if ($(button).hasClass('selected')) {
            $(button).removeClass('selected');
            win.css({
                left: browser.maximizeMCE.left + 'px',
                top: browser.maximizeMCE.top + 'px',
                width: browser.maximizeMCE.width + 'px',
                height: browser.maximizeMCE.height + 'px'
            });
            ifr.css({
                width: browser.maximizeMCE.width - browser.maximizeMCE.Hspace + 'px',
                height: browser.maximizeMCE.height - browser.maximizeMCE.Vspace + 'px'
            });

        } else {
            $(button).addClass('selected')
            browser.maximizeMCE = {
                width: _.nopx(win.css('width')),
                height: _.nopx(win.css('height')),
                left: win.position().left,
                top: win.position().top,
                Hspace: _.nopx(win.css('width')) - _.nopx(ifr.css('width')),
                Vspace: _.nopx(win.css('height')) - _.nopx(ifr.css('height'))
            };
            var width = $(window.parent).width();
            var height = $(window.parent).height();
            win.css({
                left: $(window.parent).scrollLeft() + 'px',
                top: $(window.parent).scrollTop() + 'px',
                width: width + 'px',
                height: height + 'px'
            });
            ifr.css({
                width: width - browser.maximizeMCE.Hspace + 'px',
                height: height - browser.maximizeMCE.Vspace + 'px'
            });
        }

    } else if ($('iframe', window.parent.document).get(0)) {
        var ifrm = $('iframe[name="' + window.name + '"]', window.parent.document);
        var parent = ifrm.parent();
        var width, height;
        if ($(button).hasClass('selected')) {
            $(button).removeClass('selected');
            if (browser.maximizeThread) {
                clearInterval(browser.maximizeThread);
                browser.maximizeThread = null;
            }
            if (browser.maximizeW) browser.maximizeW = null;
            if (browser.maximizeH) browser.maximizeH = null;
            $.each($('*', window.parent.document).get(), function(i, e) {
                e.style.display = browser.maximizeDisplay[i];
            });
            ifrm.css({
                display: browser.maximizeCSS.display,
                position: browser.maximizeCSS.position,
                left: browser.maximizeCSS.left,
                top: browser.maximizeCSS.top,
                width: browser.maximizeCSS.width,
                height: browser.maximizeCSS.height
            });
            $(window.parent).scrollLeft(browser.maximizeLest);
            $(window.parent).scrollTop(browser.maximizeTop);

        } else {
            $(button).addClass('selected');
            browser.maximizeCSS = {
                display: ifrm.css('display'),
                position: ifrm.css('position'),
                left: ifrm.css('left'),
                top: ifrm.css('top'),
                width: ifrm.outerWidth() + 'px',
                height: ifrm.outerHeight() + 'px'
            };
            browser.maximizeTop = $(window.parent).scrollTop();
            browser.maximizeLeft = $(window.parent).scrollLeft();
            browser.maximizeDisplay = [];
            $.each($('*', window.parent.document).get(), function(i, e) {
                browser.maximizeDisplay[i] = $(e).css('display');
                $(e).css('display', 'none');
            });

            ifrm.css('display', 'block');
            ifrm.parents().css('display', 'block');
            var resize = function() {
                width = $(window.parent).width();
                height = $(window.parent).height();
                if (!browser.maximizeW || (browser.maximizeW != width) ||
                    !browser.maximizeH || (browser.maximizeH != height)
                ) {
                    browser.maximizeW = width;
                    browser.maximizeH = height;
                    ifrm.css({
                        width: width + 'px',
                        height: height + 'px'
                    });
                    browser.resize();
                }
            }
            ifrm.css('position', 'absolute');
            if ((ifrm.offset().left == ifrm.position().left) &&
                (ifrm.offset().top == ifrm.position().top)
            )
                ifrm.css({left: '0', top: '0'});
            else
                ifrm.css({
                    left: - ifrm.offset().left + 'px',
                    top: - ifrm.offset().top + 'px'
                });

            resize();
            browser.maximizeThread = setInterval(resize, 250);
        }
    }
};

browser.refresh = function(selected) {
    this.fadeFiles();
    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: browser.baseGetData('chDir'),
        data: {dir:browser.dir},
        async: false,
        success: function(data) {
            if (browser.check4errors(data))
                return;
            browser.dirWritable = data.dirWritable;
            browser.files = data.files ? data.files : [];
            browser.orderFiles(null, selected);
            browser.statusDir();
        },
        error: function() {
            $('#files > div').css({opacity:'', filter:''});
            $('#files').html(browser.label("Unknown error."));
        }
    });
};
