/**
 * Methods and bindings for the question edit page
 */

 const bindAdvancedAttribute = ()=>{
    
    if ($('#advancedquestionsettingswrapper').length > 0) {
        window.questionFunctions = window.questionFunctions || (new QuestionFunctions()) || null;
        window.questionFunctions.updatequestionattributes();
    }

    $('#showadvancedattributes').click(function(){
        $('#showadvancedattributes').hide();
        $('#hideadvancedattributes').show();
        $('#advancedquestionsettingswrapper').animate({
            "height": "toggle", "opacity": "toggle"
        });

    });
 }

 export default bindAdvancedAttribute;