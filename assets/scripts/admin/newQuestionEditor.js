$(document).on('ready pjax:scriptcomplete', function () {
    $('#editor-link-button').on('click', function(e){
        window.location.assign(this.getAttribute('data-url'));
        window.open(newUrl, '_top');
    });
});
