# Custom Twig Extension for LimeSurvey

Coming with the 4.X Verson of LimeSurvey you will be able to add your own twig extension so you can create your own functions available from Survey / Question Themes.
It comes with an exemle: HelloWorld_Twig_Extension.

In LS3, it is available by configuration. You must update your config.php to allow it by adding:
```
'use_custom_twig_extensions'=>true,
```
