<div class="model-container clearfix">
    <div id="notice" class="text-center"></div>
    <input type="hidden" id="ia_<?=$fn?>"                value="<?=$fn?>" />
    <input type="hidden" id="<?=$fn?>_minfiles"          value="<?=$minfiles?>" />
    <input type="hidden" id="<?=$fn?>_maxfiles"          value="<?=$maxfiles?>" />
    <input type="hidden" id="<?=$fn?>_maxfilesize"       value="<?=$qidattributes['max_filesize']?>" />
    <input type="hidden" id="<?=$fn?>_allowed_filetypes" value="<?=$qidattributes['allowed_filetypes']?>" />
    <input type="hidden" id="preview"                   value="<?=Yii::app()->session['preview']?>" />
    <input type="hidden" id="<?=$fn?>_show_comment"      value="<?=$qidattributes['show_comment']?>" />
    <input type="hidden" id="<?=$fn?>_show_title"        value="<?=$qidattributes['show_title']?>" />
    <input type="hidden" id="<?=$fn?>_licount"           value="0" />
    <input type="hidden" id="<?=$fn?>_Cfilecount"         value="0" />

    <!-- The upload button -->
    <div class="upload-div">
        <button id="button_<?=$qid?>" class="btn btn-outline-secondary" type="button" ><?=gT("Select file")?></button>
    </div>
    <p class="alert alert-info uploadmsg"><?=sprintf(gT("You can upload %s under %s KB each."), $qidattributes['allowed_filetypes'], $qidattributes['max_filesize'])?></p>
    <div id="uploadstatus_<?=$qid?>" class="uploadstatus alert alert-warning d-none"></div>
    <!-- The list of uploaded files -->
</div>
        
