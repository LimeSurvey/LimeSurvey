// $Id$

$(document).ready(function(){
    $(".listsurveys").tablesorter({ widgets: ['zebra'],     
                                    sortList: [[2,0]],
                                    headers: {7:{sorter:'digit'}, // Full responses
                                              8:{sorter:'digit'}, // Partial Responses
                                              9:{sorter:'digit'} // Total Responses
                                             }
                                   });
    $(".listsurveys tr:eq(1) th:eq(2)").css('min-width','200px');
});
