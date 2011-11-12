$(document).ready(function(){
    if(!$('#tokenattribute').length ) {
                    alert("All the attributes are automatically mapped");
                }
         var height = $(document).height();
         var width = $(document).width();
         var tokencurrentarray = {};
         var newcurrentarray = {};
         $('#tokenattribute').css({ 'height' : height-200});
         $('#centralattribute').css({ 'height' : height-200});
         $('#newcreated').css({ 'height' : height-200});
         $("#tokenatt").sortable({ connectWith:'.centralatt,.newcreate',helper: 'clone',appendTo: 'body'});
         $("ul.centralatt").sortable({ helper: 'clone',appendTo: 'body', connectWith: "ul",
                beforeStop: function(event,ui) { 
                        $(this).sortable('cancel');
                    },
                receive: function(event,ui) {
                tokencurrentarray = $(this).sortable('toArray');
                var tattpos = jQuery.inArray($(ui.item).attr('id'),tokencurrentarray);
                var cattpos = tattpos+1;
                var tattid = tokencurrentarray[cattpos-2];
                var cattid = $(ui.item).attr('id');
                if(tattpos == 0 )
                    {
                        alert("You have to pair it with one attribute of the token table");
                        $(ui.sender).sortable('cancel');
                    }
                else if($("#"+tattid).css('color') == 'rgb(204, 204, 204)')
                    {
                        alert("Only one central attribute is mapped with token attribute ");
                        $(ui.sender).sortable('cancel');
                    }
                else
                    {
                        $('ul.centralatt > li:nth-child('+tattpos+')').css("color","white");
                        $('ul.centralatt > li:nth-child('+cattpos+')').css("color","white");
                        $("#"+cattid).css("background-color","#696565");
                        $("#"+tattid).css("background-color","#696565");
                    }
                }
               });
                $("ul.newcreate").sortable({ helper: 'clone',appendTo: 'body', dropOnEmpty: true,
                receive: function(event,ui) {
                newcurrentarray = $(this).sortable('toArray');
                var cpdbattpos = jQuery.inArray($(ui.item).attr('id'),newcurrentarray)
                var size = $(".newcreate li").size();
                if(cpdbattpos == 0 && size>1)
                    {
                        alert("You have to add the element below the list");
                        $(ui.sender).sortable('cancel');
                    }
                    
                else if(newcurrentarray[cpdbattpos+1]=='tb')
                    {
                        alert("Only one central attribute is mapped with token attribute ");
                       $(ui.sender).sortable('cancel');
                    }
                else
                    {
                        $('.newcreate').append('<li id="tb"><input type="text" id="td_'+$(ui.item).attr('id')+'" value=\"'+$(ui.item).attr('name')+'\"></li>');
                        cpdbattpos = cpdbattpos+1;
                        $('ul.newcreate > li:nth-child('+cpdbattpos+')').css("color", "white");
                        $('ul.newcreate > li:nth-child('+cpdbattpos+')').css("background-color","#696565");
                    }
                }
            
        });   
        $('#attmap').click(function(){
            
                var mappedarray = {};
                var anewcurrentarray = {};
                $.each(tokencurrentarray, function(index,value) { 
                                       
                            if(value[0]=='t')
                                {
                                    mappedarray[encodeURI(tokencurrentarray[index-1].substring(2))] = value.substring(2);
                                }
                          });
                   $.each(newcurrentarray, function(index,value) {
                        if(value[0]=='t')
                        {   
                            anewcurrentarray[$("#td_"+value).val()] = value.substring(2);
                        }
                    });
             $("#processing").dialog({
	            height: 90,
				width: 50,
				modal: true
	            
	        });
                     
        $("#processing").load(copyUrl, {
                        mapped: mappedarray,
                        newarr: anewcurrentarray,
                        surveyid: surveyId
                        }, function(msg){
                            alert(msg);
                            $(this).dialog("close");
                            $(location).attr('href',redUrl);
                });
        }); 
        
});