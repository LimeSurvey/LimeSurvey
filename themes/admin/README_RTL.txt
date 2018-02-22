This is information about how to generate right-to-left CSS.

Right-to-left locale, e.g. Hebrew

Use the tool R2 (nodejs) in styles/Sea_Green/css/ to convert from ltr to rtl. Source: https://pyjamacoder.com/2012/02/01/twitter-bootstrap-v2-rtl-edition/

    $ cd styles/Sea_Green/css
    $ rm *-rtl.css
    $ git checkout adminstyle-rtl.css  # This file is manually edited
    $ for file in *.css; do if [ "$file" == "adminstyle-rtl.css" ] ; then continue; fi; r2 "$file" "${file/.css/-rtl.css}"; done

PLEASE OBSERVE: The only file which has manually RTL-style is adminstyle-rtl.css. All other rtl-files are generated using R2.

Things to check:

* Plugin settings. Core plugins should use localization.
* Copy files from Sea_green into the other templates
* Minimum screen resolution: 1024 x 768
* Test a survey
