<script type="text/javascript" src ="<?php echo $this->config->item('sCKEditorURL'); ?>/ckeditor.js" >
<script type='text/javascript'>
    <!--
    var editorwindowsHash = new Object();
    function find_popup_editor(fieldname)
    {
    var window = null;
    for (var key in editorwindowsHash)
    {
    if (key==fieldname && !editorwindowsHash[key].closed)
    {
    window = editorwindowsHash[key];
    return window;
    }
    }
    return null;
    }
    
    function start_popup_editor(fieldname, fieldtext, sid, gid, qid, fieldtype, action)
    {
    controlidena = fieldname + '_popupctrlena';
    controliddis = fieldname + '_popupctrldis';
    numwindows = editorwindowsHash.length;
    activepopup = find_popup_editor(fieldname);
    if (activepopup == null)
    {
    document.getElementsByName(fieldname)[0].readOnly=true;
    document.getElementsByName(fieldname)[0].className='readonly';
    document.getElementById(controlidena).style.display='none';
    document.getElementById(controliddis).style.display='';
    popup = window.open('"<?php echo $this->config->item('homeurl'); ?>/htmleditor-popup.php?fieldname='+fieldname+'&fieldtext='+fieldtext+'&fieldtype='+fieldtype+'&action='+action+'&sid='+sid+'&gid='+gid+'&qid='+qid+'&lang="<?php echo $clang->getlangcode(); ?>','', 'location=no, status=yes, scrollbars=auto, menubar=no, resizable=yes, width=690, height=500');
    editorwindowsHash[fieldname] = popup;
    }
    else
    {
    activepopup.focus();
    }
    }
    
    function updateCKeditor(fieldname,value)
    {
    var mypopup= editorwindowsHash[fieldname];
    if (mypopup)
    {
    var oMyEditor = mypopup.CKEDITOR.instances['MyTextarea'];
    if (oMyEditor) {oMyEditor.setData(value);}
    mypopup.focus();
    }
    else
    {
    var oMyEditor = CKEDITOR.instances[fieldname];
    oMyEditor.setData(value);
    }
    }
    -->
</script>