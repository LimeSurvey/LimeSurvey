/**
 * Methods and bindings for the question edit page
 */

 const bindAdvancedAttribute = ()=>{
    $('#showadvancedattributes').click(function(){
        $('#showadvancedattributes').hide();
        $('#hideadvancedattributes').show();
        $('#advancedquestionsettingswrapper').animate({
            "height": "toggle", "opacity": "toggle"
        });

    });
 }

 export {bindAdvancedAttribute};