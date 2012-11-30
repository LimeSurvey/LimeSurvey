<?php $surveyinfo = getSurveyInfo($surveyid); ?>
<script type='text/javascript'>
    var sReplaceTextConfirmation='<?php $clang->eT("This will replace the existing text. Continue?","js"); ?>';
    function openKCFinder_singleFile(target) {
    window.KCFinder = {};
    window.KCFinder.target = target;
    window.KCFinder.callBack = KCFinder_callback;
    window.open('/third_party/kcfinder/browse.php?opener=custom&type=files&CKEditor=email_invite_en&langCode=en', 'kcfinder_single', 'height=600px, width=800px, modal=yes');
}

$(document).ready(function () {
    $('button.add-attachment').click(function(e)
    {
        e.preventDefault();
        var target = $(this).parent().find('table');
        openKCFinder_singleFile(target); 
        
    });
    $('#attachment-relevance-editor button').click(function()
    {
        $('#attachment-relevance-editor').dialog('close');
        var relevance = $('#attachment-relevance-editor textarea').val();
        $('#attachment-relevance-editor').dialog('option', 'target').val(relevance);
        var span = $('#attachment-relevance-editor').dialog('option', 'target').parents('tr').find('span.relevance');
        if (relevance.length > 50)
        {
            $(span).text(relevance.replace(/(\r\n|\n|\r)/gm,"").substr(0, 47) + '...');
        }
        else
        {
            $(span).text(relevance);
        }

    });
    
    
});

function KCFinder_callback(url)
{
    var filename = decodeURIComponent(url.substring(url.lastIndexOf('/')+1));
    if ($(window.KCFinder.target).is('table'))
    {
        var baserow = '<tr>';
        // Actions
        baserow = baserow + '<td>';
        baserow = baserow + '<img alt="Remove attachment" class="btnattachmentremove" src="/styles/gringegreen/images/deleteanswer.png">';
        //baserow = baserow + '<img alt="Edit attachment relevance" class="btnattachmentrelevance" src="/styles/gringegreen/images/global.png">';
        baserow = baserow + '</td>';

        baserow = baserow + '<td><span class="filename"></span><input class="filename" type="hidden"></td>';
        baserow = baserow + '<td><span class="filesize"></span></td>';
        baserow = baserow + '<td><span class="relevance"></span><input class="relevance" type="hidden"></td>';

        //baserow = baserow + '<td><img alt="Edit attachment relevance" class="btndelanswer" src="/styles/gringegreen/images/global.png"></td>';

        baserow = baserow + '</tr>';
    
        var newrow = $(baserow).clone();
        var templatetype = $(window.KCFinder.target).attr('data-template');
    
        $(newrow).find('span.relevance').text('1');
        $(newrow).find('input.relevance').val('1').attr('name', 'attachments-' + templatetype + '-relevance[]');
        $(newrow).find('input.filename').attr('name', 'attachments-' + templatetype + '-url[]');
        $(newrow).appendTo(window.KCFinder.target);
        $('span.relevance').unbind('click').bind('click', editAttachmentRelevance);
        $('img.btnattachmentremove').unbind('click').bind('click', removeAttachment);
        $('span.filename').unbind('click').bind('click', function(e) {
            e.preventDefault();
            var target = $(this).parents('tr');
            openKCFinder_singleFile(target); 

        });
        }
        else
        {
            var newrow = window.KCFinder.target;
        }
    
    $(newrow).find('span.filename').text(filename);
    $(newrow).find('input.filename').val(url);

    

    window.KCFinder = null;
    
}
function editAttachmentRelevance(e)
{
        e.preventDefault();
        var target = $(this).parents('tr').find('input.relevance');
        var span = $(this).parents('tr').find('span.relevance');
        $('#attachment-relevance-editor textarea').val($(target).val());
        $('#attachment-relevance-editor').dialog({
            'modal': true,
            'minWidth' : 400,
            'minHeight' :200,
            'height': 300,
            'target' : target,
            'title' : 'Relevance equation for: ' + $(this).parents('tr').find('span.filename').text()
        });
}
function removeAttachment(e)
{
    e.preventDefault();
    $(this).parents('tr').remove();
}

</script>
<style type="text/css">

#emailtemplates table.attachments td, #emailtemplates button.add-attachment {
    text-align: left;
    margin: 2px 2px 2px 2px !important;
    
}

table.attachments td span{
    border: 1px solid #999999;
    display:block;
}

table.attachments img, table.attachments span{
    height: 16px;
    cursor: pointer;
}
ul.editor-parent {
    overflow: hidden;
}


</style>
<div class='header ui-widget-header'>
    <?php $clang->eT("Edit email templates"); ?>
</div>
<?php 
?>
<form class='form30newtabs' id='emailtemplates' action='<?php echo Yii::app()->getController()->createUrl('admin/emailtemplates/sa/update/surveyid/'.$surveyid); ?>' method='post'>
    <div id='tabs'>
        <ul>
            <?php foreach ($grplangs as $grouplang): ?>
                <li><a href='#tab-<?php echo $grouplang; ?>'><?php echo getLanguageNameFromCode($grouplang,false); ?>
                        <?php if ($grouplang == Survey::model()->findByPk($surveyid)->language): ?>
                        <?php echo ' ('.$clang->gT("Base language").')'; ?>
                        <?php endif; ?>
                </a></li>
            <?php endforeach; ?>    
        </ul>
        <?php 
            foreach ($grplangs as $key => $grouplang)
            {
                $bplang = $bplangs[$key];
                $esrow = $attrib[$key];
                $aDefaultTexts = $defaulttexts[$key];
                if ($ishtml == true)
                {
                    $aDefaultTexts['admin_detailed_notification']=$aDefaultTexts['admin_detailed_notification_css'].conditionalNewlineToBreak($aDefaultTexts['admin_detailed_notification'],$ishtml);
                }
                $this->renderPartial('/admin/emailtemplates/email_language_tab', compact('surveyinfo', 'ishtml', 'surveyid', 'clang', 'grouplang', 'bplang', 'esrow', 'aDefaultTexts'));
            }
        ?>
    </div>
    <p>
        <input type='submit' class='standardbtn' value='<?php $clang->eT("Save"); ?>' />
        <input type='hidden' name='action' value='tokens' />
        <input type='hidden' name='language' value="<?php echo $esrow->surveyls_language; ?>" />
    </p>
    </form>
<div id="attachment-relevance-editor" style="display: none; overflow: hidden;">
    <textarea style="resize: none; height: 90%; width: 100%; box-sizing: border-box">

    </textarea>
    <button>Apply</button>
</div>
