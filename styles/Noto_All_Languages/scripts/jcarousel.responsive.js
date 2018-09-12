(function($) {
    $(function() {
        var jcarousel = $('.jcarousel');

        function selectTemplate($that) {
                url = $that.data('url');
                $selected = $that.data('selectedtext');
                $lastSelected = $('.item .disabled');
                $unselectedtext = $lastSelected.data('unselectedtext');

                $.ajax({
                    url : url,
                    type : 'GET',
                    dataType : 'html',

                    // html contains the buttons
                    success : function(html, statut){
                        $lastSelected.removeClass("disabled").removeClass("btn-success");
                        $that.addClass("disabled").addClass("btn-success");
                        $that.empty().append($selected);
                        $lastSelected.empty().append($unselectedtext);
                    },
                    error :  function(html, statut){
                        alert('error');
                    }
                });
        }

        $('.template-miniature').click(function() {
            $bigPicture = $($(this).data('big'));
            $('#carrousel-container .item.active').removeClass('active').addClass('inactive').hide();
            $bigPicture.show().addClass('active');
            $('.jcarousel li').removeClass('active');
            $(this).addClass('active');
        });


            $('.selectTemplate').click(function(){
                selectTemplate($(this));
            });

            $('.imgSelectTemplate').click(function(){
                $button = $(this).next();
                if(!$button.hasClass('disabled'))
                {
                    selectTemplate($button);
                }
            });

        jcarousel
            .on('jcarousel:reload jcarousel:create', function () {
                var carousel = $(this),
                    width = carousel.innerWidth();

                if (width >= 100) {
                    width = width / 4;
                }

                carousel.jcarousel('items').css('width', Math.ceil(width) + 'px');
            })
            .jcarousel({
                wrap: 'circular'
            });

        $('.jcarousel-control-prev')
            .jcarouselControl({
                target: '-=1'
            });

        $('.jcarousel-control-next')
            .jcarouselControl({
                target: '+=1'
            });

    });
})(jQuery);
