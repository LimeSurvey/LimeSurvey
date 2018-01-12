1.1.15
------

- Changed default `twigPathAlias` value to `application.vendor.Twig` to match Yii application template (samdark)

1.1.14
------

- #4: Fixed a little bug with themes (ssidelnikov)
- Added instructions on installation of the extension via Composer to readme (ssidelnikov)

1.1.3
-----

- #10: PHP 5.2 compatibility (resurtm)

1.1.2
-----
- Added global 'void' function to make possible to call functions and methods which return non-string result (Leonid Svyatov)

1.1.1
-----
- Added global 'C' variable from v0.9.5 for accessing Yii core static classes (Leonid Svyatov)
- Added new class 'ETwigViewRendererYiiCoreStaticClassesProxy' to provide global 'C' variable feature (Leonid Svyatov)
- Removed hard-coded global 'Yii' variable from v0.9.5 (it could be done by using 'globals' option) (Leonid Svyatov)
- Removed unnecessary now classes: 'YiiStatic', 'twigC' and 'twigCObj' (Leonid Svyatov)

1.1.0
-----
- Added an ability to add Twig globals (objects and static classes) (Leonid Svyatov)
- Added an ability to add Twig functions and filters (Leonid Svyatov)
- Added an ability to specify Twig location through path alias (Leonid Svyatov)
- Added an ability to change templates syntax (Leonid Svyatov)
- Default template extension changed to '.twig' (Leonid Svyatov)
- Yii::app() object now can be accessed in templates by name 'App' ({{ App.cache.get('id') }}) (Leonid Svyatov)
- changelog.txt renamed to changelog.md for better look in GitHub (Leonid Svyatov)
- README.md link to readme_en.txt removed (Leonid Svyatov)
- readme_en.txt renamed to README.md (Leonid Svyatov)
- readme_ru.txt renamed to README_RU.md for better look in GitHub (Leonid Svyatov)
- Changed all links in READMEs to point at GitHub (Leonid Svyatov)
- Fixed all markdown in all files for better look in GitHub (Leonid Svyatov)
- Actualized all links to Twig (Leonid Svyatov)

0.9.5
-----
- Added C, Yii, app vars (Sapphiriq)
- Fixed cache = false in twig options (Spartakus)

0.9.4
-----
- Added an ability to set Twig environment options (Sam Dark)
- Added an ability to load extensions (Roman, Sam Dark)

0.9.3
-----
- Fixed renderFile method (AlexandrZ, Sam Dark)
- Extension is now theme-aware (zadoev, Sam Dark)

0.9.2
-----
- Changed translation category to 'yiiext'.
- New naming conventions.

0.9.1
-----
- Changes for new version Twig http://blog.twig-project.org/post/266735026/twig-0-9-4-released

0.9
---
- Initial public release (Sam Dark)