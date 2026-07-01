#!/usr/bin/env bash

# Process the markdown files so that they can be included in
# the JavaScript language file as a concatenation of single-quoted strings:

#   1. Escape all embedded single quote characters with backslash
#   2. At the beginning of each line: add single quote character
#   3. At the end of each line: add newline, single quote and plus characters
#   4. Last line: remove plus (concatenation) character at the end of line

for name in headingHelp listHelp inlineStyleHelp imageHelp linkHelp gettingStarted aboutA11yFirst
do
  sed  -e "s/'/\\\'/g" -e "s/^/'/" -e "s/$/\\\n' +/" -e "$ s/ +$//" "${name}.md" > "${name}.tmp"
done

# Insert the modified markdown content for each help topic into the language file

sed -e '/HEADINGHELP/ {'     -e 'r headingHelp.tmp'     -e 'd' -e '}' setLang.js   > setLang-1.js
sed -e '/LISTHELP/ {'        -e 'r listHelp.tmp'        -e 'd' -e '}' setLang-1.js > setLang-2.js
sed -e '/INLINESTYLEHELP/ {' -e 'r inlineStyleHelp.tmp' -e 'd' -e '}' setLang-2.js > setLang-3.js
sed -e '/LINKHELP/ {'        -e 'r linkHelp.tmp'        -e 'd' -e '}' setLang-3.js > setLang-4.js
sed -e '/IMAGEHELP/ {'       -e 'r imageHelp.tmp'       -e 'd' -e '}' setLang-4.js > setLang-5.js
sed -e '/GETTINGSTARTED/ {'  -e 'r gettingStarted.tmp'  -e 'd' -e '}' setLang-5.js > setLang-6.js
sed -e '/ABOUTA11YFIRST/ {'  -e 'r aboutA11yFirst.tmp'  -e 'd' -e '}' setLang-6.js > en.js

# Move the end result to the lang folder
mv en.js ../../lang/

# Remove temp files
rm -f *.tmp setLang-?.js
