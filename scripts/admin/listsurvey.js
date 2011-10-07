// $Id: listsurvey.js 9692 2011-01-15 21:31:10Z c_schmitz $

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
                                    sortList: [[3,0]],
                                    selectorHeaders: "thead tr:last th",
                                    headers: {0: {sorter:false},
                                              4: {sorter:'mydate'},
                                              8: {sorter:'digit'}, // Full responses
                                              9: {sorter:'digit'}, // Partial Responses
                                              10:{sorter:'digit'} // Total Responses
                                             }
                                   });
    $(".listsurveys tr:eq(1) th:eq(3)").css('min-width','200px');
    $('#frmListSurveys').submit(function(){
        if ($('#surveysaction').val()=='delete')
        {
           $sConfirmation=sConfirmationDeleteMessage;
        }
        else if ($('#surveysaction').val()=='expire')
        {
           $sConfirmation=sConfirmationExpireMessage;
        }
        return confirm($sConfirmation);
    });
    $('#checkall').change(function(){
       $('.surveycbs').attr('checked',$('#checkall').attr('checked'));
    });
});
