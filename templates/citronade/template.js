/*
 * LimeSurvey
 * Copyright (C) 2007 The LimeSurvey Project Team / Carsten Schmitz
 * Copyright (C) 2010 GsiLL / Denis Chenu
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
 *  
 * 
 * $Id:$
 */

function navbuttonsJqueryUi(){
    // Just deactivate default jquery-ui button
}
// This function deactivate comment on multi with comment
function autoDisabledComment(){
  $(".answers-wrapper li input.checkbox").each(function(){
    if($(this).attr('checked')){
      $(this).closest('li').find('input:text').attr('readonly','');
    }else{
      $(this).closest('li').find('input:text').val('');
      $(this).closest('li').find('input:text').attr('readonly','readonly');
    }
  });
  $(".answers-wrapper li input.checkbox").click(function(){
    if($(this).attr('checked')){
      $(this).closest('li').find('input:text').attr('readonly','');
      $(this).closest('li').find('input:text').focus();
    }else{
      $(this).closest('li').find('input:text').val('');
      $(this).closest('li').find('input:text').attr('readonly','readonly');
    }
  });
}

// Add empty class to input text and textarea
function addClassEmpty(){
      $('.answers-wrapper input.text[value=""]').addClass('empty');
      $('.answers-wrapper input[type=text][value=""]').addClass('empty');
      $('.answers-wrapper textarea').each(function(index) {
        if ($(this).val() == ""){
          $(this).addClass('empty');
        }
      });

    $("input.text,input[type=text]").live("blur", function(){ 
      if ($(this).val() == ""){
        $(this).addClass('empty');
      }else{
        $(this).removeClass('empty');
      }
    });
    $("textarea").live("blur", function(){ 
      if ($(this).val() == ""){
        $(this).addClass('empty');
      }else{
        $(this).removeClass('empty');
      }
    });
}
// Replace common alert with jquery-ui dialog
function jalert(text) {
	var $dialog = $('<div></div>')
		.html(text)
		.dialog({
			title: '',
			dialogClass: 'alert',
			buttons: { "Ok": function() { $(this).dialog("close"); } },
			modal: true
		});

	$dialog.dialog('open');
}
// Uncomment this part to replace default alert
/*function alert(text) {
	jalert(text);
}*/



$(document).ready(function(){
  //autoDisabledComment();
  addClassEmpty();

})


