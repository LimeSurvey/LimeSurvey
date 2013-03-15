function doDragDropRank(qID, showpopups, samechoiceheight, samelistheight) {
// TODO : advanced setting in attributes
  if (typeof showpopups === 'undefined'){showpopups=true;}
  if (typeof samechoiceheight === 'undefined'){samechoiceheight=true;}
  if (typeof samelistheight === 'undefined'){ samelistheight=true;}
  var maxanswers= parseInt($("#ranking-"+qID+"-maxans").text(),10);
  var rankingname= "javatbd"+$("#ranking-"+qID+"-name").text();
  var rankingnamewidth=rankingname.length;
  //Add a class to the question
  $('#question'+qID+'').addClass('dragDropRanking');
  // Hide the default answers list
  $('#question'+qID+' .answers-list').hide();


  // Add connected sortables elements to the question
  // Actually a table : move it to a list is a good idea, but need reviewing template a lot.
  var htmlCode = '<div class="dragDropTable"> \
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
    revert: 50,
    receive: function(event, ui) {
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

  if(samechoiceheight){fixChoiceHeight(qID);}
  if(samelistheight){fixListHeight(qID);}
  
  // Allow users to double click to move to selections from list to list
  $('#sortable-choice-'+qID+' li').live('dblclick', function() {
      if($(maxanswers>0 && '#sortable-rank-'+qID+' li').length >= maxanswers) {
        sortableAlert (qID,showpopups,maxanswers);
        if(showpopups){return false;}
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
  var maxanswers= parseInt($("#ranking-"+qID+"-maxans").text(),10);
  var rankingname= "javatbd"+$("#ranking-"+qID+"-name").text();
  var relevancename= "relevance"+$("#ranking-"+qID+"-name").text();
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
  $('#question'+qID+' .select-item select').each(function(index){
    number=index+1;
    if($(this).val()!=""){$("#"+relevancename+number).val("1");}
    checkconditions($(this).val(),$(this).attr("name"),'select-one','onchange');
  });
    $('#sortable-rank-'+qID+' li').removeClass("error");
    $('#sortable-choice-'+qID+' li').removeClass("error");
    $('#sortable-rank-'+qID+' li:gt('+(maxanswers*1-1)+')').addClass("error");
}

function sortableAlert (qID,showpopups)
{
    if(showpopups){
        txtAlert=$("#question"+qID+" .em_num_answers").text()
        alert(txtAlert);
    }
}
function loadDragDropRank(qID){
  var maxanswers= parseInt($("#ranking-"+qID+"-maxans").text(),10);
  var rankingname= "javatbd"+$("#ranking-"+qID+"-name").text();
  var relevancename= "relevance"+$("#ranking-"+qID+"-name").text();
  var rankingnamewidth=rankingname.length;
  // Update #relevance 
  $("[id^=" + relevancename + "]").val('0');
  $('#question'+qID+' .select-item select').each(function(index){
    if($(this).val()!=''){
        number=index+1;
        $("#"+relevancename+number).val("1");
        $('#sortable-choice-'+qID+' li#'+rankingname+$(this).val()).appendTo('#sortable-rank-'+qID);
    }
  });

  $('#sortable-rank-'+qID+' li').removeClass("error");
  $('#sortable-choice-'+qID+' li').removeClass("error");
  $('#sortable-rank-'+qID+' li:gt('+(maxanswers*1-1)+')').addClass("error");
}

// All choice at same height
function fixChoiceHeight(qID){
  maxHeight=0;
  $('.connectedSortable'+qID+' li').each(function(){
    if ($(this).actual('height')>maxHeight){
      maxHeight=$(this).actual('height');
    }
  });
  $('.connectedSortable'+qID+' li').height(maxHeight);
}
// Make the 2 list at maximum height
function fixListHeight(qID){
  totalHeight=0;
  $('.connectedSortable'+qID+' li').each(function(){
    totalHeight=totalHeight+$(this).actual('outerHeight',{includeMargin:true});;
  });
  $('.connectedSortable'+qID).height(totalHeight);
}

