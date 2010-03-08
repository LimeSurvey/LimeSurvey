// $Id: templates.js 7699 2009-09-30 22:28:50Z c_schmitz $

$(document).ready(function(){
    $(".listsurveys").tablesorter({sortList: [[2,0]],
                                    headers: {7:{sorter:'digit'}, // Full responses
                                              8:{sorter:'digit'}, // Partial Responses
                                              9:{sorter:'digit'} // Total Responses
                                             }
                                   });
});
