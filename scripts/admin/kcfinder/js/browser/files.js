<?php

/** This file is part of KCFinder project
  *
  *      @desc File related functionality
  *   @package KCFinder
  *   @version 2.51
  *    @author Pavel Tzonkov <pavelc@users.sourceforge.net>
  * @copyright 2010, 2011 KCFinder Project
  *   @license http://www.opensource.org/licenses/gpl-2.0.php GPLv2
  *   @license http://www.opensource.org/licenses/lgpl-2.1.php LGPLv2
  *      @link http://kcfinder.sunhater.com
  */?>

browser.initFiles = function() {
    $(document).unbind('keydown');
    $(document).keydown(function(e) {
        return !browser.selectAll(e);
    });
    $('#files').unbind();
    $('#files').scroll(function() {
        browser.hideDialog();
    });
    $('.file').unbind();
    $('.file').click(function(e) {
        _.unselect();
        browser.selectFile($(this), e);
    });
    $('.file').rightClick(function(e) {
        _.unselect();
        browser.menuFile($(this), e);
    });
    $('.file').dblclick(function() {
        _.unselect();
        browser.returnFile($(this));
    });
    $('.file').mouseup(function() {
        _.unselect();
    });
    $('.file').mouseout(function() {
        _.unselect();
    });
    $.each(this.shows, function(i, val) {
        var display = (_.kuki.get('show' + val) == 'off')
            ? 'none' : 'block';
        $('#files .file div.' + val).css('display', display);
    });
    this.statusDir();
};

browser.showFiles = function(callBack, selected) {
    this.fadeFiles();
    setTimeout(function() {
        var html = '';
        $.each(browser.files, function(i, file) {
            var stamp = [];
            $.each(file, function(key, val) {
                stamp[stamp.length] = key + "|" + val;
            });
            stamp = _.md5(stamp.join('|'));
            if (_.kuki.get('view') == 'list') {
                if (!i) html += '<table summary="list">';
                var icon = _.getFileExtension(file.name);
                if (file.thumb)
                    icon = '.image';
                else if (!icon.length || !file.smallIcon)
                    icon = '.';
                icon = 'themes/' + browser.theme + '/img/files/small/' + icon + '.png';
                html += '<tr class="file">' +
                    '<td class="name" style="background-image:url(' + icon + ')">' + _.htmlData(file.name) + '</td>' +
                    '<td class="time">' + file.date + '</td>' +
                    '<td class="size">' + browser.humanSize(file.size) + '</td>' +
                '</tr>';
                if (i == browser.files.length - 1) html += '</table>';
            } else {
                if (file.thumb)
                    var icon = browser.baseGetData('thumb') + '&file=' + encodeURIComponent(file.name) + '&dir=' + encodeURIComponent(browser.dir) + '&stamp=' + stamp;
                else if (file.smallThumb) {
                    var icon = browser.uploadURL + '/' + browser.dir + '/' + file.name;
                    icon = _.escapeDirs(icon).replace(/\'/g, "%27");
                } else {
                    var icon = file.bigIcon ? _.getFileExtension(file.name) : '.';
                    if (!icon.length) icon = '.';
                    icon = 'themes/' + browser.theme + '/img/files/big/' + icon + '.png';
                }
                html += '<div class="file">' +
                    '<div class="thumb" style="background-image:url(\'' + icon + '\')" ></div>' +
                    '<div class="name">' + _.htmlData(file.name) + '</div>' +
                    '<div class="time">' + file.date + '</div>' +
                    '<div class="size">' + browser.humanSize(file.size) + '</div>' +
                '</div>';
            }
        });
        $('#files').html('<div>' + html + '<div>');
        $.each(browser.files, function(i, file) {
            var item = $('#files .file').get(i);
            $(item).data(file);
            if (_.inArray(file.name, selected) ||
                ((typeof selected != 'undefined') && !selected.push && (file.name == selected))
            )
                $(item).addClass('selected');
        });
        $('#files > div').css({opacity:'', filter:''});
        if (callBack) callBack();
        browser.initFiles();
    }, 200);
};

browser.selectFile = function(file, e) {
    if (e.ctrlKey || e.metaKey) {
        if (file.hasClass('selected'))
            file.removeClass('selected');
        else
            file.addClass('selected');
        var files = $('.file.selected').get();
        var size = 0;
        if (!files.length)
            this.statusDir();
        else {
            $.each(files, function(i, cfile) {
                size += parseInt($(cfile).data('size'));
            });
            size = this.humanSize(size);
            if (files.length > 1)
                $('#fileinfo').html(files.length + ' ' + this.label("selected files") + ' (' + size + ')');
            else {
                var data = $(files[0]).data();
                $('#fileinfo').html(data.name + ' (' + this.humanSize(data.size) + ', ' + data.date + ')');
            }
        }
    } else {
        var data = file.data();
        $('.file').removeClass('selected');
        file.addClass('selected');
        $('#fileinfo').html(data.name + ' (' + this.humanSize(data.size) + ', ' + data.date + ')');
    }
};

browser.selectAll = function(e) {
    if ((!e.ctrlKey && !e.metaKey) || ((e.keyCode != 65) && (e.keyCode != 97)))
        return false;
    var files = $('.file').get();
    if (files.length) {
        var size = 0;
        $.each(files, function(i, file) {
            if (!$(file).hasClass('selected'))
                $(file).addClass('selected');
            size += parseInt($(file).data('size'));
        });
        size = this.humanSize(size);
        $('#fileinfo').html(files.length + ' ' + this.label("selected files") + ' (' + size + ')');
    }
    return true;
};

browser.returnFile = function(file) {

    var fileURL = file.substr
        ? file : browser.uploadURL + '/' + browser.dir + '/' + file.data('name');
    fileURL = _.escapeDirs(fileURL);

    if (this.opener.CKEditor) {
        this.opener.CKEditor.object.tools.callFunction(this.opener.CKEditor.funcNum, fileURL, '');
        window.close();

    } else if (this.opener.FCKeditor) {
        window.opener.SetUrl(fileURL) ;
        window.close() ;

    } else if (this.opener.TinyMCE) {
        var win = tinyMCEPopup.getWindowArg('window');
        win.document.getElementById(tinyMCEPopup.getWindowArg('input')).value = fileURL;
        if (win.getImageData) win.getImageData();
        if (typeof(win.ImageDialog) != "undefined") {
            if (win.ImageDialog.getImageData)
                win.ImageDialog.getImageData();
            if (win.ImageDialog.showPreviewImage)
                win.ImageDialog.showPreviewImage(fileURL);
        }
        tinyMCEPopup.close();

    } else if (this.opener.callBack) {

        if (window.opener && window.opener.KCFinder) {
            this.opener.callBack(fileURL);
            window.close();
        }

        if (window.parent && window.parent.KCFinder) {
            var button = $('#toolbar a[href="kcact:maximize"]');
            if (button.hasClass('selected'))
                this.maximize(button);
            this.opener.callBack(fileURL);
        }

    } else if (this.opener.callBackMultiple) {
        if (window.opener && window.opener.KCFinder) {
            this.opener.callBackMultiple([fileURL]);
            window.close();
        }

        if (window.parent && window.parent.KCFinder) {
            var button = $('#toolbar a[href="kcact:maximize"]');
            if (button.hasClass('selected'))
                this.maximize(button);
            this.opener.callBackMultiple([fileURL]);
        }

    }
};

browser.returnFiles = function(files) {
    if (this.opener.callBackMultiple && files.length) {
        var rfiles = [];
        $.each(files, function(i, file) {
            rfiles[i] = browser.uploadURL + '/' + browser.dir + '/' + $(file).data('name');
            rfiles[i] = _.escapeDirs(rfiles[i]);
        });
        this.opener.callBackMultiple(rfiles);
        if (window.opener) window.close()
    }
};

browser.returnThumbnails = function(files) {
    if (this.opener.callBackMultiple) {
        var rfiles = [];
        var j = 0;
        $.each(files, function(i, file) {
            if ($(file).data('thumb')) {
                rfiles[j] = browser.thumbsURL + '/' + browser.dir + '/' + $(file).data('name');
                rfiles[j] = _.escapeDirs(rfiles[j++]);
            }
        });
        this.opener.callBackMultiple(rfiles);
        if (window.opener) window.close()
    }
};

browser.menuFile = function(file, e) {
    var data = file.data();
    var path = this.dir + '/' + data.name;
    var files = $('.file.selected').get();
    var html = '';

    if (file.hasClass('selected') && files.length && (files.length > 1)) {
        var thumb = false;
        var notWritable = 0;
        var cdata;
        $.each(files, function(i, cfile) {
            cdata = $(cfile).data();
            if (cdata.thumb) thumb = true;
            if (!data.writable) notWritable++;
        });
        if (this.opener.callBackMultiple) {
            html += '<a href="kcact:pick">' + this.label("Select") + '</a>';
            if (thumb) html +=
                '<a href="kcact:pick_thumb">' + this.label("Select Thumbnails") + '</a>';
        }
        if (data.thumb || data.smallThumb || this.support.zip) {
            html += (html.length ? '<div class="delimiter"></div>' : '');
            if (data.thumb || data.smallThumb)
                html +='<a href="kcact:view">' + this.label("View") + '</a>';
            if (this.support.zip) html += (html.length ? '<div class="delimiter"></div>' : '') +
                '<a href="kcact:download">' + this.label("Download") + '</a>';
        }

        if (this.access.files.copy || this.access.files.move)
            html += (html.length ? '<div class="delimiter"></div>' : '') +
                '<a href="kcact:clpbrdadd">' + this.label("Add to Clipboard") + '</a>';
        if (this.access.files['delete'])
            html += (html.length ? '<div class="delimiter"></div>' : '') +
                '<a href="kcact:rm"' + ((notWritable == files.length) ? ' class="denied"' : '') +
                '>' + this.label("Delete") + '</a>';

        if (html.length) {
            html = '<div class="menu">' + html + '</div>';
            $('#dialog').html(html);
            this.showMenu(e);
        } else
            return;

        $('.menu a[href="kcact:pick"]').click(function() {
            browser.returnFiles(files);
            browser.hideDialog();
            return false;
        });

        $('.menu a[href="kcact:pick_thumb"]').click(function() {
            browser.returnThumbnails(files);
            browser.hideDialog();
            return false;
        });

        $('.menu a[href="kcact:download"]').click(function() {
            browser.hideDialog();
            var pfiles = [];
            $.each(files, function(i, cfile) {
                pfiles[i] = $(cfile).data('name');
            });
            browser.post(browser.baseGetData('downloadSelected'), {dir:browser.dir, files:pfiles});
            return false;
        });

        $('.menu a[href="kcact:clpbrdadd"]').click(function() {
            browser.hideDialog();
            var msg = '';
            $.each(files, function(i, cfile) {
                var cdata = $(cfile).data();
                var failed = false;
                for (i = 0; i < browser.clipboard.length; i++)
                    if ((browser.clipboard[i].name == cdata.name) &&
                        (browser.clipboard[i].dir == browser.dir)
                    ) {
                        failed = true
                        msg += cdata.name + ": " + browser.label("This file is already added to the Clipboard.") + "\n";
                        break;
                    }

                if (!failed) {
                    cdata.dir = browser.dir;
                    browser.clipboard[browser.clipboard.length] = cdata;
                }
            });
            browser.initClipboard();
            if (msg.length) browser.alert(msg.substr(0, msg.length - 1));
            return false;
        });

        $('.menu a[href="kcact:rm"]').click(function() {
            if ($(this).hasClass('denied')) return false;
            browser.hideDialog();
            var failed = 0;
            var dfiles = [];
            $.each(files, function(i, cfile) {
                var cdata = $(cfile).data();
                if (!cdata.writable)
                    failed++;
                else
                    dfiles[dfiles.length] = browser.dir + "/" + cdata.name;
            });
            if (failed == files.length) {
                browser.alert(browser.label("The selected files are not removable."));
                return false;
            }

            var go = function(callBack) {
                browser.fadeFiles();
                $.ajax({
                    type: 'POST',
                    dataType: 'json',
                    url: browser.baseGetData('rm_cbd'),
                    data: {files:dfiles},
                    async: false,
                    success: function(data) {
                        if (callBack) callBack();
                        browser.check4errors(data);
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
                    browser.label("{count} selected files are not removable. Do you want to delete the rest?", {count:failed}),
                    go
                )

            else
                browser.confirm(
                    browser.label("Are you sure you want to delete all selected files?"),
                    go
                );

            return false;
        });

    } else {
        html += '<div class="menu">';
        $('.file').removeClass('selected');
        file.addClass('selected');
        $('#fileinfo').html(data.name + ' (' + this.humanSize(data.size) + ', ' + data.date + ')');
        if (this.opener.callBack || this.opener.callBackMultiple) {
            html += '<a href="kcact:pick">' + this.label("Select") + '</a>';
            if (data.thumb) html +=
                '<a href="kcact:pick_thumb">' + this.label("Select Thumbnail") + '</a>';
            html += '<div class="delimiter"></div>';
        }

        if (data.thumb || data.smallThumb)
            html +='<a href="kcact:view">' + this.label("View") + '</a>';

        html +=
            '<a href="kcact:download">' + this.label("Download") + '</a>';

        if (this.access.files.copy || this.access.files.move)
            html += '<div class="delimiter"></div>' +
                '<a href="kcact:clpbrdadd">' + this.label("Add to Clipboard") + '</a>';
        if (this.access.files.rename || this.access.files['delete'])
            html += '<div class="delimiter"></div>';
        if (this.access.files.rename)
            html += '<a href="kcact:mv"' + (!data.writable ? ' class="denied"' : '') + '>' +
                this.label("Rename...") + '</a>';
        if (this.access.files['delete'])
            html += '<a href="kcact:rm"' + (!data.writable ? ' class="denied"' : '') + '>' +
                this.label("Delete") + '</a>';
        html += '</div>';

        $('#dialog').html(html);
        this.showMenu(e);

        $('.menu a[href="kcact:pick"]').click(function() {
            browser.returnFile(file);
            browser.hideDialog();
            return false;
        });

        $('.menu a[href="kcact:pick_thumb"]').click(function() {
            var path = browser.thumbsURL + '/' + browser.dir + '/' + data.name;
            browser.returnFile(path);
            browser.hideDialog();
            return false;
        });

        $('.menu a[href="kcact:download"]').click(function() {
            var html = '<form id="downloadForm" method="post" action="' + browser.baseGetData('download') + '">' +
                '<input type="hidden" name="dir" />' +
                '<input type="hidden" name="file" />' +
            '</form>';
            $('#dialog').html(html);
            $('#downloadForm input').get(0).value = browser.dir;
            $('#downloadForm input').get(1).value = data.name;
            $('#downloadForm').submit();
            return false;
        });

        $('.menu a[href="kcact:clpbrdadd"]').click(function() {
            for (i = 0; i < browser.clipboard.length; i++)
                if ((browser.clipboard[i].name == data.name) &&
                    (browser.clipboard[i].dir == browser.dir)
                ) {
                    browser.hideDialog();
                    browser.alert(browser.label("This file is already added to the Clipboard."));
                    return false;
                }
            var cdata = data;
            cdata.dir = browser.dir;
            browser.clipboard[browser.clipboard.length] = cdata;
            browser.initClipboard();
            browser.hideDialog();
            return false;
        });

        $('.menu a[href="kcact:mv"]').click(function(e) {
            if (!data.writable) return false;
            browser.fileNameDialog(
                e, {dir: browser.dir, file: data.name},
                'newName', data.name, browser.baseGetData('rename'), {
                    title: "New file name:",
                    errEmpty: "Please enter new file name.",
                    errSlash: "Unallowable characters in file name.",
                    errDot: "File name shouldn't begins with '.'"
                },
                function() {
                    browser.refresh();
                }
            );
            return false;
        });

        $('.menu a[href="kcact:rm"]').click(function() {
            if (!data.writable) return false;
            browser.hideDialog();
            browser.confirm(browser.label("Are you sure you want to delete this file?"),
                function(callBack) {
                    $.ajax({
                        type: 'POST',
                        dataType: 'json',
                        url: browser.baseGetData('delete'),
                        data: {dir:browser.dir, file:data.name},
                        async: false,
                        success: function(data) {
                            if (callBack) callBack();
                            browser.clearClipboard();
                            if (browser.check4errors(data))
                                return;
                            browser.refresh();
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
    }

    $('.menu a[href="kcact:view"]').click(function() {
        browser.hideDialog();
        var ts = new Date().getTime();
        var showImage = function(data) {
            url = _.escapeDirs(browser.uploadURL + '/' + browser.dir + '/' + data.name) + '?ts=' + ts,
            $('#loading').html(browser.label("Loading image..."));
            $('#loading').css('display', 'inline');
            var img = new Image();
            img.src = url;
            img.onerror = function() {
                browser.lock = false;
                $('#loading').css('display', 'none');
                browser.alert(browser.label("Unknown error."));
                $(document).unbind('keydown');
                $(document).keydown(function(e) {
                    return !browser.selectAll(e);
                });
                browser.refresh();
            };
            var onImgLoad = function() {
                browser.lock = false;
                $('#files .file').each(function() {
                    if ($(this).data('name') == data.name)
                        browser.ssImage = this;
                });
                $('#loading').css('display', 'none');
                $('#dialog').html('<div class="slideshow"><img /></div>');
                $('#dialog img').attr({
                    src: url,
                    title: data.name
                }).fadeIn('fast', function() {
                    var o_w = $('#dialog').outerWidth();
                    var o_h = $('#dialog').outerHeight();
                    var f_w = $(window).width() - 30;
                    var f_h = $(window).height() - 30;
                    if ((o_w > f_w) || (o_h > f_h)) {
                        if ((f_w / f_h) > (o_w / o_h))
                            f_w = parseInt((o_w * f_h) / o_h);
                        else if ((f_w / f_h) < (o_w / o_h))
                            f_h = parseInt((o_h * f_w) / o_w);
                        $('#dialog img').attr({
                            width: f_w,
                            height: f_h
                        });
                    }
                    $('#dialog').unbind('click');
                    $('#dialog').click(function(e) {
                        browser.hideDialog();
                        $(document).unbind('keydown');
                        $(document).keydown(function(e) {
                            return !browser.selectAll(e);
                        });
                        if (browser.ssImage) {
                            browser.selectFile($(browser.ssImage), e);
                        }
                    });
                    browser.showDialog();
                    var images = [];
                    $.each(browser.files, function(i, file) {
                        if (file.thumb || file.smallThumb)
                            images[images.length] = file;
                    });
                    if (images.length)
                        $.each(images, function(i, image) {
                            if (image.name == data.name) {
                                $(document).unbind('keydown');
                                $(document).keydown(function(e) {
                                    if (images.length > 1) {
                                        if (!browser.lock && (e.keyCode == 37)) {
                                            var nimg = i
                                                ? images[i - 1]
                                                : images[images.length - 1];
                                            browser.lock = true;
                                            showImage(nimg);
                                        }
                                        if (!browser.lock && (e.keyCode == 39)) {
                                            var nimg = (i >= images.length - 1)
                                                ? images[0]
                                                : images[i + 1];
                                            browser.lock = true;
                                            showImage(nimg);
                                        }
                                    }
                                    if (e.keyCode == 27) {
                                        browser.hideDialog();
                                        $(document).unbind('keydown');
                                        $(document).keydown(function(e) {
                                            return !browser.selectAll(e);
                                        });
                                    }
                                });
                            }
                        });
                });
            };
            if (img.complete)
                onImgLoad();
            else
                img.onload = onImgLoad;
        };
        showImage(data);
        return false;
    });
};
