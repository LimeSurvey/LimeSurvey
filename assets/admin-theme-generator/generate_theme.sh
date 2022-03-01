#!/bin/bash

# author : Louis Gac
# email  : louis.gac@limesurvey.org

###################################################################
#  _     _              ___
# | |   (_) _ __   ___ / __| _  _  _ _ __ __ ___  _  _
# | |__ | || '  \ / -_)\__ \| || || '_|\ V // -_)| || |
# |____||_||_|_|_|\___||___/ \_,_||_|   \_/ \___| \_, |
#    _       _         _         _____  _         |__/
#   /_\   __| | _ __  (_) _ _   |_   _|| |_   ___  _ __   ___
#  / _ \ / _` || '  \ | || ' \    | |  | ' \ / -_)| '  \ / -_)
# /_/ \_\\__,_||_|_|_||_||_||_|   |_|  |_||_|\___||_|_|_|\___|
#   ___                             _
#  / __| ___  _ _   ___  _ _  __ _ | |_  ___  _ _
# | (_ |/ -_)| ' \ / -_)| '_|/ _` ||  _|/ _ \| '_|
#  \___|\___||_||_|\___||_|  \__,_| \__|\___/|_|
#
##################################################################

# This script will generate the lime-admin-colors.css files for each template and copy them to the indicated path
# It can also copy all the JS files and the files lime-admin-common.css and adminstyle-rtl.css file from Sea_Green to the other templates

# usage : ./generate_theme.sh [PATH] [UPDATE_JS_AND_COMMON]"
# example : ./generate_theme.sh  "/var/www/limesurvey/LimeSurveyNext" Y'

STYLE_BASE_PATH="/themes/admin"

# Dependecies Version Numbers
INSTALL_YARN_VERSION=1.22.5

# COLOR CONSTANTS
COLOR_RED="\033[1;31m"
COLOR_GREEN="\033[1;32m"
COLOR_YELLOW="\033[1;33m"
COLOR_BLUE="\033[1;34m"
COLOR_NO_COLOR="\033[0m"

cat ./ascii/logo.txt;

# BEGIN Install Dependecies
echo "";
echo -e "$COLOR_BLUE Installing Dependencies";
echo -e "$COLOR_BLUE ==> Ensuring .bashrc exists and is writable."

touch ~/.bashrc

# Install Yarn 1 Package Manager
echo -e "$COLOR_BLUE Install $COLOR_YELLOW yarn $COLOR_BLUE package manager.";
rm -rf ~/.yarn

curl --silent https://yarnpkg.com/install.sh | bash -s -- --version $INSTALL_YARN_VERSION
echo -e "$COLOR_BLUE Package $COLOR_YELLOW yarn $COLOR_BLUE was successfully installed.";

# Install node-sass
echo -e "$COLOR_BLUE Install $COLOR_YELLOW node-sass $COLOR_BLUE package.";
yarn add node-sass
echo -e "$COLOR_BLUE Package $COLOR_YELLOW node-sass $COLOR_BLUE was successfully installed.";

# Install r2
# Source: https://github.com/ded/R2
echo -e "$COLOR_BLUE Install $COLOR_YELLOW R2 $COLOR_BLUE package.";
yarn add R2
echo -e "$COLOR_BLUE Package $COLOR_YELLOW R2 $COLOR_BLUE was successfully installed.";

echo -e "$COLOR_BLUE -------------------------------------------------------------------------- $COLOR_NO_COLOR";
# END Install Dependencies

echo ""
echo " This script will generate the lime-admin-colors.css files for each
 template and copy them to the indicated path";
echo " So it will also re-create the RTL files, using node.js R2 command.
 Make sure you have node.js and R2 installed!";
echo " It can also copy all the JS files and the lime-admin-common.css
 file from Sea_Green to the other templates";
echo " usage : ./generate_theme.sh [PATH] [UPDATE_JS_AND_COMMON]";
echo ' example : ./generate_theme.sh  "/var/www/limesurvey/LimeSurveyNext" Y';
echo "--------------------------------------------------------------------------"

# Test if parameters are corrects
# Test path
if [ -z "$1" ]
then
    echo "";
    echo "ERROR !!!!";
    echo "Please, provide your LimeSurvey path";
    echo "./generate_theme.sh [PATH]  [UPDATE_JS_AND_COMMON]";
    echo 'example : ./generate_theme.sh  "/var/www/limesurvey/LimeSurveyNext" Y';
    echo "";
    exit;
else
    if [ ! -d "$1" ];
    then
        echo "";
        echo -e "$COLOR_RED ERROR !!!!";
        echo -e "$COLOR_RED $1 is not a directory !";
        echo "";
        echo "";
        exit;
    else
        path=$1;
        echo "";
        echo "";
        echo -e "$COLOR_GREEN -- The script will update $1";
    fi
fi

# Test copy JavaScript
if [ -z "$2" ] || ( [ "$2" != "Y" ] && [ "$2" != "N" ])
then
    echo "";
    echo -e "$COLOR_RED ERROR !!!!";
    echo -e "$COLOR_RED Please, indicate if you want to update js (Y/N)";
    echo -e "$COLOR_RED ./generate_theme.sh [PATH]  [UPDATE_JS_AND_COMMON]";
    echo -e "$COLOR_RED example : ./generate_theme.sh  '/var/www/limesurvey/LimeSurveyNext' Y";
    echo "";
    exit;
else
    if [ "$2" == "Y" ]
    then
        updateJS="Y";
        echo -e "$COLOR_YELLOW -- The script will copy "$path$STYLE_BASE_PATH"/Sea_Green javascript and lime-admin-common.css files";
    else
        updateJS="N";
        echo -e "$COLOR_YELLOW -- The script will $COLOR_RED NOT $COLOR_YELLOW update javascript and lime-admin-common.css files $COLOR_NO_COLOR";
    fi
fi

# Defining colors by template
declare -A template
template[Apple_Blossom]='#AA4340';
template[Bay_of_Many]='#214F7E';
template[Black_Pearl]='#071630';
template[Dark_Sky]='#000000';
template[Free_Magenta]='#C63678';
template[Purple_Tentacle]='#993399';
template[Sea_Green]='#328637';
template[Noto_All_Languages]='#328637';
template[Sunset_Orange]='#FE5B35';

echo "Templates to update: ";
for i in "${!template[@]}"
do
  echo "---> Template : "$i"  Color : "${template[$i]} ;
done
read -r -p "Continue? [Y/N] " response
if [ "$response" != "Y" ] && [ "$response" != "y" ]
then
    echo "Abort";
    exit;
fi

echo "";
cat ./ascii/process.txt;
echo "";

# Generate CSS files from SASS
for i in "${!template[@]}"
do
  color=${template[$i]};
  echo -e "$COLOR_YELLOW ---> Template : $i";
  echo -e "$COLOR_YELLOW      Color : " $color;

  # Change the first line of the scss file to indicate the template name in comment
  sed -i "1s/.*/\/\/Template: $i;/" lime-admin-colors.scss

  # Change the second line of the scss file to set the right color
  sed -i "2s/.*/\$base-color: $color;/" lime-admin-colors.scss

  # Generate css
  echo -e "$COLOR_BLUE      --> Generate CSS. $COLOR_NO_COLOR";

  # Lime-admin-colors CSS
  echo -e "$COLOR_BLUE          --> lime-admin-colors.css";
  ./node_modules/node-sass/bin/node-sass lime-admin-colors.scss > lime-admin-colors.css;
  echo -e "$COLOR_GREEN              ---> css generated! $COLOR_NO_COLOR";

  # Lime-admin-common CSS
  echo -e "$COLOR_BLUE          --> lime-admin-common.css";
  ./node_modules/node-sass/bin/node-sass lime-admin-common.scss > lime-admin-common.css;
  echo -e "$COLOR_GREEN             ---> css generated! $COLOR_NO_COLOR";

  # Copy file
  echo -e "$COLOR_BLUE      --> Copy CSS. $COLOR_NO_COLOR";

  # Copy Lime-admin-colors CSS
  echo -e "$COLOR_BLUE          --> lime-admin-colors.css $COLOR_NO_COLOR";
  cp -fv ./lime-admin-colors.css "$path$STYLE_BASE_PATH"/$i/css/
  echo -e "$COLOR_GREEN       ---> css copied. $COLOR_NO_COLOR";

  # Copy Lime-admin-common CSS
  echo -e "$COLOR_BLUE          --> lime-admin-common.css $COLOR_NO_COLOR";
  cp -fv ./lime-admin-common.css "$path$STYLE_BASE_PATH"/$i/css/
  echo -e "$COLOR_GREEN       ---> css copied. $COLOR_NO_COLOR";
done

echo "";
echo "GENERATING RTL FILES!";
echo "";

declare -a CssToRTL=('lime-admin-common' 'statistics');
RTLfiles=();
for z in "${!CssToRTL[@]}"
do
  fileRoot=${CssToRTL[$z]};
  file=$path$STYLE_BASE_PATH'/Sea_Green/css/'$fileRoot'.css';
  ./node_modules/r2/bin/r2 "$file" "${file/.css/-rtl.css}";
  RTLfile=$fileRoot'-rtl.css';
  RTLfiles+=("$RTLfile");
done;
RTLfiles+=("adminstyle-rtl.css");

# Copy Sea Green JavaScript, lime-admin-common.css, and adminstyle-rtl.css
if [ "$updateJS" == "Y" ]
    then
        cat ./ascii/jscommon.txt;
        for i in "${!template[@]}"
        do
            if [ "$i" != "Sea_Green" ]
            then
                jsSource=$path$STYLE_BASE_PATH'/Sea_Green/scripts/*';
                jsTarget=$path$STYLE_BASE_PATH"/$i/scripts/";

                echo -e "$COLOR_BLUE Copying Sea Green JavaScript files to $i";
                cp -Rf $jsSource $jsTarget;

                declare -a CommonCss=('lime-admin-common.css' 'printablestyle.css' 'statistics.css');

                for z in "${!CommonCss[@]}"
                do
                    commonCssSource=$path$STYLE_BASE_PATH'/Sea_Green/css/'${CommonCss[$z]};
                    commonCssTarget=$path$STYLE_BASE_PATH'/'$i'/css/'${CommonCss[$z]};

                    echo "Copying Sea Green " ${CommonCss[$z]} " file to $i";
                    cp -Rf $commonCssSource $commonCssTarget;
                done;

                echo "";
                echo "";
                echo "Copying the RTL files";

                for j in "${!RTLfiles[@]}"
                do
                    RTLfileName=${RTLfiles[$j]};
                    RTLfileSource=$path$STYLE_BASE_PATH'/Sea_Green/css/'$RTLfileName;
                    RTLfileTarget=$path$STYLE_BASE_PATH'/'$i'/css/'$RTLfileName;
                    cp -Rf $RTLfileSource $RTLfileTarget;
                    echo "---> RTL File : "$RTLfileSource;
                done;
            fi
        done
fi
echo -e "$COLOR_GREEN Complete !";
echo -e "$COLOR_BLUE Check message above for eventual $COLOR_RED errors";
