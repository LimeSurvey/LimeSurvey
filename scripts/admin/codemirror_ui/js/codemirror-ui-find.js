/**
 * @author jgreen
 */
var cursor = null;

function setupFindReplace(){
    document.getElementById('closeButton').onclick = closeWindow;
    document.getElementById('findButton').onclick = find;
    document.getElementById('replaceButton').onclick = replace;
	document.getElementById('replaceAllButton').onclick = replaceAll;
    document.getElementById('replaceFindButton').onclick = replaceFind;
}

function closeWindow(){
    codeMirrorUI.searchWindow = null;
    window.close();
}

function find(){
    var findString = document.getElementById('find').value;
    if (findString == null || findString == '') {
        alert('You must enter something to search for.');
        return;
    }
	
	if(document.getElementById('regex').checked){
		findString = new RegExp(findString);
	}
	
	cursor = codeMirrorUI.mirror.getSearchCursor(findString, true);
    var found = moveCursor(cursor);
	
	//if we didn't find anything, let's check to see if we should start from the top
	if(!found && document.getElementById('wrap').checked){
		cursor = codeMirrorUI.mirror.getSearchCursor(findString, false);
		found = moveCursor(cursor);
	}
	
	if(found){
		cursor.select();
	}else{
		alert("No instances found. (Maybe you need to enable 'Wrap Search'?)");
	}
	
}

function moveCursor(cursor){
	var found = false;
	if( getFindDirection() == "forward" ){
		found = cursor.findNext();
    }else{
		found = cursor.findPrevious();
	}
	return found;
}


function getFindDirection(){
    var dRadio = document.forms[0].elements['direction'];
    
    for (var i = 0; i < dRadio.length; i++) {
        if (dRadio[i].checked) {
            return dRadio[i].value;
        }
    }
    
    return 'no-value?';
    
}


function replaceAll(){
	var cursor = codeMirrorUI.mirror.getSearchCursor(document.getElementById('find').value, false);
    while (cursor.findNext())
      cursor.replace(document.getElementById('replace').value);
}


function replace(){
    cursor.replace(document.getElementById('replace').value);
	//codeMirrorUI.replaceSelection(document.getElementById('replace').value);
    setTimeout(window.focus, 100);
    //alert('replaced!');
}

function replaceFind(){
    replace();
    find();
}
