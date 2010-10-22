//$Id$

$(document).ready(function(){
 $(".surveysecurity").tablesorter({
	 	sortList: [[2,0]],
 		headers: { 19: { sorter: false} }
 });


 $(".usersurveypermissions").tablesorter({
         widgets: ['zebra'],
         headers: { 1: { sorter: false},
                    2: { sorter: false},
                    3: { sorter: false},
                    4: { sorter: false},
                    5: { sorter: false}
                  }
 });
 
});

