/*
 * This file is part of LimeSurvey
 * See COPYRIGHT.php for copyright notices and details.
 * @license magnet:?xt=urn:btih:cf05388f2679ee054f2beb29a391d25f4e673ac3&dn=gpl-2.0.txt  GNU/GPL License v2 or later
 */

/**
 * Update answers part for ranking function
 *
 * @param {number} qId The qid of the question where apply.
 */
function doDragDropRank(qID, showpopups, samechoiceheight, samelistheight) {

// TODO : advanced setting in attributes
  if (typeof showpopups === 'undefined'){showpopups=true;}
  if (typeof samechoiceheight === 'undefined'){samechoiceheight=true;}
  if (typeof samelistheight === 'undefined'){ samelistheight=true;}
  var maxanswers= parseInt($("#ranking-"+qID+"-maxans").text().trim(),10);// We need to test it each time : because it can be dynamic
  var rankingname= "javatbd"+$("#ranking-"+qID+"-name").text().trim();
  var rankingnamewidth=rankingname.length;
  //Add a class to the question
  $('#question'+qID+'').addClass('sortable-activated');
  // Hide the default answers list but display for media oral or screen reader
  // We are in javascript, then default tip can be replaced
  $('#question'+qID+' .em_default').html("<div class='sr-only'>"+$('#question'+qID+' .em_default').html()+"</div><div aria-hidden='true'>"+LSvar.lang.rankhelp+"</div>");
  $('#question'+qID+' .answers-list').on("change",".select-item",{source:false},function(event,data){
    data = data || event.data;
    if(data.source!='dragdrop')
      loadDragDropRank(qID);;
  });

  /* Update rank according to actual value */

  loadDragDropRank(qID);
  // Set up the connected sortable
  $('#sortable-choice-'+qID).sortable({
    group: "sortable-"+qID,
    ghostClass: "ls-rank-placeholder",
  });
  $('#sortable-rank-'+qID).sortable({
    group: "sortable-"+qID,
    ghostClass: "ls-rank-placeholder",
    onSort: function (evt) {
      updateDragDropRank(qID);
    }
  });
  $('#question'+qID+' .ls-remove').remove();

  // Adapt choice and list height
  fixChoiceListHeight(qID,samechoiceheight,samelistheight);
  // Allow users to double click to move to selections from list to list
    $('#sortable-choice-'+qID).delegate('li','dblclick', function() {
      maxanswers=parseInt($("#ranking-"+qID+"-maxans").text().trim(),10);
      if($(maxanswers>0 && '#sortable-rank-'+qID+' li').length >= maxanswers) {
        sortableAlert (qID,showpopups,maxanswers);
        if(showpopups){return false;}
      }
      else {
        $(this).appendTo('#sortable-rank-'+qID+'');
        $('#sortable-choice-'+qID+'').sortable('refresh');
        $('#sortable-rank-'+qID+'').sortable('refresh');
      }
      updateDragDropRank(qID);
    });
    $('#sortable-rank-'+qID).delegate('li','dblclick', function() {
      $(this).appendTo('#sortable-choice-'+qID+'');
      $('#sortable-choice-'+qID+'').sortable('refresh');
      $('#sortable-rank-'+qID+'').sortable('refresh');
      updateDragDropRank(qID);
    });
  }

/**
 * Update answers after updating drag and drop part
 *
 * @param {number} qId The qid of the question where apply.
 */
function updateDragDropRank(qID){
    var maxanswers= parseInt($("#ranking-"+qID+"-maxans").text().trim(),10);
    var rankingname= "javatbd"+$("#ranking-"+qID+"-name").text().trim();
    var relevancename= "relevance"+$("#ranking-"+qID+"-name").text().trim();
  var rankingnamewidth=rankingname.length;
  $('#question'+qID+' .select-item select').val('');
  $('#sortable-rank-'+qID+' li').each(function(index) {
    // Get value of ranked item
    var liID = $(this).attr("id");
    liValue = liID.substr(rankingnamewidth);
    $('#question'+qID+' .select-item select').eq(index).val(liValue);
  });
  // Update #relevance and lauch checkconditions function
  $("[id^=" + relevancename + "]").val('0');
  $('#question'+qID+' .select-item select:lt('+maxanswers+')').each(function(index){
      number=index+1;
      if($(this).val()!="")
      {
          $("#"+relevancename+number).val("1");
      }
      $(this).trigger("change",{ source : 'dragdrop'});
  });
    $('#sortable-rank-'+qID+' li').removeClass("text-error");
    $('#sortable-choice-'+qID+' li').removeClass("text-error");
    $('#sortable-rank-'+qID+' li:gt('+(maxanswers*1-1)+')').addClass("text-error");
}
/**
 * Show an alert if needed
 *
 * @param {number} qId The qid of the question where apply.
 * @param {bool} showpopups Show or not the alert
 */
function sortableAlert (qID,showpopups)
{
    if(showpopups){
        txtAlert=$("#question"+qID+" .em_num_answers").text()
        alert(txtAlert);
    }
}
/**
 * Set the drag and drop according to existing answers
 *
 * @param {number} qId The qid of the question where apply.
 */
function loadDragDropRank(qID){
  var maxanswers= parseInt($("#ranking-"+qID+"-maxans").text().trim(),10);
  var rankingname= "javatbd"+$("#ranking-"+qID+"-name").text().trim();
  var relevancename= "relevance"+$("#ranking-"+qID+"-name").text().trim();
  var rankingnamewidth=rankingname.length;
  // Update #relevance
  $("[id^=" + relevancename + "]").val('0');
  $('#sortable-rank-'+qID+' li').each(function(){
    $(this).appendTo('#sortable-choice-'+qID+'');
  });
  $('#question'+qID+' .select-item select').each(function(index){
    if($(this).val()!=''){
      number=index+1;
      $("#"+relevancename+number).val("1");
      $('#sortable-choice-'+qID+' li#'+rankingname+$(this).val()).appendTo('#sortable-rank-'+qID);
    }
  });
  updateDragDropRank(qID);// Update to reorder select
  $('#sortable-rank-'+qID+' li').removeClass("error");
  $('#sortable-choice-'+qID+' li').removeClass("error");
  $('#sortable-rank-'+qID+' li:gt('+(maxanswers*1-1)+')').addClass("error");
}

/**
 * Fix height of drag and drop according to question settings
 *
 * @param {number} qId The qid of the question where apply.
 * @param {bool} samechoiceheight
 * @param {bool} samelistheight
 * actual is still need, @see Additional Notes at http://api.jquery.com/height/
 * can be replaced by http://stackoverflow.com/a/2548882/2239406 for exemple -jquery3 don't do the job)
 */
function fixChoiceListHeight(qID,samechoiceheight,samelistheight){
  if(samechoiceheight)
  {
    var maxHeight=0;
    $('#sortable-choice-'+qID+' li,#sortable-rank-'+qID+' li').each(function(){
      if ($(this).actual('height')>maxHeight){
        maxHeight=$(this).actual('height');
      }
    });
    $('#sortable-choice-'+qID+' li,#sortable-rank-'+qID+' li').css('min-height',maxHeight+'px');
  }
  if(samelistheight)
  {
    var totalHeight=0;
    $('#sortable-choice-'+qID+' li,#sortable-rank-'+qID+' li').each(function(){
      totalHeight=totalHeight+$(this).actual('outerHeight',{includeMargin:true});/* Border not inside */
    });
    /* Add the padding to min-height */
    $('#sortable-choice-'+qID+',#sortable-rank-'+qID).css('min-height',totalHeight+'px').addClass("ls-sameheight");
  }
}

function triggerEmRelevanceSortable()
{
  $(".sortable-item").on('relevance:on',function(event,data) {
    if(event.target != this) return; // not needed now, but after maybe (2016-11-07)
    data = $.extend({style:'hidden'}, data);
    $(event.target).closest(".ls-answers").find("option[value="+$(event.target).data("value")+"]").prop('disabled',false);
    $(event.target).removeClass("disabled").prop('disabled',false);
  });
  $(".sortable-item").on('relevance:off',function(event,data) {
    if(event.target != this) return; // not needed now, but after maybe (2016-11-07)
    data = $.extend({style:'hidden'}, data);
    $(event.target).closest(".ls-answers").find("option[value="+$(event.target).data("value")+"]").prop('disabled',true);
    $(event.target).addClass("disabled").prop('disabled',true);
  });
}
