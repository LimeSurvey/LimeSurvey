// $Id$

$.tablesorter.addParser({ 
    // set a unique id 
    id: 'mydate', 
    is: function(s) { 
        // return false so this parser is not auto detected 
        return false; 
    }, 
    format: function(s) { 
        // format your data for normalization 
        return $.datepicker.formatDate( '@', $.datepicker.parseDate(userdateformat,s));
    }, 
    // set type, either numeric or text 
    type: 'numeric' 
});

$(document).ready(function(){
    $(".listsurveys").tablesorter({ widgets: ['zebra'],     
                                    sortList: [[2,0]],
                                    headers: {3:{sorter:'mydate'},
                                              7:{sorter:'digit'}, // Full responses
                                              8:{sorter:'digit'}, // Partial Responses
                                              9:{sorter:'digit'} // Total Responses
                                             }
                                   });
    $(".listsurveys tr:eq(1) th:eq(2)").css('min-width','200px');
});
