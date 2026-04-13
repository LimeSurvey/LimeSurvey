# jQuery Actual Plugin

Get the actual width/height of invisible DOM elements with jQuery.



## Description

jQuery has trouble finding the width/height of invisible DOM elements. With element or its parent element has css property 'display' set to 'none'. `$('.hidden').width();` will return 0 instead of the actual width; This plugin simply fix it.



## Demo

- Normal usage see demo/normal.html
- If you use [css3pie](http://css3pie.com/) you might also want to take a look at another demo( demo/css3pie.html )
- Live demo please take a look at [this](http://dreamerslab.com/demos/get-hidden-element-width-with-jquery-actual-plugin) and [this](http://dreamerslab.com/demos/get-hidden-element-width-with-jquery-actual-plugin-with-css3pie/)



## Documentation

- There is a syntax highlight version, please see [this post](http://dreamerslab.com/blog/en/get-hidden-elements-width-and-height-with-jquery/)
- For chinese version please go [here](http://dreamerslab.com/blog/tw/get-hidden-elements-width-and-height-with-jquery/)



## Requires

- jQuery >= 1.2.3



## Browser Compatibility

- [Firefox](http://mzl.la/RNaI) 2.0+
- [Internet Explorer](http://bit.ly/9fMgIQ) 6+
- [Safari](http://bit.ly/gMhzVR) 3+
- [Opera](http://bit.ly/fWJzaC) 10.6+
- [Chrome](http://bit.ly/ePHvYZ) 8+



## Installation
- First, make sure you are using valid [DOCTYPE](http://bit.ly/hQK1Rk)
- Include necessary JS files

<!-- -->

    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script>
    <script type="text/javascript" src="path-to-file/jquery.actual.js"></script>



## Usage

Example code:

    // get hidden element actual width
    $( '.hidden' ).actual( 'width' );

    // get hidden element actual innerWidth
    $( '.hidden' ).actual( 'innerWidth' );

    // get hidden element actual outerWidth
    $( '.hidden' ).actual( 'outerWidth' );

    // get hidden element actual outerWidth and set the `includeMargin` argument
    $( '.hidden' ).actual( 'outerWidth', { includeMargin : true });

    // get hidden element actual height
    $( '.hidden' ).actual( 'height' );

    // get hidden element actual innerHeight
    $( '.hidden' ).actual( 'innerHeight' );

    // get hidden element actual outerHeight
    $( '.hidden' ).actual( 'outerHeight' );

    // get hidden element actual outerHeight and set the `includeMargin` argument
    $( '.hidden' ).actual( 'outerHeight', { includeMargin : true });

    // if the page jumps or blinks, pass a attribute '{ absolute : true }'
    // be very careful, you might get a wrong result depends on how you makrup your html and css
    $( '.hidden' ).actual( 'height', { absolute : true });

    // if you use css3pie with a float element
    // for example a rounded corner navigation menu you can also try to pass a attribute '{ clone : true }'
    // please see demo/css3pie in action
    $( '.hidden' ).actual( 'width', { clone : true });

    // if it is not a block element. By default { display: 'block' }.
    // for example a inline element
    $( '.hidden' ).actual( 'width', { display: 'inline-block' });



## Credits

- Erwin Derksen
- [Jon Tara](https://github.com/jtara)
- [Matt Hinchliffe](https://github.com/i-like-robots)
- [Ryan Millikin](https://github.com/dhamma)
- [Jacob Quant](https://github.com/jacobq)
- [ejn](https://github.com/ejn)
- [Rudolf](https://github.com/qakovalyov)
- [jamesallchin](https://github.com/jamesallchin)



## License

The expandable plugin is licensed under the MIT License (LICENSE.txt).

Copyright (c) 2012 [Ben Lin](http://dreamerslab.com)
