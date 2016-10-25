/*
 * LimeSurvey
 * Copyright (C) 2007-2016 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 *
 * Description: Javascript file for templates. Put JS-functions for your template here.
 */


/*
 * The function focusFirst puts the Focus on the first non-hidden element in the Survey.
 *
 * Normally this is the first input field (the first answer).
 */

function focusFirst(Event) {
  $('#limesurvey :input:visible:enabled:first').focus();
}

$(document).ready(function() {
/* Uncomment below if you want to use the focusFirst function */
    // focusFirst();

});


window.alert = function(message, title) {
  if($("#bootstrap-alert-box-modal").length == 0) {
      $("body").append('<div id="bootstrap-alert-box-modal" class="modal fade">\
          <div class="modal-dialog">\
              <div class="modal-content">\
                  <div class="modal-header" style="min-height:40px;">\
                      <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>\
                      <h4 class="modal-title"></h4>\
                  </div>\
                  <div class="modal-body"><p></p></div>\
                  <div class="modal-footer">\
                      <a href="#" data-dismiss="modal" class="btn btn-default">Close</a>\
                  </div>\
              </div>\
          </div>\
      </div>');
  }
  $("#bootstrap-alert-box-modal .modal-header h4").text(title || "");
  $("#bootstrap-alert-box-modal .modal-body p").text(message || "");

  $(document).ready(function()
  {
      $("#bootstrap-alert-box-modal").modal('show');
  });
};
