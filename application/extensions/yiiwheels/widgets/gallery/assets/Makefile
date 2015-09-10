.PHONY: default css js

MINIFY_CSS=css/blueimp-gallery.css
MINIFY_CSS+= css/blueimp-gallery-indicator.css
MINIFY_CSS+= css/blueimp-gallery-video.css

MINIFY_JS=js/blueimp-helper.js
MINIFY_JS+= js/blueimp-gallery.js
MINIFY_JS+= js/blueimp-gallery-fullscreen.js
MINIFY_JS+= js/blueimp-gallery-indicator.js
MINIFY_JS+= js/blueimp-gallery-video.js

MINIFY_JS_JQUERY=js/blueimp-gallery.js
MINIFY_JS_JQUERY+= js/blueimp-gallery-fullscreen.js
MINIFY_JS_JQUERY+= js/blueimp-gallery-indicator.js
MINIFY_JS_JQUERY+= js/blueimp-gallery-video.js
MINIFY_JS_JQUERY+= js/jquery.blueimp-gallery.js

default: css js

css:
	cat ${MINIFY_CSS} | node_modules/.bin/lessc --yui-compress - > css/blueimp-gallery.min.css

js:
	node_modules/.bin/uglifyjs ${MINIFY_JS} -c -m -o js/blueimp-gallery.min.js
	node_modules/.bin/uglifyjs ${MINIFY_JS_JQUERY} -c -m -o js/jquery.blueimp-gallery.min.js
