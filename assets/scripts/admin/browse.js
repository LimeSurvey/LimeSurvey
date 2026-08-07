/*
* LimeSurvey
* Copyright (C) 2007-2026 The LimeSurvey Project Team
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This xversion may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
*/

/* Tooltip only on mouseenter and only if there are no title
 * This allow to set tooltip only when needed
 */
$(document).on("mouseenter",".browsetable thead th:not([title])",function(){
  $(this).attr('title',$(this).find(".questiontext").text());
  $(this).tooltip({ tooltipClass: "tooltip-text" });//,track: true allow to update always tooltip, but seems really annoying
});
$(document).on("mouseenter",".browsetable tbody td:not([title])",function(){
  if($(this).text().length>20)// 20 seem a good value, maybe less (10 ?)
  {
    $(this).attr('title',$(this).text());
    $(this).tooltip({ tooltipClass: "tooltip-text" });
  }
  else
  {
    $(this).attr('title',"");// Don't do this again
  }
});
