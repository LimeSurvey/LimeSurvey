CKEDITOR.editorConfig = function( config )
{

    config.filebrowserBrowseUrl = CKEDITOR.basePath+'../kcfinder/browse.php?type=files';
    config.filebrowserImageBrowseUrl = CKEDITOR.basePath+'../kcfinder/browse.php?type=images';
    config.filebrowserFlashBrowseUrl = CKEDITOR.basePath+'../kcfinder/browse.php?type=flash';
    config.filebrowserUploadUrl = CKEDITOR.basePath+'../kcfinder/upload.php?type=files';
    config.filebrowserImageUploadUrl = CKEDITOR.basePath+'../kcfinder/upload.php?type=images';
    config.filebrowserFlashUploadUrl = CKEDITOR.basePath+'../kcfinder/upload.php?type=flash';

	config.skin = 'office2003';
	config.toolbarCanCollapse = false;
	config.resize_enabled = false;

    config.toolbar_popup =
    [
        ['Save','Createlimereplacementfields','Source'],
        ['Cut','Copy','Paste','PasteText','PasteFromWord'],
        ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
        ['Image','Flash','Table','HorizontalRule','Smiley','SpecialChar'],
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
        ['Maximize','Createlimereplacementfields','Source'],
        ['Cut','Copy','Paste','PasteText','PasteFromWord'],
        ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
        ['Image','Flash','Table','HorizontalRule','Smiley','SpecialChar'],
        '/',
        ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
        ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote','CreateDiv'],
        ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
        ['BidiLtr', 'BidiRtl'],
        ['Link','Unlink','Anchor','Iframe'],
        '/',
        ['Styles','Format','Font','FontSize'],
        ['TextColor','BGColor'],
        [ 'ShowBlocks','Templates'],
        '/',
        ['Maximize','Createlimereplacementfields','Source'],
        ['Bold','Italic','Underline'],
        ['NumberedList','BulletedList','-','Outdent','Indent'],
        ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
        ['Link','Unlink','Image']
    ];


   /* for a later time when CKEditor can change the toolbar on maximize

   config.toolbar_inline =
    [
        ['Maximize','Createlimereplacementfields','Source'],
        ['Bold','Italic','Underline'],
        ['NumberedList','BulletedList','-','Outdent','Indent'],
        ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
        ['Link','Unlink','Image']
    ];*/


   	config.extraPlugins = "limereplacementfields,ajax";

};