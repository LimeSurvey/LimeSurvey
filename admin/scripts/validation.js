//We have form validation and other stuff..

function validatefilename ( form )
{
  // see http://www.thesitewizard.com/archive/validation.shtml
  // for an explanation of this script and how to use it on your
  // own website

  // ** START **
  if (form.the_file.value == "") {
    alert( "Please select an sql file to import" );
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

