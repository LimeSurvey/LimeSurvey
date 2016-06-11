# A fork of LimeSurvey 2.06 #

Some user think ergonomy of LimeSurvey 2.50 is not better than 2.06 version. But LimeSurvey need improvement, this fork offer some improvement.

## Installation ##
- See [LimeSurvey manual](https://manual.limesurvey.org/Installation)
- Download and extract <http://extensions.sondages.pro/IMG/auto/limesurvey_sondages-pro.zip>
- If you have git access `git clone https://framagit.org/Shnoulle/LimeSurvey.git  --branch 2.06_SondagesPro --single-branch limesurvey`
- [Run_the_installation_script](https://manual.limesurvey.org/Installation#Run_the_installation_script)



## Improvement and history ##
* 1.0.19
    * Better loading of plugins for command
* 1.0.17
    * Merge from core lts
* 1.0.15/1.0.16
    * Fix bad comparaison of Number (again)
* 1.0.14
    * Add cssclass attribute : allow to manage more easily any plugins for question
* 1.0.13
    * Fix Bad comparaison of NUMBER value after reloading survey
* 1.0.12
    * Merge from core lts : Fixed issue #11145: PHP memory_limit being set too low
* 1.0.11
    * New token table firstname/lastname to 150
* 1.0.10
    * Merge from core lts
* 1.0.9
    * New feature #10571: beforeController event (for web)
    * Fix from core lts
* 1.0.8
    * Fix from core lts
* 1.0.7
    * Fix from core lts : thousand_separator
* 1.0.6
    * Fix relevance of sub questions at X Scale
* 1.0.5
    * Fix language in label sets administration
* 1.0.4
    * Fix SMTP for email
    * Filter only script and not HTML in Survey Logic file.
* 1.0.3
    * Fixed issue #10528: beforeHasPermission event don't happen for owner of survey
    * Fix from lts : Fixed issue: [Security] Survey ID not properly sanitized on survey creation
    * Fix from lts : Fixed issue #10641: pie chart error will make statistics reports unavailable
    * Fix from lts : Fixed issue #10697: 4-byte UTF characters (e.g. Emojis) entered into free text causes database error/truncated text on MySQL
* 1.0.2
    * Fixed issue #10627 : download files with multilingual surveys
* 1.0.1
    * Higher risk that the emails are rated as Spam
    * Filter script in Plugin management and Survey Logic file.
    * Deactivate auto update


## Copyright ##
- Copyright © 2007-2016 The LimeSurvey Project Team / Carsten Schmitz <http://www.limesurvey.org>
- Copyright © 2016 Denis Chenu <http://sondages.pro>
- License: GNU/GPL License v3 or later, see gpl-3.0.txt
- TradeMark: The name LimeSurvey™ and the logo is a registered trademark of Fa. Carsten Schmitz / Germany <https://www.limesurvey.org/about-limesurvey/license>
