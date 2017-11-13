CKEDITOR.editorConfig = function( config )
{

    config.filebrowserBrowseUrl = CKEDITOR.basePath+'../kcfinder/browse.php?type=files';
    config.filebrowserImageBrowseUrl = CKEDITOR.basePath+'../kcfinder/browse.php?type=images';
    config.filebrowserFlashBrowseUrl = CKEDITOR.basePath+'../kcfinder/browse.php?type=flash';

    config.filebrowserUploadUrl = CKEDITOR.basePath+'../kcfinder/upload.php?type=files';
    config.filebrowserImageUploadUrl = CKEDITOR.basePath+'../kcfinder/upload.php?type=images';
    config.filebrowserFlashUploadUrl = CKEDITOR.basePath+'../kcfinder/upload.php?type=flash';
    /* Remove included upload tabs */
    config.removeDialogTabs = 'link:upload;image:Upload';
    /* Remove automatic img width/height : better manage "RWD" img */
    config.image_prefillDimensions = false;
    config.image2_prefillDimensions = false;

    config.allowedContent = true;
    config.skin = 'bootstrapck';
    //config.toolbarCanCollapse = true;
    config.autoParagraph = false;
    /* For expression manager */
    config.basicEntities = false; // For <, >, & ( and nbsp)
    config.entities = false; // For ' ( and a lot of other but not <>&)

    config.uiColor = '#f1f1f1';
    if($('html').attr('dir') == 'rtl') {
        config.contentsLangDirection = 'rtl';
    }

    config.toolbar_popup =
    [
        ['Save','Source','Createlimereplacementfields'],
        ['Cut','Copy','Paste','PasteText','PasteFromWord'],
        ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
        ['Image','VideoDetector','Flash','Table','HorizontalRule','Smiley','SpecialChar'],
        '/',
        ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
        ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote','CreateDiv'],
        ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
        ['BidiLtr', 'BidiRtl'],
        ['Link','Unlink','Anchor','Iframe'],
        '/',
        ['Styles','Format','Font','FontSize'],
        ['TextColor','BGColor'],
        [ 'ShowBlocks','Templates']
    ];
    config.toolbar_inline =
    [
        ['Source','Createlimereplacementfields'],
        ['Cut','Copy','Paste','PasteText','PasteFromWord'],
        ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
        ['Image','VideoDetector','Flash'],['Table','HorizontalRule','Smiley','SpecialChar'],
        ['Bold','Italic','Underline','Strike'],['Subscript','Superscript'],
        ['NumberedList','BulletedList'],['Outdent','Indent','Blockquote','CreateDiv'],
        ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
        ['BidiLtr', 'BidiRtl'],
        ['ShowBlocks','Templates'],
        ['Link','Unlink'],
        ['Styles','Format','Font','FontSize'],
        ['Anchor','Iframe'],
        ['TextColor','BGColor']
    ];
   config.toolbar_inline2 =
    [
        ['Maximize','Createlimereplacementfields'],
        ['Bold','Italic','Underline'],
        ['NumberedList','BulletedList','-','Outdent','Indent'],
        ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
        ['Link','Unlink','Image'],
        ['Source']
    ];
   config.extraPlugins = "limereplacementfields,codemirror";
};

// (function () {
//     CKEDITOR.plugins.addExternal('limereplacementfields', CKEDITOR.basePath + '../../assets/scripts/admin/limereplacementfields/', 'plugin.js');
// })();
