// $Id$

$(document).ready(function(){

        if ($(".answertable tbody").children().length == 0)
            add_label(undefined);
        

	$(".btnaddanswer").live('click',add_label);
        $(".btndelanswer").live('click',del_label);

        $('#neweditlblset0 .answertable tbody').sortable({
                                            update:sort_complete,
                                            distance:2
                                        });

        $('#quickadd').dialog({autoOpen: false,
                                modal: true,
                                width:600,
                                title: $("#quickadd").attr('name')});

        $('.btnquickadd').live('click',function(){
            $('#quickadd').dialog('open');  
        });

        $('#btnqacancel').click(function(){
           $('#quickadd').dialog('close');
        });

        
       $('#btnqareplace').click(quickaddfunction);
       $('#btnqainsert').click(quickaddfunction);
       

       
        
        $("#mainform").submit(function(event,ui){

                 var dataToSend = {};
                 dataToSend['langs'] = [];
                 dataToSend['codelist'] = [];
        
                 $("#tabs>div").each(function(divindex,divelement){
                    var div_language = $(".lslanguage",divelement).val();

                    if (typeof(div_language)!="undefined")
                        dataToSend['langs'].push(div_language);
                });
        
                $("tbody>tr",$("#tabs>div:first")).each(function(trindex,trelement){
                    
                    var tr_code = $(trelement).attr('name');
                    

                    dataToSend['codelist'].push(tr_code);
                    dataToSend[tr_code] = {
                        code: $("#code_"+tr_code).val(),
                        assessmentvalue: $("#assessmentvalue_"+tr_code).val()
                    };

                    $(dataToSend['langs']).each(function(i,e){
                        dataToSend[tr_code]['text_'+e] = $("[name=title_"+e+"_"+tr_code+"]").val();
                        });
                    });
                    $("input[name=dataToSend]").remove();
                    $("#mainform").append("<input type='hidden' name='dataToSend' value='"+js2php(dataToSend)+"' />");
        });

        fix_highlighting();
});

function quickaddfunction(){
    var lsreplace = false;
    if ($(this).attr('id') == 'btnqareplace'){
       lsreplace=true;
    }
    
    if (lsreplace){
        $("#tabs>div:not(:last) tbody>tr").remove();
    }

    lsrows=$('#quickaddarea').val().split("\n");
    var seperatorchar="\t";
    if (lsrows[0].indexOf("\t")==-1){
            seperatorchar=';'
    }



    $(lsrows).each(function(index,element){
        code = undefined;

       params = element.split(seperatorchar);
       i = 0;
       if (params.length > $(".lslanguage").length){
           code = params[0].replace(/[^a-zA-Z 0-9]+/g,'');

           i++;
       }

       if (index!=0){
           event = {};
           event.target = $(".btnaddanswer:last");
           var retcode = add_label(event);
       }
       else{
           var retcode = add_label();
       }



      if (typeof(code)!="undefined")
          $("#code_"+retcode).val(code);

      $(".lslanguage").each(function(){
          $("input[name=title_"+$(this).val()+"_"+retcode+"]").val(params[i]);
          i++;
      });

       
    });
    $("#quickaddarea").html('');
    $('#quickadd').dialog('close');
}


function check_onsubmit(element){
    var onsub = $(element).attr('onsubmit');
    var code = onsub.substr(6,onsub.length);

    return eval(code);
}

function sort_complete(event, ui){
    var newposition = ($(ui.item).parent().children().index($(ui.item)));
    var item = ui.item;

    var position = ui.position;
    var originalposition = ui.originalPosition;
    
    if (originalposition.top > position.top) newposition = newposition - 1;


    $("#tabs div:not(:first) [name="+$(item).attr('name')+"]").each(function(index,element){
       var backup = "<tr name='"+$(item).attr('name')+"'>"+$(element).html()+"</tr>";

       if (newposition >= 0)
            $($(element).parent().children()[newposition]).after(backup);
        
       else
            $(element).parent().prepend(backup);

       $(element).remove();
    });

    fix_highlighting();
}

function add_label(event)
{
	
	if (typeof(event)=="undefined")
            var next_code = 1;
        else
            var next_code = parseInt($('.hiddencode',$(event.target).parent().parent()).val()) + 1;
	
	var html = createNewLabelTR(true,true);

        if (typeof(event)=="undefined")
            var row_id = -1;
        else
            var row_id = ($(event.target).parent().parent().parent().children().index($(event.target).parent().parent()));

        var randomid = 'new' + Math.floor(Math.random()*111111);

	html = str_replace("###assessmentval###",'0',html);
	html = str_replace("###codeval###",next_code,html);
	html = str_replace("###next###",randomid,html);
	html = str_replace("###lang###",$("#tabs div:first .lslanguage").val(),html);

        
	if (typeof(event) == "undefined")
            $("#tabs div:first tbody").html(html);
        else
            $(event.target).parent().parent().after(html);

        html = createNewLabelTR(true,false);
        
        html = str_replace("###assessmentval###",'0',html);
	html = str_replace("###codeval###",next_code,html);
	html = str_replace("###next###",randomid,html);

        $("#tabs div:not(:first)").each(function(index,element){

            var temp_html = str_replace("###lang###",$(".lslanguage",element).val(),html);
            if (row_id >= 0){
                $($("tbody",element).children()[row_id]).after(temp_html);
            }
            else
                $(".answertable tbody",$(element)).html(temp_html);
            
        });

        $("tr[name="+randomid+"]").hide().fadeIn(1000);
	
	fix_highlighting();

        return randomid;
}


function del_label(event){

    var id = $(event.target).parent().parent().attr('name');

    $("[name="+id+"]").remove();

    fix_highlighting();

    if ($(".answertable tbody").children().length == 0)
            add_label(undefined);

}

function fix_highlighting(){
    $("tbody tr").removeClass("highlight");
    
    $("tbody tr:even").addClass("highlight");
}

function createNewLabelTR(alternate,first){
    x = "<tr ";
    if (alternate)
        x = x + "class= 'highlight' ";
    x = x + "style = 'white-space: nowrap;' name='###next###'>";

    if (!first)
        x = x + "<td>###codeval###</td><td>###assessmentval###</td>";
    else
        x = x + "<td><img src=" + ci_path + "/handle.png></td><td>"
                + "<input type='hidden' class='hiddencode' value='###codeval###' />"
                + "<input type='text' class='codeval' value='###codeval###' name='code_###next###' id='code_###next###' size='6' maxlength='5' >"
                + "</td><td>"
                + "<input type=\"text\" class='assessmentval' value=\"###assessmentval###\" name=\"assessmentvalue_###next###\" id=\"assessmentvalue_###next###\" style=\"text-align: right;\" size=\"6\" maxlength=\"5\" >";

    x = x + "<td><input name=\"title_###lang###_###next###\"  type=\"text\" value=\"\" size=\"80\" maxlength=\"3000\" >"+
            "<a title=\"\" id=\"title_###lang###_###next###_ctrl\" href=\"javascript:start_popup_editor('title_###lang###_###next###','[Label:](###lang###)','','','','editlabel','labels')\">"+
            "<img height=\"16\" border=\"0\" width=\"16\" src=" + ci_path + "/edithtmlpopup.png name=\"title_###lang###_###next###_popupctrlena\" id=\"title_###lang###_###next###_popupctrlena\" alt=\"\">"+
            "<img height=\"16\" border=\"0\" align=\"top\" width=\"16\" style=\"display: none;\" src=" + ci_path + "/edithtmlpopup_disabled.png name=\"title_###lang###_###next###_popupctrldis\" id=\"title_###lang###_###next###_popupctrldis\" alt=\"Give focus to the HTML editor popup window\"></a></td>";

    if (first)
        x = x + "<td style=\"text-align: center;\"><img class=\"btnaddanswer\" src=" + ci_path + "/addanswer.png><img class=\"btndelanswer\" src=" + ci_path + "/deleteanswer.png></td>";

    x = x + "</tr>";

    return x;
}



function str_replace (search, replace, subject, count) {
    var i = 0, j = 0, temp = '', repl = '', sl = 0, fl = 0,
            f = [].concat(search),
            r = [].concat(replace),
            s = subject,
            ra = r instanceof Array, sa = s instanceof Array;
    s = [].concat(s);
    if (count) {
        this.window[count] = 0;
    }
 
    for (i=0, sl=s.length; i < sl; i++) {
        if (s[i] === '') {
            continue;
        }
        for (j=0, fl=f.length; j < fl; j++) {
            temp = s[i]+'';
            repl = ra ? (r[j] !== undefined ? r[j] : '') : r[0];
            s[i] = (temp).split(f[j]).join(repl);
            if (count && s[i] !== temp) {
                this.window[count] += (temp.length-s[i].length)/f[j].length;}
        }
    }
    return sa ? s : s[0];
}

