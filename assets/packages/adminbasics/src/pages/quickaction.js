/**
 * Methods for the quickaction module
 * -> deprecated and stalled for now
 */

const methods = {
    surveyQuickActionTrigger : () => {
        const $self = $(this);
        $.ajax({
            url : $self.data('url'),
            type : 'GET',
            dataType : 'json',
            data: {currentState: $self.data('active')},
            // html contains the buttons
            success : function(data, statut){
                const newState = parseInt(data.newState);
                console.log('quickaction resolve', data);
                console.log('quickaction new state', newState);
                $self.data('active', newState);
                if(newState === 1){
                    $('#survey-action-container').slideDown(500);
                } else {
                    $('#survey-action-container').slideUp(500);
                }
                $('#survey-action-chevron').find('i').toggleClass('fa-caret-up').toggleClass('fa-caret-down');
                
            },
            error :  function(html, statut){
                console.error('ERROR!', html, statut);
            }
        });
    },
}

const bindings = ()=>{
    $('#switchchangeformat button').on('click', function(event, state) {
        $('#switchchangeformat button.active').removeClass('active');
        $(this).addClass('active');

        const value = $(this).data('value');
        const url = $('#switch-url').attr('data-url')+'/format/'+value;

        $.ajax({
            url : url,
            type : 'GET',
            dataType : 'html',

            // html contains the buttons
            success : function(html, statut){
            },
            error :  function(html, statut){
                alert('error');
            }
        });

    });
}

export {bindings, methods};