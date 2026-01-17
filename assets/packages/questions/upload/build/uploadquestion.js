(function (factory) {
    typeof define === 'function' && define.amd ? define(factory) :
    factory();
})((function () { 'use strict';

    /**
     * AJAX Upload ( http://valums.com/ajax-upload/ )
     * Copyright (c) Andrew Valums
     * Licensed under the MIT license
     */
    (function () {
      /**
       * Attaches event to a dom element.
       * @param {Element} el
       * @param type event name
       * @param fn callback This refers to the passed element
       */
      function addEvent(el, type, fn) {
        if (el.addEventListener) {
          el.addEventListener(type, fn, false);
        } else if (el.attachEvent) {
          el.attachEvent('on' + type, function () {
            fn.call(el);
          });
        } else {
          throw new Error('not supported or DOM not loaded');
        }
      }
      // getOffset function copied from jQuery lib (http://jquery.com/)


      if (document.documentElement.getBoundingClientRect) {
        // Get Offset using getBoundingClientRect
        // http://ejohn.org/blog/getboundingclientrect-is-awesome/
        var getOffset = function (el) {
          var box = el.getBoundingClientRect();
          var doc = el.ownerDocument;
          var body = doc.body;
          var docElem = doc.documentElement; // for ie

          var clientTop = docElem.clientTop || body.clientTop || 0;
          var clientLeft = docElem.clientLeft || body.clientLeft || 0; // In Internet Explorer 7 getBoundingClientRect property is treated as physical,
          // while others are logical. Make all logical, like in IE8.

          var zoom = 1;

          if (body.getBoundingClientRect) {
            var bound = body.getBoundingClientRect();
            zoom = (bound.right - bound.left) / body.clientWidth;
          }

          if (zoom > 1) {
            clientTop = 0;
            clientLeft = 0;
          }

          var top = box.top / zoom + (window.pageYOffset || docElem && docElem.scrollTop / zoom || body.scrollTop / zoom) - clientTop,
              left = box.left / zoom + (window.pageXOffset || docElem && docElem.scrollLeft / zoom || body.scrollLeft / zoom) - clientLeft;
          return {
            top: top,
            left: left
          };
        };
      } else {
        // Get offset adding all offsets
        var getOffset = function (el) {
          var top = 0,
              left = 0;

          do {
            top += el.offsetTop || 0;
            left += el.offsetLeft || 0;
            el = el.offsetParent;
          } while (el);

          return {
            left: left,
            top: top
          };
        };
      }
      /**
       * Returns left, top, right and bottom properties describing the border-box,
       * in pixels, with the top-left relative to the body
       * @param {Element} el
       * @return {Object} Contains left, top, right,bottom
       */


      function getBox(el) {
        var left, right, top, bottom;
        var offset = getOffset(el);
        left = offset.left;
        top = offset.top;
        right = left + el.offsetWidth;
        bottom = top + el.offsetHeight;
        return {
          left: left,
          right: right,
          top: top,
          bottom: bottom
        };
      }
      /**
       * Helper that takes object literal
       * and add all properties to element.style
       * @param {Element} el
       * @param {Object} styles
       */


      function addStyles(el, styles) {
        for (var name in styles) {
          if (styles.hasOwnProperty(name)) {
            el.style[name] = styles[name];
          }
        }
      }
      /**
       * Function places an absolutely positioned
       * element on top of the specified element
       * copying position and dimentions.
       * @param {Element} from
       * @param {Element} to
       */


      function copyLayout(from, to) {
        var box = getBox(from);
        addStyles(to, {
          position: 'absolute',
          left: box.left + 'px',
          top: box.top + 'px',
          width: from.offsetWidth + 'px',
          height: from.offsetHeight + 'px'
        });
      }
      /**
      * Creates and returns element from html chunk
      * Uses innerHTML to create an element
      */


      var toElement = function () {
        var div = document.createElement('div');
        return function (html) {
          div.innerHTML = html;
          var el = div.firstChild;
          return div.removeChild(el);
        };
      }();
      /**
       * Function generates unique id
       * @return unique id
       */


      var getUID = function () {
        var id = 0;
        return function () {
          return 'ValumsAjaxUpload' + id++;
        };
      }();
      /**
       * Get file name from path
       * @param {String} file path to file
       * @return filename
       */


      function fileFromPath(file) {
        return file.replace(/.*(\/|\\)/, "");
      }
      /**
       * Get file extension lowercase
       * @param {String} file name
       * @return file extenstion
       */


      function getExt(file) {
        file = file.toLowerCase();
        return -1 !== file.indexOf('.') ? file.replace(/.*[.]/, '') : '';
      }

      function hasClass(el, name) {
        var re = new RegExp('\\b' + name + '\\b');
        return re.test(el.className);
      }

      function addClass(el, name) {
        if (!hasClass(el, name)) {
          el.className += ' ' + name;
        }
      }

      function removeClass(el, name) {
        var re = new RegExp('\\b' + name + '\\b');
        el.className = el.className.replace(re, '');
      }

      function removeNode(el) {
        el.parentNode.removeChild(el);
      }
      /**
       * Easy styling and uploading
       * @constructor
       * @param button An element you want convert to
       * upload button. Tested dimentions up to 500x500px
       * @param {Object} options See defaults below.
       */


      window.AjaxUpload = function (button, options) {
        this._settings = {
          // Location of the server-side upload script
          action: 'upload.php',
          // File upload name
          name: 'userfile',
          // Select & upload multiple files at once FF3.6+, Chrome 4+
          multiple: false,
          // Additional data to send
          data: {},
          // Submit file as soon as it's selected
          autoSubmit: true,
          // The type of data that you're expecting back from the server.
          // html and xml are detected automatically.
          // Only useful when you are using json data as a response.
          // Set to "json" in that case.
          responseType: false,
          // Class applied to button when mouse is hovered
          hoverClass: 'hover',
          // Class applied to button when button is focused
          focusClass: 'focus',
          // Class applied to button when AU is disabled
          disabledClass: 'disabled',
          // When user selects a file, useful with autoSubmit disabled
          // You can return false to cancel upload
          onChange: function (file, extension) {},
          // Callback to fire before file is uploaded
          // You can return false to cancel upload
          onSubmit: function (file, extension) {},
          // Fired when file upload is completed
          // WARNING! DO NOT USE "FALSE" STRING AS A RESPONSE!
          onComplete: function (file, response) {}
        }; // Merge the users options with our defaults

        for (var i in options) {
          if (options.hasOwnProperty(i)) {
            this._settings[i] = options[i];
          }
        } // button isn't necessary a dom element


        if (button.jquery) {
          // jQuery object was passed
          button = button[0];
        } else if (typeof button == "string") {
          if (/^#.*/.test(button)) {
            // If jQuery user passes #elementId don't break it
            button = button.slice(1);
          }

          button = document.getElementById(button);
        }

        if (!button || button.nodeType !== 1) {
          throw new Error("Please make sure that you're passing a valid element");
        }

        if (button.nodeName.toUpperCase() == 'A') {
          // disable link
          addEvent(button, 'click', function (e) {
            if (e && e.preventDefault) {
              e.preventDefault();
            } else if (window.event) {
              window.event.returnValue = false;
            }
          });
        } // DOM element


        this._button = button; // DOM element

        this._input = null; // If disabled clicking on button won't do anything

        this._disabled = false; // if the button was disabled before refresh if will remain
        // disabled in FireFox, let's fix it

        this.enable();

        this._rerouteClicks();
      }; // assigning methods to our class


      AjaxUpload.prototype = {
        setData: function (data) {
          this._settings.data = data;
        },
        disable: function () {
          addClass(this._button, this._settings.disabledClass);
          this._disabled = true;

          var nodeName = this._button.nodeName.toUpperCase();

          if (nodeName == 'INPUT' || nodeName == 'BUTTON') {
            this._button.setAttribute('disabled', 'disabled');
          } // hide input


          if (this._input) {
            if (this._input.parentNode) {
              // We use visibility instead of display to fix problem with Safari 4
              // The problem is that the value of input doesn't change if it
              // has display none when user selects a file
              this._input.parentNode.style.visibility = 'hidden';
            }
          }
        },
        enable: function () {
          removeClass(this._button, this._settings.disabledClass);

          this._button.removeAttribute('disabled');

          this._disabled = false;
        },

        /**
         * Creates invisible file input
         * that will hover above the button
         * <div><input type='file' /></div>
         */
        _createInput: function () {
          var self = this;
          var input = document.createElement("input");
          input.setAttribute('type', 'file');
          input.setAttribute('name', this._settings.name);
          if (this._settings.multiple) input.setAttribute('multiple', 'multiple');
          addStyles(input, {
            'position': 'absolute',
            // in Opera only 'browse' button
            // is clickable and it is located at
            // the right side of the input
            'right': 0,
            'margin': 0,
            'padding': 0,
            'fontSize': '480px',
            // in Firefox if font-family is set to
            // 'inherit' the input doesn't work
            'fontFamily': 'sans-serif',
            'cursor': 'pointer'
          });
          var div = document.createElement("div");
          addStyles(div, {
            'display': 'block',
            'position': 'absolute',
            'overflow': 'hidden',
            'margin': 0,
            'padding': 0,
            'opacity': 0,
            // Make sure browse button is in the right side
            // in Internet Explorer
            'direction': 'ltr',
            //Max zIndex supported by Opera 9.0-9.2
            'zIndex': 2147483583
          }); // Make sure that element opacity exists.
          // Otherwise use IE filter

          if (div.style.opacity !== "0") {
            if (typeof div.filters == 'undefined') {
              throw new Error('Opacity not supported by the browser');
            }

            div.style.filter = "alpha(opacity=0)";
          }

          addEvent(input, 'change', function () {
            if (!input || input.value === '') {
              return;
            } // Get filename from input, required
            // as some browsers have path instead of it


            var file = fileFromPath(input.value);

            if (false === self._settings.onChange.call(self, file, getExt(file))) {
              self._clearInput();

              return;
            } // Submit form when value is changed


            if (self._settings.autoSubmit) {
              self.submit();
            }
          });
          addEvent(input, 'mouseover', function () {
            addClass(self._button, self._settings.hoverClass);
          });
          addEvent(input, 'mouseout', function () {
            removeClass(self._button, self._settings.hoverClass);
            removeClass(self._button, self._settings.focusClass);

            if (input.parentNode) {
              // We use visibility instead of display to fix problem with Safari 4
              // The problem is that the value of input doesn't change if it
              // has display none when user selects a file
              input.parentNode.style.visibility = 'hidden';
            }
          });
          addEvent(input, 'focus', function () {
            addClass(self._button, self._settings.focusClass);
          });
          addEvent(input, 'blur', function () {
            removeClass(self._button, self._settings.focusClass);
          });
          div.appendChild(input);
          document.body.appendChild(div);
          this._input = input;
        },
        _clearInput: function () {
          if (!this._input) {
            return;
          } // this._input.value = ''; Doesn't work in IE6


          removeNode(this._input.parentNode);
          this._input = null;

          this._createInput();

          removeClass(this._button, this._settings.hoverClass);
          removeClass(this._button, this._settings.focusClass);
        },

        /**
         * Function makes sure that when user clicks upload button,
         * the this._input is clicked instead
         */
        _rerouteClicks: function () {
          var self = this; // IE will later display 'access denied' error
          // if you use using self._input.click()
          // other browsers just ignore click()

          addEvent(self._button, 'mouseover', function () {
            if (self._disabled) {
              return;
            }

            if (!self._input) {
              self._createInput();
            }

            var div = self._input.parentNode;
            copyLayout(self._button, div);
            div.style.visibility = 'visible';
          }); // commented because we now hide input on mouseleave

          /**
           * When the window is resized the elements
           * can be misaligned if button position depends
           * on window size
           */
          //addResizeEvent(function(){
          //    if (self._input){
          //        copyLayout(self._button, self._input.parentNode);
          //    }
          //});
        },

        /**
         * Creates iframe with unique name
         * @return {Element} iframe
         */
        _createIframe: function () {
          // We can't use getTime, because it sometimes return
          // same value in safari :(
          var id = getUID(); // We can't use following code as the name attribute
          // won't be properly registered in IE6, and new window
          // on form submit will open
          // var iframe = document.createElement('iframe');
          // iframe.setAttribute('name', id);

          var iframe = toElement('<iframe src="javascript:false;" name="' + id + '" />'); // src="javascript:false; was added
          // because it possibly removes ie6 prompt
          // "This page contains both secure and nonsecure items"
          // Anyway, it doesn't do any harm.

          iframe.setAttribute('id', id);
          iframe.style.display = 'none';
          document.body.appendChild(iframe);
          return iframe;
        },

        /**
         * Creates form, that will be submitted to iframe
         * @param {Element} iframe Where to submit
         * @return {Element} form
         */
        _createForm: function (iframe) {
          var settings = this._settings; // We can't use the following code in IE6
          // var form = document.createElement('form');
          // form.setAttribute('method', 'post');
          // form.setAttribute('enctype', 'multipart/form-data');
          // Because in this case file won't be attached to request

          var form = toElement('<form method="post" enctype="multipart/form-data"></form>');
          form.setAttribute('action', settings.action);
          form.setAttribute('target', iframe.name);
          form.style.display = 'none';
          document.body.appendChild(form); // Create hidden input element for each data key

          for (var prop in settings.data) {
            if (settings.data.hasOwnProperty(prop)) {
              var el = document.createElement("input");
              el.setAttribute('type', 'hidden');
              el.setAttribute('name', prop);
              el.setAttribute('value', settings.data[prop]);
              form.appendChild(el);
            }
          }

          return form;
        },

        /**
         * Gets response from iframe and fires onComplete event when ready
         * @param iframe
         * @param file Filename to use in onComplete callback
         */
        _getResponse: function (iframe, file) {
          // getting response
          var toDeleteFlag = false,
              self = this,
              settings = this._settings;
          addEvent(iframe, 'load', function () {
            if ( // For Safari
            iframe.src == "javascript:'%3CHtml%3E%3C/html%3E';" || // For FF, IE
            iframe.src == "javascript:'<html></html>';") {
              // First time around, do not delete.
              // We reload to blank page, so that reloading main page
              // does not re-submit the post.
              if (toDeleteFlag) {
                // Fix busy state in FF3
                setTimeout(function () {
                  removeNode(iframe);
                }, 0);
              }

              return;
            }

            var doc = iframe.contentDocument ? iframe.contentDocument : window.frames[iframe.id].document; // fixing Opera 9.26,10.00

            if (doc.readyState && doc.readyState != 'complete') {
              // Opera fires load event multiple times
              // Even when the DOM is not ready yet
              // this fix should not affect other browsers
              return;
            } // fixing Opera 9.64


            if (doc.body && doc.body.innerHTML == "false") {
              // In Opera 9.64 event was fired second time
              // when body.innerHTML changed from false
              // to server response approx. after 1 sec
              return;
            }

            var response;

            if (doc.XMLDocument) {
              // response is a xml document Internet Explorer property
              response = doc.XMLDocument;
            } else if (doc.body) {
              // response is html document or plain text
              response = doc.body.innerHTML;

              if (settings.responseType && settings.responseType.toLowerCase() == 'json') {
                // If the document was sent as 'application/javascript' or
                // 'text/javascript', then the browser wraps the text in a <pre>
                // tag and performs html encoding on the contents.  In this case,
                // we need to pull the original text content from the text node's
                // nodeValue property to retrieve the unmangled content.
                // Note that IE6 only understands text/html
                if (doc.body.firstChild && doc.body.firstChild.nodeName.toUpperCase() == 'PRE') {
                  doc.normalize();
                  response = doc.body.firstChild.firstChild.nodeValue;
                }

                if (response) {
                  response = eval("(" + response + ")");
                } else {
                  response = {};
                }
              }
            } else {
              // response is a xml document
              response = doc;
            }

            settings.onComplete.call(self, file, response); // Reload blank page, so that reloading main page
            // does not re-submit the post. Also, remember to
            // delete the frame

            toDeleteFlag = true; // Fix IE mixed content issue

            iframe.src = "javascript:'<html></html>';";
            removeNode(iframe);
          });
        },

        /**
         * Upload file contained in this._input
         */
        submit: function () {
          var self = this,
              settings = this._settings;

          if (!this._input || this._input.value === '') {
            return;
          }

          var file = fileFromPath(this._input.value); // user returned false to cancel upload

          if (false === settings.onSubmit.call(this, file, getExt(file))) {
            this._clearInput();

            return;
          } // sending request


          var iframe = this._createIframe(); // Get response from iframe and fire onComplete event when ready


          this._getResponse(iframe, file);

          var form = this._createForm(iframe); // assuming following structure
          // div -> input type='file'


          removeNode(this._input.parentNode);
          removeClass(self._button, self._settings.hoverClass);
          removeClass(self._button, self._settings.focusClass);
          form.appendChild(this._input);
          form.submit(); // request set, clean up

          removeNode(form);
          form = null;
          removeNode(this._input);
          this._input = null; // get ready for next request

          this._createInput();
        }
      };
    })();

    window.uploadModalObjects = window.uploadModalObjects || {};

    class UploadQuestionController {
      constructor(fieldname) {
        this.fieldname = fieldname;
        this.$el = $('#upload_' + fieldname);
        this.$modalEl = $('#file-upload-modal-' + this.fieldname);
        this.show_title = this.$el.data('showtitle');
        this.show_comment = this.$el.data('showcomment');
      }

      prepareOpenUploadModalDialog() {
        const buttonsOpts = {};

        buttonsOpts[uploadLang.returnTxt] = () => {
          this.$el.dialog("close");
        };

        $(document).off('shown.bs.modal.lsuploadquestion', '#file-upload-modal-' + this.fieldname);
        $(document).on('shown.bs.modal.lsuploadquestion', '#file-upload-modal-' + this.fieldname, () => {
          const uploadFrame = $('#uploader' + this.fieldname);
          uploadFrame.load(uploadFrame.data('src'));
          this.updateMaxHeightModalbody(this.$el);
        });
        this.$modalEl.off('hide.bs.modal.lsuploadquestion');
        this.$modalEl.on('hide.bs.modal.lsuploadquestion', () => {
          const uploadFrame = $('#uploader' + this.fieldname);
          window.currentUploadHandler.saveAndExit(this.fieldname, this.show_title, this.show_comment, 1);
          uploadFrame.html('');
          return true;
        });
        this.$el.off('click.lsuploadquestion');
        this.$el.on('click.lsuploadquestion', e => {
          console.ls.log("File upload modal opening");
          this.$modalEl.modal('show');
        });
      }
      /* Function to update upload frame
       *
       * @param frameName name of the frame (here it's id too :) )
       * @param integer heigth
       */


      updateUploadFrame(frameName, heigth) {
        $("#" + frameName).innerHeight(heigth);
      }
      /* Function to update modal body max height
       *
       * @param modal jquery object : the modal
       */


      updateMaxHeightModalbody(modal) {
        const modalHeader = $(modal).find(".modal-header").outerHeight();
        const modalFooter = $(modal).find(".modal-footer").outerHeight();
        const finalMaxHeight = Math.max(150, $(window).height() - (modalHeader + modalFooter + 16)); // Not less than 150px

        console.ls.log([$(window).height(), modalHeader, modalFooter, modalHeader + modalFooter]);
        $(modal).find(".modal-body").css("max-height", finalMaxHeight);
      }

      getQueryVariable(variable, url) {
        const vars1 = url.split("/");

        for (let i = 0; i < vars1.length; i++) {
          if (vars1[i] == variable) {
            return vars1[i + 1];
          }
        } // If not found try with ?
        // TODO : replace by a regexp


        const vars2 = url.replace(/\&amp;/g, '&').split("&");

        for (let i = 0; i < vars.length; i++) {
          const pair = vars2[i].split("=");

          if (pair[0] == variable) {
            return pair[1];
          }
        }

        return null;
      }

      isValueInArray(arr, val) {
        inArray = false;

        for (let i = 0; i < arr.length; i++) {
          if (val.toLowerCase() == arr[i].toLowerCase()) {
            inArray = true;
          }
        }

        return inArray;
      }

      displayUploadedFiles(filecount, fieldname, show_title, show_comment) {
        const jsonstring = $("#java" + fieldname).val();

        if (jsonstring == '[]' || jsonstring == '') {
          $('#' + this.fieldname + '_uploadedfiles').addClass('d-none');
          $('#' + this.fieldname + '_uploadedfiles').find('table>tbody').html('');
          return;
        }

        if (jsonstring !== '') {
          let jsonobj = [];

          try {
            jsonobj = JSON.parse(jsonstring);
          } catch (e) {}
          $('#' + this.fieldname + '_uploadedfiles').removeClass('d-none');
          $('#' + this.fieldname + '_uploadedfiles').find('table>tbody').html('');
          const image_extensions = new Array('gif', 'jpeg', 'jpg', 'png', 'swf', 'psd', 'bmp', 'tiff', 'jp2', 'iff', 'bmp', 'xbm', 'ico');
          const templateHtml = $('#filerowtemplate_' + this.fieldname).html();
          jsonobj.forEach((item, iterator) => {
            let imageOrPlaceholder, imageOrPlaceholderHtml, title, comment, name, filepointer;

            if (this.isValueInArray(image_extensions, item.ext)) {
              imageOrPlaceholder = "image";
              imageOrPlaceholderHtml = '<img src="' + uploadurl + '/filegetcontents/' + decodeURIComponent(item.filename) + '" class="uploaded" />';
            } else {
              imageOrPlaceholder = "placeholder";
              imageOrPlaceholderHtml = '<div class="upload-placeholder"></div>';
            }

            title = show_title != 0 ? htmlentities(item.title) : '';
            comment = show_comment != 0 ? htmlentities(item.comment) : '';
            name = item.name;
            filepointer = iterator;
            const rowHtml = this.replaceWithObject(templateHtml, {
              imageOrPlaceholder,
              imageOrPlaceholderHtml,
              title,
              comment,
              name,
              filepointer
            });
            $('#' + this.fieldname + '_uploadedfiles').find('table>tbody').append(rowHtml);
          });
          $('.trigger_edit_upload_' + this.fieldname).off('click.lsuploadquestion');
          $('.trigger_edit_upload_' + this.fieldname).on('click.lsuploadquestion', () => {
            this.$modalEl.modal('show');
          });
        }
      }

      replaceWithObject(templateString, objectWithReplacements) {
        let outString = templateString;

        for (let key in objectWithReplacements) {
          outString = outString.replace(new RegExp(`\{${key}\}`), objectWithReplacements[key]);
        }

        return outString;
      }

      showBasic() {
        $('#basic').show();
      }

      hideBasic() {
        $('#basic').hide();
      }

    }

    window.UploadQuestionController = UploadQuestionController;

    var uploadHandler = function (qid, options) {
      var init = function () {
        doFileUpload();
      };

      var renderPreviewItem = function (fieldname, item, iterator) {
        var i = iterator;
        var image_extensions = new Array('gif', 'jpeg', 'jpg', 'png', 'swf', 'psd', 'bmp', 'tiff', 'jp2', 'iff', 'bmp', 'xbm', 'ico', 'heic');
        var previewblock = $('<li id="' + fieldname + '_li_' + i + '" class="previewblock file-element"></li>');
        var previewContainer = $('<div class="file-preview"></div>');

        if (isValueInArray(image_extensions, item.ext.toLowerCase())) {
          previewContainer.append('<img src="' + options.uploadurl + '/filegetcontents/' + item.filename + '" class="uploaded" />');
        } else {
          previewContainer.append('<div class="upload-placeholder"></div>');
        }

        previewContainer.append('<span class="file-name">' + escapeHtml(item.name) + '</span>');

        if ($('#' + fieldname + '_show_title').val() == 1 || $('#' + fieldname + '_show_comment').val() == 1) {
          var previewTitleContainer = $('');
          var previewCommentContainer = $('');

          if ($('#' + fieldname + '_show_title').val() == 1) {
            var previewTitleContainer = $('<div class="mb-3"></div>');
            $('<label class="control-label col-5"></label>').attr('for', fieldname + '_title_' + i).text(options.uploadLang.titleFld).appendTo(previewTitleContainer);
            $('<input class="form-control" type="text"/>').attr('id', fieldname + "_title_" + i).val(item.title).wrap('<div class="input-container"></div>').appendTo(previewTitleContainer);
          }

          if ($('#' + fieldname + '_show_comment').val() == 1) {
            var previewCommentContainer = $('<div class="mb-3"></div>');
            $('<label class="control-label col-5"></label>').attr('for', fieldname + '_comment_' + i).text(options.uploadLang.commentFld).appendTo(previewCommentContainer);
            $('<input class="form-control" type="text"/>').attr('id', fieldname + "_comment_" + i).val(item.comment).wrap('<div class="input-container"></div>').appendTo(previewCommentContainer);
          }
        }

        var previewDeleteBlock = $('<div class="mb-3"></div>').append($('<a class="btn btn-danger"></a>').html('<span class="fa fa-trash ri-delete-bin-fill"></span>&nbsp;' + options.uploadLang.deleteFile).on('click', function () {
          deletefile(fieldname, i);
        }).wrap('<div class="input-container text-center"></div>'));
        $('<fieldset></fieldset>').append(previewTitleContainer).append(previewCommentContainer).append(previewDeleteBlock).wrap('<div class="file-info"></div>').appendTo(previewContainer);
        $('<input type="hidden" />').attr('id', fieldname + '_size_' + i).attr('value', item.size).appendTo(previewblock);
        $('<input type="hidden" />').attr('id', fieldname + '_name_' + i).attr('value', item.name).appendTo(previewblock);
        $('<input type="hidden" />').attr('id', fieldname + '_file_index_' + i).attr('value', i).appendTo(previewblock);
        $('<input type="hidden" />').attr('id', fieldname + '_filename_' + i).attr('value', item.filename).appendTo(previewblock);
        $('<input type="hidden" />').attr('id', fieldname + '_ext_' + i).attr('value', item.ext).appendTo(previewblock); // add file to the list only if it doesn't exists already

        if ($("#" + fieldname + "_li_" + i).length === 0) {
          previewblock.append(previewContainer);
          $('#field' + fieldname + '_listfiles').append(previewblock);
        }
      };

      var doFileUpload = function () {
        var fieldname = options.sFieldName;
        /* Load the previously uploaded files */

        var filecount = $('#java' + fieldname + '_Cfilecount').val();
        $('#java' + fieldname + '_Cfilecount').val(filecount);

        if (filecount > 0) {
          var jsontext = $('#java' + fieldname).val();
          var json = '';

          try {
            json = JSON.parse(jsontext);
          } catch (e) {}

          if ($('#field' + fieldname + '_listfiles').length == 0) {
            $('<ul id="field' + fieldname + '_listfiles" class="files-list" />').insertAfter('#uploadstatus_' + qid);
          }

          $('#' + fieldname + '_licount').val(filecount);
          json.forEach(function (item, iterator) {
            renderPreviewItem(fieldname, item, iterator + 1);
          });
        } // The upload button


        var button = $('#button_' + qid);
        new AjaxUpload(button, {
          action: options.uploadurl + '/sid/' + surveyid + '/preview/' + options.questgrppreview + '/fieldname/' + fieldname + '/',
          name: 'uploadfile',
          data: {
            valid_extensions: $('#' + fieldname + '_allowed_filetypes').val(),
            max_filesize: $('#' + fieldname + '_maxfilesize').val(),
            preview: $('#preview').val(),
            surveyid: surveyid,
            fieldname: fieldname,
            YII_CSRF_TOKEN: options.csrfToken
          },
          onSubmit: function (file, ext) {
            var maxfiles = parseInt($('#' + fieldname + '_maxfiles').val());
            var filecount = parseInt($('#java' + fieldname + '_Cfilecount').val());
            var allowed_filetypes = $('#' + fieldname + '_allowed_filetypes').val().split(",");
            /* If maximum number of allowed filetypes have already been uploaded,
             * do not upload the file and display an error message ! */

            if (filecount >= maxfiles) {
              $('#notice').html('<p class="alert alert-danger"><span class="fa fa-exclamation-circle ri-error-warning-fill"></span>&nbsp;' + uploadLang.errorNoMoreFiles + '</p>');
              return false;
            }
            /* If the file being uploaded is not allowed,
             * do not upload the file and display an error message!
             */


            let allowSubmit = false;

            for (let fileType of allowed_filetypes) {
              allowSubmit = allowed_filetypes.includes(fileType);
            }

            if (allowSubmit == false) {
              $('#notice').html('<p class="alert alert-danger"><span class="fa fa-exclamation-circle ri-error-warning-fill"></span>&nbsp;' + uploadLang.errorOnlyAllowed.replace('%s', $('#' + fieldname + '_allowed_filetypes').val()) + '</p>');
              return false;
            } // change button text, when user selects file


            button.text(options.uploadLang.uploading); // If you want to allow uploading only 1 file at time,
            // you can disable upload button

            this.disable();
            button.append('<i id="loading-icon-fielupload" class="fa fa-spinner fa-pulse fa-3x fa-fw ri-loader-fill remix-pulse ri-3x" ></i>');
          },
          onComplete: function (file, response) {
            button.text(uploadLang.selectfile);
            $('#loading-icon-fielupload').remove(); // enable upload button

            this.enable(); // Once the file has been uploaded via AJAX,
            // the preview is appended to the list of files

            try {
              var metadata = JSON.parse(response);
            } catch (e) {
              /* Suppose we get an HTML error ? Replace whole HTML (without head) */
              $('body').html(response);
              return;
            }

            var count = parseInt($('#' + fieldname + '_licount').val());
            count++;
            $('#' + fieldname + '_licount').val(count);

            if (metadata.success) {
              $('#notice').html('<p class="alert alert-success"><span class="fa fa-check ri-check-fill"></span>&nbsp;' + metadata.msg + '</p>');

              if ($('#field' + fieldname + '_listfiles').length == 0) {
                $("<ul id='field" + fieldname + "_listfiles' class='files-list' />").insertAfter("#uploadstatus_" + options.qid);
              }

              renderPreviewItem(fieldname, metadata, count);
              var filecount = parseInt($('#java' + fieldname + '_Cfilecount').val());
              var minfiles = parseInt($('#' + fieldname + '_minfiles').val());
              filecount++;
              var maxfiles = parseInt($('#' + fieldname + '_maxfiles').val());
              $('#java' + fieldname + '_Cfilecount').val(filecount);
              if (filecount < minfiles) $('#uploadstatus').html(options.uploadLang.errorNeedMore.replace('%s', minfiles - filecount));else if (filecount < maxfiles) $('#uploadstatus').html(options.uploadLang.errorMoreAllowed.replace('%s', maxfiles - filecount));else $('#uploadstatus').html(options.uploadLang.errorMaxReached);
              if (filecount >= maxfiles) $('#notice').html('<p class="alert alert-success"><span class="fa fa-check ri-check-fill"></span>&nbsp;' + options.uploadLang.errorTooMuch + '</p>');
            } else {
              $('#notice').html('<p class="alert alert-danger"><span class="fa fa-exclamation-circle ri-error-warning-fill"></span>&nbsp;' + metadata.msg + '</p>');
            }
          }
        });
      };

      function isValueInArray(arr, val) {
        return arr.reduce(function (col, item) {
          return col || val.toLowerCase() == item.toLowerCase();
        }, false);
      } // pass the JSON data from the iframe to the main survey page


      function passJSON(fieldname, show_title, show_comment, pos) {
        var jsonArray = [];
        var filecount = 0;
        var licount = parseInt($('#' + fieldname + '_licount').val());
        var i = 1;

        while (i <= licount) {
          if ($("#" + fieldname + "_li_" + i).is(':visible')) {
            var fileObject = {
              title: $("#" + fieldname + "_show_title").val() == 1 ? $("#" + fieldname + "_title_" + i).val() : '',
              comment: $("#" + fieldname + "_show_comment").val() == 1 ? $("#" + fieldname + "_comment_" + i).val() : '',
              size: $("#" + fieldname + "_size_" + i).val(),
              name: $("#" + fieldname + "_name_" + i).val(),
              filename: $("#" + fieldname + "_filename_" + i).val(),
              ext: $("#" + fieldname + "_ext_" + i).val()
            };
            filecount += 1;
            jsonArray.push(fileObject);
          }

          i++;
        }

        $('#java' + fieldname).val(JSON.stringify(jsonArray)).trigger('updated');
        copyJSON(filecount, fieldname, show_title, show_comment, pos);
      }

      const copyJSON = function (filecount, fieldname, show_title, show_comment, pos) {
        $('#java' + fieldname + '_Cfilecount').val(filecount).trigger('updated');
        window['uploadQuestionController_' + fieldname].displayUploadedFiles(filecount, fieldname, show_title, show_comment, pos);
      };

      var saveAndExit = function (fieldname, show_title, show_comment, pos) {
        var filecount = parseInt($('#java' + fieldname + '_Cfilecount').val());
        var minfiles = parseInt($('#' + fieldname + '_minfiles').val());

        if (minfiles != 0 && filecount < minfiles && showpopups) {
          var confirmans = confirm(uploadLang.errorNeedMoreConfirm.replace('%s', minfiles - filecount));

          if (confirmans) {
            passJSON(fieldname, show_title, show_comment, pos);
            return true;
          } else return false;
        } else {
          passJSON(fieldname, show_title, show_comment, pos);
          return true;
        }
      };

      var deletefile = function (fieldname, count) {
        var file_index;
        var filename = $("#" + fieldname + "_filename_" + count).val();
        var name = $("#" + fieldname + "_name_" + count).val();
        var filecount = parseInt($('#java' + fieldname + '_Cfilecount').val());
        var licount = parseInt($('#' + fieldname + '_licount').val());
        $.ajax({
          method: "POST",
          url: uploadurl,
          data: {
            'delete': 1,
            'fieldname': fieldname,
            'filename': filename,
            'name': name,
            YII_CSRF_TOKEN: options.csrfToken
          }
        }).done(function (msg) {
          $('#notice').html('<p class="alert alert-success"><span class="fa fa-check ri-check-fill"></span>&nbsp;' + msg + '</p>');
          setTimeout(function () {
            $(".success").remove();
          }, 5000);
          $("#" + fieldname + "_li_" + count).hide();
          filecount--;
          $('#java' + fieldname + '_Cfilecount').val(filecount);
          file_index = $("#" + fieldname + "_file_index_" + count).val();

          for (j = count; j <= licount; j++) {
            if ($('#' + fieldname + '_li_' + j).is(":visible")) {
              $('#' + fieldname + '_file_index_' + j).val(file_index);
              file_index++;
            }
          }

          var minfiles = parseInt($('#' + fieldname + '_minfiles').val());
          var maxfiles = parseInt($('#' + fieldname + '_maxfiles').val());

          if (filecount < minfiles) {
            $('#uploadstatus').html(uploadLang.errorNeedMore.replace('%s', minfiles - filecount));
          } else {
            $('#uploadstatus').html(uploadLang.errorMoreAllowed.replace('%s', maxfiles - filecount));
          }
        });
      };

      return {
        init: init,
        saveAndExit: saveAndExit
      };
    };

    function escapeHtml(unsafe) {
      return unsafe.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    window.getUploadHandler = function (qid, options) {
      window.currentUploadHandler = new uploadHandler(qid, options);
      window.currentUploadHandler.init();
      return window.currentUploadHandler;
    };

}));
