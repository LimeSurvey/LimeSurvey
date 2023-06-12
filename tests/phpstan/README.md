PHPStan is a static analyzer. Details here: https://github.com/phpstan/phpstan

Example run:

    $ ./vendor/bin/phpstan analyse plugins/MassAction/ -c tests/phpstan/phpstan.neon -l 3 > phpstan.txt 

PHPStan requires a fix in Yii base class: Yii::app() should @return LSYii_Application, not CApplication.

Other analyzers are psalm and phan (tested but did not get to work). MessDetector and codesniffer are simpler tools that are easy to setup.

Static analyzers can be used in the plugin shop to validate and check third party code.
