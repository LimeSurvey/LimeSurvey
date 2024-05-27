$(document).on('ready pjax:scriptcomplete', function () {
    $('#editor-link-button').on('click', function(e){
        let url = this.getAttribute('data-url');
        $.ajax({
            url: url,
            method: 'GET',
        });
    });
});
