
/**
 * A method to use the implemented notifier, via ajax or javascript
 *
 * @param text string  | The text to be displayed
 * @param classes string | The classes that will be put onto the inner container
 * @param styles object | An object of css-attributes that will be put onto the inner container
 * @param customOptions | possible options are:
 *                         useHtml (boolean) -> use the @text as html
 *                         timeout (int) -> the timeout in milliseconds until the notifier will fade/slide out
 *                         inAnimation (string) -> The jQuery animation to call for the notifier [fadeIn||slideDown]
 *                         outAnimation (string) -> The jQuery animation to remove the notifier [fadeOut||slideUp]
 *                         animationTime (int) -> The time in milliseconds the animation will last
 */

window.LS = window.LS || {};

class NotifyFader {
    constructor(){
        this.count = 0;
    }

    increment(){this.count = this.count+1;}
    decrement(){this.count = this.count-1;}
    getCount(){return this.count;};

    create(text, classes, styles, customOptions){

        this.increment();
        customOptions = customOptions || {};
        styles = styles || {};
        classes = classes || "well well-lg";

        const options = {
            useHtml : customOptions.useHtml || true,
            timeout : customOptions.timeout || 3500,
            inAnimation : customOptions.inAnimation || "slideDown",
            outAnimation : customOptions.outAnimation || "slideUp",
            animationTime : customOptions.animationTime || 450
        };

        const container = $("<div> </div>");
        const newID = "notif-container_"+this.getCount();

        container.addClass(classes);
        container.css(styles);

        if(options.useHtml){
            container.html(text);
        } else {
            container.text(text);
        }

        $('#notif-container').clone()
            .attr('id', newID)
            .css({
                display: 'none',
                top : (8*((this.getCount())))+"%",
                position: 'fixed',
                left : "15%",
                width : "70%",
                'z-index':3500
            })
            .appendTo($('#notif-container').parent())
            .html(container);

        // using the option inAnimation as funtion of jquery
        $('#'+newID)[options.inAnimation](
            options.animationTime,
            () => {
                const remove = () => {
                    $('#'+newID)[options.outAnimation](
                        options.animationTime, 
                        () => {
                            $('#'+newID).remove(); 
                            this.decrement(); 
                        }
                    );
                }
                $(this).on('click', remove);
                if(options.timeout) {
                    setTimeout(remove, options.timeout);
                }
            }
        );
    };
};

window.LS.LsGlobalNotifier = window.LS.LsGlobalNotifier || new NotifyFader();

export default function (text, classes, styles, customOptions) {
    window.LS.LsGlobalNotifier.create(text, classes, styles, customOptions);
};
