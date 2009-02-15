//We have form validation and other stuff..

function validatefilename (form, strmessage )
{
  // see http://www.thesitewizard.com/archive/validation.shtml
  // for an explanation of this script and how to use it on your
  // own website

  // ** START **
  if (form.the_file.value == "") {
    alert( strmessage );
    form.the_file.focus();
    return false ;
  }
  // ** END **
  return true ;
}


// If the length of the element's string is 0 then display helper message
function isEmpty(elem, helperMsg)
{
	if(elem.value.length == 0){
		alert(helperMsg);
		elem.focus(); // set the focus to this input
		return false;
	}
	return true;
}

function codeCheck(prefix, elementcount, helperMsg)
{
 var i, j;
 var X = new Array();

 for (i=0; i<=elementcount; i++) {
   j = document.getElementById(prefix+i);
   if (j != undefined) 
   {
       j.value=trim(j.value);
       X.push(j.value);
   }
 }   
 if (arrHasDupes(X))
 {
 		alert(helperMsg);
	    return false;
 }
 return true;

}


function arrHasDupes( A ) {                          // finds any duplicate array elements using the fewest possible comparison
	var i, j, n;
	n=A.length;
                                                     // to ensure the fewest possible comparisons
	for (i=0; i<n; i++) {                        // outer loop uses each item i at 0 through n
		for (j=i+1; j<n; j++) {              // inner loop only compares items j at i+1 to n
			if (A[i]==A[j]) return true;
	}	}
	return false;
}


// (c) 2006 Simon Wunderlin, License: GPL, hacks want to be free ;)
// This fix forces Firefox to fire the onchange event if someone changes select box with cursor keys 
function ev_gecko_select_keyup_ev(Ev) {
	// prevent tab, alt, ctrl keys from fireing the event
	if (Ev.keyCode && (Ev.keyCode == 1 || Ev.keyCode == 9 || 
	    Ev.keyCode == 16 || Ev.altKey || Ev.ctrlKey))
		return true;
	Ev.target.onchange();
    	return true;
}

function init_gecko_select_hack() {
	var selects = document.getElementsByTagName("SELECT");
	for(i=0; i<selects.length; i++)
			selects.item(i).addEventListener("keyup", ev_gecko_select_keyup_ev, false);
	return true;
}


function getkey(e)
{
   if (window.event) return window.event.keyCode;
      else 
         if (e) return e.which; 
             else return null;
}

function goodchars(e, goods)
{
   var key, keychar;
   key = getkey(e);
   if (key == null) return true;

   // get character
   keychar = String.fromCharCode(key);
   keychar = keychar.toLowerCase();
   goods = goods.toLowerCase();

   // check goodkeys
   if (goods.indexOf(keychar) != -1)
        return true;

   // control keys
   if ( key==null || key==0 || key==8 || key==9  || key==27 )
      return true;

   // else return false
   return false;
}


function trim(stringToTrim) {
    return stringToTrim.replace(/^\s+|\s+$/g,"");
}
