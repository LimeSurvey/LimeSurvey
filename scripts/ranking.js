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
  $('#question'+qID+'').addClass('dragDropRanking');
  // Hide the default answers list but display for media oral or screen reader
  $('#question'+qID+' .answers-list').addClass("hide");
  // We are in javascript, then default tip can be replaced
  $('#question'+qID+' .em_default').html("<div class='hide'>"+$('#question'+qID+' .em_default').html()+"</div><div aria-hidden='true'>"+aRankingTranslations.rankhelp+"</div>");
  $('#question'+qID+' .answers-list').on("change",".select-item",{source:false},function(event,data){
    data = data || event.data;
    if(data.source!='dragdrop')
      loadDragDropRank(qID);;
  });

  // Add connected sortables elements to the question
  // Actually a table : move it to a list is a good idea, but need reviewing template a lot.
  var htmlCode = '<div class="dragDropTable" aria-hidden="true"> \
      <div class="columns2">\
        <strong class="SortableTitle">'+aRankingTranslations.choicetitle+'</strong>\
        <div class="ui-state-default dragDropChoices"> \
          <ul id="sortable-choice-'+qID+'" class="connectedSortable'+qID+' dragDropChoiceList"> \
            <li>'+aRankingTranslations.choicetitle+'</li> \
          </ul> \
        </div> \
      </div>\
      <div class="columns2">\
        <strong class="SortableTitle">'+aRankingTranslations.ranktitle+'</strong>\
        <div class="ui-state-default dragDropRanks"> \
          <ol id="sortable-rank-'+qID+'" class="connectedSortable'+qID+' dragDropRankList selectionSortable"> \
            <li>'+aRankingTranslations.ranktitle+'</li> \
          </ol> \
        </div> \
      </div> \
    </div>';
  $(htmlCode).insertAfter('#question'+qID+' .answers-list');
  $('#sortable-choice-'+qID+' li, #sortable-rank-'+qID+' li').remove();

  // Get the list of choices from the LimeSurvey question and copy them as items into the sortable choices list
  var ranked =[];
  $('#question'+qID+' .answers-list .select-item option:selected').each(function(index, Element) {
    if($(this).val()!=''){
      ranked.push($(this).val());
      htmloption=$("#htmlblock-"+qID+'-'+$(this).val()).html();
      var liCode = '<li class="ui-state-default choice" id="'+rankingname+$(this).val()+'">' + htmloption + '</li>'
      $(liCode).appendTo('#sortable-rank-'+qID+'');
    }
  });
  $('#question'+qID+' .answers-list .select-item:first option').each(function(index, Element) {
    var thisvalue=$(this).val();
    if(thisvalue!='' && jQuery.inArray(thisvalue,ranked)<0){
        htmloption=$("#htmlblock-"+qID+'-'+$(this).val()).html();
        var liCode = '<li class="ui-state-default choice" id="'+rankingname+$(this).val()+'">' + htmloption + '</li>'
        $(liCode).appendTo('#sortable-choice-'+qID+'');
    }
  });
  loadDragDropRank(qID);

  // Set up the connected sortable
  $('#sortable-choice-'+qID+', #sortable-rank-'+qID+'').sortable({
    connectWith: '.connectedSortable'+qID+'',
    forceHelperSize: true,
    forcePlaceholderSize: true,
    placeholder: 'ui-sortable-placeholder',
    helper: 'clone',
    delay: 200,
    revert: 50,
    receive: function(event, ui) {
      maxanswers=parseInt($("#ranking-"+qID+"-maxans").text().trim(),10);
      if($(this).attr("id")=='sortable-rank-'+qID && $(maxanswers>0 && '#sortable-rank-'+qID+' li').length > maxanswers) {
        sortableAlert (qID,showpopups,maxanswers);
        if(showpopups){$(ui.sender).sortable('cancel');}
      }
      },
    stop: function(event, ui) {
      $('#sortable-choice-'+qID+'').sortable('refresh');
      $('#sortable-rank-'+qID+'').sortable('refresh');
      updateDragDropRank(qID);
    }
  }).disableSelection();
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
  $(function() { // Update height for IE7, maybe for other function too
    fixChoiceListHeight(qID,samechoiceheight,samelistheight);
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
    $('#sortable-rank-'+qID+' li').removeClass("error");
    $('#sortable-choice-'+qID+' li').removeClass("error");
    $('#sortable-rank-'+qID+' li:gt('+(maxanswers*1-1)+')').addClass("error");
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
 */
function fixChoiceListHeight(qID,samechoiceheight,samelistheight){
  if(samechoiceheight)
  {
    var maxHeight=0;
    $('.connectedSortable'+qID+' li').each(function(){
      if ($(this).actual('height')>maxHeight){
        maxHeight=$(this).actual('height');
      }
    });
    $('.connectedSortable'+qID+' li').css('min-height',maxHeight+'px');
  }
  if(samelistheight)
  {
    var totalHeight=0;
    $('.connectedSortable'+qID+' li').each(function(){
      totalHeight=totalHeight+$(this).actual('outerHeight',{includeMargin:true});;
    });
    $('.connectedSortable'+qID).css('min-height',totalHeight+'px');
  }
}
