$(document).ready(function(){
                if(!$('#centralattribute').length ) {
                    alert("All the attributes are automatically mapped");    }
                var height = $(document).height();
                var width = $(document).width();
                var tokencurrentarray = {};
                var newcurrentarray = {};
                $('#tokenattribute').css({'height' : height-200});
                $('#centralattribute').css({'height' : height-200});
                $('#newcreated').css({'height' : height-200});
                $(".newcreate").sortable({connectWith:'.tokenatt,#cpdbatt'});
                $("#cpdbatt").sortable({connectWith:'.tokenatt,.newcreate',helper: 'clone',appendTo: 'body'});
                $("ul.tokenatt").sortable({helper: 'clone',appendTo: 'body', connectWith: "ul",
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
                else if($("#"+tattid).css('color') == 'white')
                    {
                        alert("Only one central attribute is mapped with token attribute ");
                        $(ui.sender).sortable('cancel');
                    }
                else
                    {
                        $('ul.tokenatt > li:nth-child('+tattpos+')').css("color","white");
                        $('ul.tokenatt > li:nth-child('+cattpos+')').css("color","white");
                        $("#"+cattid).css("border-color","#FFFFFF");
                        $("#"+tattid).css("border-color","#FFFFFF");
                        $("#"+cattid).css("background-color","#696565");
                        $("#"+tattid).css("background-color","#696565");
                    }
                }
                /*remove : function(event,ui){
                       var newarray = $(this).sortable('toArray');
                       $.each(tokencurrentarray, function(index, innervalue) {
                             if(innervalue[0]=='c'){
                             if(jQuery.inArray(innervalue,newarray))
                                   {
                                       alert("yes");
                                   }
                             }
                       
                        }); 
                    }*/
                });
                $("ul.newcreate").sortable({helper: 'clone',appendTo: 'body', dropOnEmpty: true,
                receive: function(event,ui) {
                    if($(ui.item).attr('id')[0]=='t')
                    {
                        alert("This list cannot accept token attributes")
                        $(ui.sender).sortable('cancel');
                    }
                        newcurrentarray = $(this).sortable('toArray');
                        var cpdbattpos = jQuery.inArray($(ui.item).attr('id'),newcurrentarray)
                        cpdbattpos = cpdbattpos+1;
                        $('ul.newcreate > li:nth-child('+cpdbattpos+')').css("color", "white");
                        $('ul.newcreate > li:nth-child('+cpdbattpos+')').css("background-color","#696565");
                }
            
        });   
        $('#attmap').click(function(){
                var mappedarray = {};
                $.each(tokencurrentarray, function(index,value) { 
                            if(value[0]=='c')
                                {
                                    mappedarray[tokencurrentarray[index-1].substring(2)] = value.substring(2);
                                }
                            
                        });
                   $.each(newcurrentarray, function(index,value) { 
                        newcurrentarray[index] = value.substring(2);
                    });
             $("#processing").dialog({
	            height: 90,
				width: 50,
				modal: true
	            
	        });
                
        $("#processing").load(copyUrl, {
                        mapped: mappedarray,
                        newarr: newcurrentarray,
                        surveyid: surveyId
                        }, function(msg){
                            $(this).dialog("close");
                            alert(msg);
                            $(location).attr('href',redUrl);
                });
        }); 
        
});