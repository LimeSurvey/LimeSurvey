<script type='text/javascript'>
<!--
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

function DoRemove()
{
  var strText = document.getElementById("additional_languages").options[document.getElementById("additional_languages").selectedIndex].text;
  var strId = document.getElementById("additional_languages").options[document.getElementById("additional_languages").selectedIndex].value;
  AddItem(document.getElementById("available_languages"), strText, strId);
  RemoveItem(document.getElementById("additional_languages"), document.getElementById("additional_languages").selectedIndex);
  sortSelect(document.getElementById("available_languages"));
}

function DoAddG(){  if (document.getElementById("available_groups").selectedIndex>-1)  {  var strText = document.getElementById("available_groups").options[document.getElementById("available_groups").selectedIndex].text;  var strId = document.getElementById("available_groups").options[document.getElementById("available_groups").selectedIndex].value;  AddItem(document.getElementById("groups_in_set"), strText, strId);  RemoveItem(document.getElementById("available_groups"), document.getElementById("available_groups").selectedIndex);  sortSelect(document.getElementById("groups_in_set"));  UpdateLanguageIDs();  }}function DoRemoveG(){  var strText = document.getElementById("groups_in_set").options[document.getElementById("groups_in_set").selectedIndex].text;  var strId = document.getElementById("groups_in_set").options[document.getElementById("groups_in_set").selectedIndex].value;  AddItem(document.getElementById("available_groups"), strText, strId);  RemoveItem(document.getElementById("groups_in_set"), document.getElementById("groups_in_set").selectedIndex);  sortSelect(document.getElementById("available_groups"));}

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


function UpdateLanguageIDs()
{
  document.getElementById("languageids").value = ''; 

  var lbBox = document.getElementById("additional_languages");
  for (var i = 0; i < lbBox.options.length; i++)
  {
  document.getElementById("languageids").value = document.getElementById("languageids").value + lbBox.options[i].value+ ' ';  
  }
  UpdateGroupsinSet();
  return true;
}

function UpdateGroupsinSet()
{
  document.getElementById("groupsinset").value = ''; 

  var lbBox = document.getElementById("groups_in_set");
  for (var i = 0; i < lbBox.options.length; i++)
  {
  document.getElementById("groupsinset").value = document.getElementById("groupsinset").value + lbBox.options[i].value+ ' ';  
  }
  return true;
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

//-->
    </script>
