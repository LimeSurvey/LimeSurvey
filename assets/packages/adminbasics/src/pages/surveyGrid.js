/**
 * Methods to load when a the surveygrid is available
 *     if($('#survey-grid').length>0)
 */

const onExistBinding = ()=>{
        $(document).on('click', '.has-link', function () {
            const linkUrl = $(this).find('a').attr('href');
            window.location.href=linkUrl;
        });
}

export {onExistBinding};

