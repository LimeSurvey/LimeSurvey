function DoAdd()
{
	if (document.getElementById("available_languages").selectedIndex>-1)
	{
		var strText = document.getElementById("available_languages").options[document.getElementById("available_languages").selectedIndex].text;
		var strId = document.getElementById("available_languages").options[document.getElementById("available_languages").selectedIndex].value;
		AddItem(document.getElementById("additional_languages"), strText, strId);
		RemoveItem(document.getElementById("available_languages"), document.getElementById("available_languages").selectedIndex);
		sortSelect(document.getElementById("additional_languages"));
		UpdateLanguageIDs();
	}
}

function DoRemove(minItems,strmsg)
{
	var strText = document.getElementById("additional_languages").options[document.getElementById("additional_languages").selectedIndex].text;
	var strId = document.getElementById("additional_languages").options[document.getElementById("additional_languages").selectedIndex].value;
	if (document.getElementById("additional_languages").options.length>minItems)
	{
		AddItem(document.getElementById("available_languages"), strText, strId);
		RemoveItem(document.getElementById("additional_languages"), document.getElementById("additional_languages").selectedIndex);
		sortSelect(document.getElementById("available_languages"));
		UpdateLanguageIDs();
	}
	else
	if (strmsg!=''){alert(strmsg);}
}



function AddItem(objListBox, strText, strId)
{
	var newOpt;
	newOpt = document.createElement("OPTION");
	newOpt = new Option(strText,strId);
	newOpt.id = strId;
	objListBox.options[objListBox.length]=newOpt;
}

function RemoveItem(objListBox, strId)
{
	if (strId > -1)
	objListBox.options[strId]=null;
}

function GetItemIndex(objListBox, strId)
{
	for (var i = 0; i < objListBox.children.length; i++)
	{
		var strCurrentValueId = objListBox.children[i].id;
		if (strId == strCurrentValueId)
		{
			return i;
		}
	}
	return -1;
}


function UpdateLanguageIDs(mylangs,confirmtxt)
{
	document.getElementById("languageids").value = '';

	var lbBox = document.getElementById("additional_languages");
	for (var i = 0; i < lbBox.options.length; i++)
	{
		document.getElementById("languageids").value = document.getElementById("languageids").value + lbBox.options[i].value+ ' ';
	}
	if (mylangs)
	{
		if (checklangs(mylangs))
		{
			return true;
		} else
		{
			return confirm(confirmtxt);
		}
	}
}

function compareText (option1, option2) {
	return option1.text < option2.text ? -1 :
	option1.text > option2.text ? 1 : 0;
}
function compareValue (option1, option2) {
	return option1.value < option2.value ? -1 :
	option1.value > option2.value ? 1 : 0;
}
function compareTextAsFloat (option1, option2) {
	var value1 = parseFloat(option1.text);
	var value2 = parseFloat(option2.text);
	return value1 < value2 ? -1 :
	value1 > value2 ? 1 : 0;
}
function compareValueAsFloat (option1, option2) {
	var value1 = parseFloat(option1.value);
	var value2 = parseFloat(option2.value);
	return value1 < value2 ? -1 :
	value1 > value2 ? 1 : 0;
}
function sortSelect (select, compareFunction) {
	if (!compareFunction)
	compareFunction = compareText;
	var options = new Array (select.options.length);
	for (var i = 0; i < options.length; i++)
	options[i] =
	new Option (
	select.options[i].text,
	select.options[i].value,
	select.options[i].defaultSelected,
	select.options[i].selected
	);
	options.sort(compareFunction);
	select.options.length = 0;
	for (var i = 0; i < options.length; i++)
	select.options[i] = options[i];
}

function checklangs(mylangs)
{
	selObject=document.getElementById("additional_languages");
	var found;

	for (x = 0; x < mylangs.length; x++)
	{
		found = 0;
		for (i=0;i<selObject.options.length;i++)
		{
			if(selObject.options[i].value == mylangs[x])
			{
				found = 1;
				break;
			}
		}
		if (found == 0) { return false; }
	}
	return true;
}
