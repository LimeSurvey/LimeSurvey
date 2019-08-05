import Embedo from 'embedo';
import './scss/main.scss';

// Initialize once (prefer globally)
const embedo = new Embedo({
    facebook: true,
    twitter: true,
    instagram: true,
    pinterest: true,
    youtube: true,
    vimeo: true,
    github: true,
    soundcloud: true,
    googlemaps: true
});

$(document).on('ready pjax:scriptcomplete', function(){
    $('oembed').each(
        function(i,item) {
            if($(this).find(".svgcontainer").length == 0) {
                $(this).append(`
                <div class="svgcontainer">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 26 26">
                        <polygon class="play-btn__svg" points="9.33 6.69 9.33 19.39 19.3 13.04 9.33 6.69"/>
                        <path class="play-btn__svg" d="M26,13A13,13,0,1,1,13,0,13,13,0,0,1,26,13ZM13,2.18A10.89,10.89,0,1,0,23.84,13.06,10.89,10.89,0,0,0,13,2.18Z"/>
                    </svg>
                </div>`);
            }
        }
    )

    $('oembed').off('click.embeddable');
    $('oembed').on('click.embeddable',  function() {
        $(this).find(".svgcontainer").remove();
        const url = $(this).attr('url');
        embedo.load(this, url)
        .done((result) => {
            console.ls.log(result);
        })
        .fail((result) => {
            console.ls.error(result);
        })
    });    
});