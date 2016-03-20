<script type="text/javascript" src="<?php echo Yii::app()->getConfig('sCKEditorURL'); ?>/ckeditor.js"></script>
<script type='text/javascript'>
    <!--
    CKEDITOR.on('dialogDefinition', function (ev) {
        var dialogName = ev.data.name;
        var dialogDefinition = ev.data.definition;

        // Remove upload tab from Link and Image dialog as it interferes with
        // CSRF protection and upload can be reached using the browse server tab
        if ( dialogName == 'link')
        {
           // remove Upload tab
           dialogDefinition.removeContents( 'upload' );
        }
        if ( dialogName == 'image')
        {
           // remove Upload tab
           dialogDefinition.removeContents( 'Upload' );
        }
    });

    var sReplacementFieldTitle = '<?php eT('Placeholder fields','js');?>';
    var sReplacementFieldButton = '<?php eT('Insert/edit placeholder field','js');?>';
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
        console.log('gid'+gid);
        controlidena = fieldname + '_popupctrlena';
        controliddis = fieldname + '_popupctrldis';
        numwindows = editorwindowsHash.length;
        activepopup = find_popup_editor(fieldname);

        if (activepopup == null)
        {
            document.getElementsByName(fieldname)[0].readOnly=true;
            document.getElementsByName(fieldname)[0].className='readonly form-control input-lg';
            document.getElementById(controlidena).style.display='none';
            document.getElementById(controliddis).style.display='';

            if (fieldname == '')
                fieldname='0';

            if (fieldtext == '')
                fieldtext='0';

            if (fieldtype == '')
                fieldtype='0';

            if (action == '')
                action='0';

            if (sid == '')
                sid='0';

            if (gid == '')
                gid='0';

            if (qid == '')
                qid='0';



            popup = window.open('<?php echo $this->createUrl('admin/htmleditor_pop/sa/index'); ?>/name/'+fieldname+'/text/'+fieldtext+'/type/'+fieldtype+'/action/'+action+'/sid/'+sid+'/gid/'+gid+'/qid/'+qid+'/lang/<?php echo App()->language; ?>','', 'location=no, status=yes, scrollbars=auto, menubar=no, resizable=yes, width=690, height=500');

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
