<?php

/** This file is part of KCFinder project
  *
  *      @desc Upload files using drag and drop
  *   @package KCFinder
  *   @version 2.51
  *    @author Forum user (updated by Pavel Tzonkov)
  * @copyright 2010, 2011 KCFinder Project
  *   @license http://www.opensource.org/licenses/gpl-2.0.php GPLv2
  *   @license http://www.opensource.org/licenses/lgpl-2.1.php LGPLv2
  *      @link http://kcfinder.sunhater.com
  */?>

browser.initDropUpload = function() {
    if ((typeof(XMLHttpRequest) == 'undefined') ||
        (typeof(document.addEventListener) == 'undefined') ||
        (typeof(File) == 'undefined') ||
        (typeof(FileReader) == 'undefined')
    )
        return;

    if (!XMLHttpRequest.prototype.sendAsBinary) {
        XMLHttpRequest.prototype.sendAsBinary = function(datastr) {
            var ords = Array.prototype.map.call(datastr, function(x) {
                return x.charCodeAt(0) & 0xff;
            });
            var ui8a = new Uint8Array(ords);
            this.send(ui8a.buffer);
        }
    }

    var uploadQueue = [],
        uploadInProgress = false,
        filesCount = 0,
        errors = [],
        files = $('#files'),
        folders = $('div.folder > a'),
        boundary = '------multipartdropuploadboundary' + (new Date).getTime(),
        currentFile,

    filesDragOver = function(e) {
        if (e.preventDefault) e.preventDefault();
        $('#files').addClass('drag');
        return false;
    },

    filesDragEnter = function(e) {
        if (e.preventDefault) e.preventDefault();
        return false;
    },

    filesDragLeave = function(e) {
        if (e.preventDefault) e.preventDefault();
        $('#files').removeClass('drag');
        return false;
    },

    filesDrop = function(e) {
        if (e.preventDefault) e.preventDefault();
        if (e.stopPropagation) e.stopPropagation();
        $('#files').removeClass('drag');
        if (!$('#folders span.current').first().parent().data('writable')) {
            browser.alert("Cannot write to upload folder.");
            return false;
        }
        filesCount += e.dataTransfer.files.length
        for (var i = 0; i < e.dataTransfer.files.length; i++) {
            var file = e.dataTransfer.files[i];
            file.thisTargetDir = browser.dir;
            uploadQueue.push(file);
        }
        processUploadQueue();
        return false;
    },

    folderDrag = function(e) {
        if (e.preventDefault) e.preventDefault();
        return false;
    },

    folderDrop = function(e, dir) {
        if (e.preventDefault) e.preventDefault();
        if (e.stopPropagation) e.stopPropagation();
        if (!$(dir).data('writable')) {
            browser.alert("Cannot write to upload folder.");
            return false;
        }
        filesCount += e.dataTransfer.files.length
        for (var i = 0; i < e.dataTransfer.files.length; i++) {
            var file = e.dataTransfer.files[i];
            file.thisTargetDir = $(dir).data('path');
            uploadQueue.push(file);
        }
        processUploadQueue();
        return false;
    };

    files.get(0).removeEventListener('dragover', filesDragOver, false);
    files.get(0).removeEventListener('dragenter', filesDragEnter, false);
    files.get(0).removeEventListener('dragleave', filesDragLeave, false);
    files.get(0).removeEventListener('drop', filesDrop, false);

    files.get(0).addEventListener('dragover', filesDragOver, false);
    files.get(0).addEventListener('dragenter', filesDragEnter, false);
    files.get(0).addEventListener('dragleave', filesDragLeave, false);
    files.get(0).addEventListener('drop', filesDrop, false);

    folders.each(function() {
        var folder = this,

        dragOver = function(e) {
            $(folder).children('span.folder').addClass('context');
            return folderDrag(e);
        },

        dragLeave = function(e) {
            $(folder).children('span.folder').removeClass('context');
            return folderDrag(e);
        },

        drop = function(e) {
            $(folder).children('span.folder').removeClass('context');
            return folderDrop(e, folder);
        };

        this.removeEventListener('dragover', dragOver, false);
        this.removeEventListener('dragenter', folderDrag, false);
        this.removeEventListener('dragleave', dragLeave, false);
        this.removeEventListener('drop', drop, false);

        this.addEventListener('dragover', dragOver, false);
        this.addEventListener('dragenter', folderDrag, false);
        this.addEventListener('dragleave', dragLeave, false);
        this.addEventListener('drop', drop, false);
    });

    function updateProgress(evt) {
        var progress = evt.lengthComputable
            ? Math.round((evt.loaded * 100) / evt.total) + '%'
            : Math.round(evt.loaded / 1024) + " KB";
        $('#loading').html(browser.label("Uploading file {number} of {count}... {progress}", {
            number: filesCount - uploadQueue.length,
            count: filesCount,
            progress: progress
        }));
    }

    function processUploadQueue() {
        if (uploadInProgress)
            return false;

        if (uploadQueue && uploadQueue.length) {
            var file = uploadQueue.shift();
            currentFile = file;
            $('#loading').html(browser.label("Uploading file {number} of {count}... {progress}", {
                number: filesCount - uploadQueue.length,
                count: filesCount,
                progress: ""
            }));
            $('#loading').css('display', 'inline');

            var reader = new FileReader();
            reader.thisFileName = file.name;
            reader.thisFileType = file.type;
            reader.thisFileSize = file.size;
            reader.thisTargetDir = file.thisTargetDir;

            reader.onload = function(evt) {
                uploadInProgress = true;

                var postbody = '--' + boundary + '\r\nContent-Disposition: form-data; name="upload[]"';
                if (evt.target.thisFileName)
                    postbody += '; filename="' + _.utf8encode(evt.target.thisFileName) + '"';
                postbody += '\r\n';
                if (evt.target.thisFileSize)
                    postbody += 'Content-Length: ' + evt.target.thisFileSize + '\r\n';
                postbody += 'Content-Type: ' + evt.target.thisFileType + '\r\n\r\n' + evt.target.result + '\r\n--' + boundary + '\r\nContent-Disposition: form-data; name="dir"\r\n\r\n' + _.utf8encode(evt.target.thisTargetDir) + '\r\n--' + boundary + '\r\n--' + boundary + '--\r\n';

                var xhr = new XMLHttpRequest();
                xhr.thisFileName = evt.target.thisFileName;

                if (xhr.upload) {
                    xhr.upload.thisFileName = evt.target.thisFileName;
                    xhr.upload.addEventListener("progress", updateProgress, false);
                }
                xhr.open('POST', browser.baseGetData('upload'), true);
                xhr.setRequestHeader('Content-Type', 'multipart/form-data; boundary=' + boundary);
                xhr.setRequestHeader('Content-Length', postbody.length);

                xhr.onload = function(e) {
                    $('#loading').css('display', 'none');
                    if (browser.dir == reader.thisTargetDir)
                        browser.fadeFiles();
                    uploadInProgress = false;
                    processUploadQueue();
                    if (xhr.responseText.substr(0, 1) != '/')
                        errors[errors.length] = xhr.responseText;
                }

                xhr.sendAsBinary(postbody);
            };

            reader.onerror = function(evt) {
                $('#loading').css('display', 'none');
                uploadInProgress = false;
                processUploadQueue();
                errors[errors.length] = browser.label("Failed to upload {filename}!", {
                    filename: evt.target.thisFileName
                });
            };

            reader.readAsBinaryString(file);

        } else {
            filesCount = 0;
            var loop = setInterval(function() {
                if (uploadInProgress) return;
                clearInterval(loop);
                if (currentFile.thisTargetDir == browser.dir)
                    browser.refresh();
                boundary = '------multipartdropuploadboundary' + (new Date).getTime();
                if (errors.length) {
                    browser.alert(errors.join('\n'));
                    errors = [];
                }
            }, 333);
        }
    }
};
