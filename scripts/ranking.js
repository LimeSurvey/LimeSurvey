function doDragDropRank(qID, showpopups, samechoiceheight, samelistheight) {
// TODO : advanced setting in attributes
if (typeof showpopups === 'undefined'){showpopups=true;}
if (typeof samechoiceheight === 'undefined'){samechoiceheight=true;}
if (typeof samelistheight === 'undefined'){samelistheight=true;}

  //Add a class to the question
  $('#question'+qID+'').addClass('dragDropRanking');
  // Hide the default answers list
  $('#question'+qID+' .answers-list').hide();


  // Add connected sortables elements to the question
  // Actually a table : move it to a list is a good idea, but need reviewing template a lot.
  var htmlCode = '<div class="dragDropTable"> \
      <div class="columns2">\
        <strong class="SortableTitle">'+translt.choicetitle+'</strong>\
        <div class="ui-state-default dragDropChoices"> \
          <ul id="sortable-choice-'+qID+'" class="connectedSortable'+qID+' dragDropChoiceList"> \
            <li>'+translt.choicetitle+'</li> \
          </ul> \
        </div> \
      </div>\
      <div class="columns2">\
        <strong class="SortableTitle">'+translt.ranktitle+'</strong>\
        <div class="ui-state-default dragDropRanks"> \
          <ol id="sortable-rank-'+qID+'" class="connectedSortable'+qID+' dragDropRankList selectionSortable"> \
            <li>'+translt.ranktitle+'</li> \
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
      var liCode = '<li class="ui-state-default choice" id="choice_'+$(this).val()+'">' + $(this).text() + '</li>'
      $(liCode).appendTo('#sortable-rank-'+qID+'');
    }
  });
  $('#question'+qID+' .answers-list .select-item:first option').each(function(index, Element) {
    var thisvalue=$(this).val();
    if(thisvalue!='' && jQuery.inArray(thisvalue,ranked)<0){
        var liCode = '<li class="ui-state-default choice" id="choice_'+$(this).val()+'">' + $(this).text() + '</li>'
        $(liCode).appendTo('#sortable-choice-'+qID+'');
    }
  });
  loadDragDropRank(qID);

  // Set up the connected sortable			
  $('#sortable-choice-'+qID+', #sortable-rank-'+qID+'').sortable({
    connectWith: '.connectedSortable'+qID+'',
    placeholder: 'ui-sortable-placeholder',
    helper: 'clone',
    revert: 50,
    receive: function(event, ui) {
      maxanswers= parseInt($("#ranking-"+qID+"-maxans").text(),10);
      if($(this).attr("id")=='sortable-rank-'+qID && $(maxanswers>0 && '#sortable-rank-'+qID+' li').length > maxanswers) {
        sortableAlert (qID,showpopups);
        $(ui.sender).sortable('cancel');
      }
      },
    stop: function(event, ui) {
      $('#sortable-choice-'+qID+'').sortable('refresh');
      $('#sortable-rank-'+qID+'').sortable('refresh');
      updateDragDropRank(qID);
    }
  }).disableSelection();

  if(samechoiceheight){fixChoiceHeight(qID);}
  if(samelistheight){fixListHeight(qID);}
  
  // Allow users to double click to move to selections from list to list
  $('#sortable-choice-'+qID+' li').live('dblclick', function() {
      maxanswers= parseInt($("#ranking-"+qID+"-maxans").text(),10);
      if($(maxanswers>0 && '#sortable-rank-'+qID+' li').length >= maxanswers) {
        sortableAlert (qID,showpopups);
      return false;
    }
    else {
      $(this).appendTo('#sortable-rank-'+qID+'');
      $('#sortable-choice-'+qID+'').sortable('refresh');
      $('#sortable-rank-'+qID+'').sortable('refresh');
      updateDragDropRank(qID);
    }
    });
    $('#sortable-rank-'+qID+' li').live('dblclick', function() {
      $(this).appendTo('#sortable-choice-'+qID+'');
      $('#sortable-choice-'+qID+'').sortable('refresh');
      $('#sortable-rank-'+qID+'').sortable('refresh');
      updateDragDropRank(qID);
    });
  }

function updateDragDropRank(qID){
  $('#question'+qID+' .select-item select').val('');
  $('#sortable-rank-'+qID+' li').each(function(index) {
    // Get value of ranked item
    var liID = $(this).attr("id");
    liIDArray = liID.split('_');
    $('#question'+qID+' .select-item select').eq(index).val(liIDArray[1]);
  });
  $('#question'+qID+' .select-item select').each(function(){
    checkconditions($(this).val(),$(this).attr("name"),'select-one','onchange');
  });
}

function sortableAlert (qID,showpopups)
{
    if(showpopups){
        txtAlert=$("#question"+qID+" .em_num_answers").text()
        alert(txtAlert);
    }
}
function loadDragDropRank(qID){
  $('#question'+qID+' .select-item select').each(function(){
    if($(this).val()!=''){
        $('#sortable-choice-'+qID+' li#choice_'+$(this).val()).appendTo('#sortable-rank-'+qID);
    }
  });
}

// All choice at same height
function fixChoiceHeight(qID){
  maxHeight=0;
  $('.connectedSortable'+qID+' li').each(function(){
    if ($(this).height()>maxHeight){
      maxHeight=$(this).height();
    }
  });
  $('.connectedSortable'+qID+' li').height(maxHeight);
}
// Make the 2 list at maximum height
function fixListHeight(qID){
  totalHeight=0;
  $('.connectedSortable'+qID+' li').each(function(){
    totalHeight=totalHeight+$(this).outerHeight();
  });
  $('.connectedSortable'+qID).height(totalHeight);
}

