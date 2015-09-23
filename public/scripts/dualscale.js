/*
 * @license This file is part of LimeSurvey
 * Copyright (C) 2007-2013 The LimeSurvey Project Team / Carsten Schmitz / Denis Chenu
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 *
 */

/**
 * Update answers part for a dual scale radio question and lauch EM
 *
 * @author Denis Chenu (Shnoulle)
 * @param {number} qId The qid of the question where apply.
 * @version 205-01
 */
function doDualScaleRadio(qID) {
  // We can do it before document ready, because function come after answers and we use delegate
  $("#question"+qID+" .jshide").hide();

  // Lauch EM with hidden input
  $("#question"+qID+" table.question").delegate(".noanswer-item :radio","click",function(){
    $(this).closest(".answers-list").find(":radio[value='']").prop("checked", true);
    name=$(this).attr("name");
    name0=name.replace("#1","_0");
    name1=name.replace('#','_');
    $("#java"+name0).val("");
    $("#java"+name1).val("");
    ExprMgr_process_relevance_and_tailoring('change',name0,'hidden');
    ExprMgr_process_relevance_and_tailoring('change',name1,'hidden');
  });
  $("#question"+qID+" table.question").delegate(".answer-item:not(.noanswer-item)  :radio","click",function(){
    $(this).closest(".answers-list").find(":radio[value='']").prop("checked", false);
    name=$(this).attr("name");
    name=name.replace('#','_');
    value=""+$(this).val();
    $("#java"+name).val(value);
    ExprMgr_process_relevance_and_tailoring('change',name,'radio');
  });
}

/**
 * Update answers part for a dual scale dropdown question and lauch EM
 *
 * @author Denis Chenu (Shnoulle)
 * @param {number} qId The qid of the question where apply.
 */
function doDualScaleDropDown(qID) {
  $("#question"+qID+" table.question").delegate("select","change",function(){
    name=$(this).attr("name");
    name=name.replace('#','_');
    value=""+$(this).val();
    $("#java"+name).val(value);
    ExprMgr_process_relevance_and_tailoring('change',name,'select');
  });
}
