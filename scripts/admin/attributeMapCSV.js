$(document).ready(function(){
                if(!$('#csvattribute').length ) {
//                    alert("All the attributes are automatically mapped");
                }
                var height = $(document).height();
                var width = $(document).width();
                var cpdbattarray = {};
                var newcurrentarray = {};
                $('#centralattribute').css({'height' : height-200});
                $('#csvattribute').css({'height' : height-200});
                $('#newcreated').css({'height' : height-200});
                $(".newcreate").sortable({connectWith:'.cpdbatt,.csvatt'});
                $(".csvatt").sortable({connectWith:'.cpdbatt,.newcreate',helper: 'clone',appendTo: 'body'});
                $("ul.cpdbatt").sortable({helper: 'clone',appendTo: 'body', connectWith: "ul",
                beforeStop: function(event,ui) { 
                        $(this).sortable('cancel');
                    },
                receive: function(event,ui) {
                cpdbattarray = $(this).sortable('toArray');
                var cpdbattpos = jQuery.inArray($(ui.item).attr('id'),cpdbattarray);
                var csvpos = cpdbattpos+1;
                var cpdbattid = cpdbattarray[cpdbattpos-1];
                var csvattid = $(ui.item).attr('id');
                if(cpdbattpos == 0 )
                    {
                        alert("You have to pair it with one attribute of the Central Database");
                        $(ui.sender).sortable('cancel');
                    }
                else if($("#"+cpdbattid).css('color') == 'white')
                    {
                        alert("Only one CSV attribute is mapped with central attribute ");
                        $(ui.sender).sortable('cancel');
                    }
                else
                    {
                        $('ul.cpdbatt > li:nth-child('+cpdbattpos+')').css("color","white");
                        $('ul.cpdbatt > li:nth-child('+csvpos+')').css("color","white");
                        $("#"+cpdbattid).css("background-color","#696565");
                        $("#"+cpdbattid).css("border-color","#FFFFFF");
                        $("#"+csvattid).css("border-color","#FFFFFF");
                        $("#"+csvattid).css("background-color","#696565");
                        
                    }
                }
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
                
                $.each(cpdbattarray, function(index,value) { 
                            if(value[0]=='c' && value[1]=='s')
                                {   
                                    mappedarray[cpdbattarray[index-1].substring(2)] = value.substring(3);
                                }
                            
                        });
                   $.each(newcurrentarray, function(index,value) { 
                        newcurrentarray[index] = value.substring(3);
                    });
           $("#processing").dialog({
	            height: 90,
				width: 50,
				modal: true
	            
	        });
                
        $("#processing").load(copyUrl, {
                        characterset: characterset,
                        seperatorused : seperator,
                        fullfilepath : thefilepath,
                        newarray : newcurrentarray,
                        mappedarray : mappedarray
                        }, function(msg){ 
                        $(location).attr('href',redUrl);
                    });
        }); 
        
});