![LimeSurvey Logo](https://www.limesurvey.org/images/logos/logo_main.png)
# LimeSurvey Bootstrap Vanilla Survey Theme

## Views directory
The view directory contains all the necessary views to render the frontend.

* layout_global.twig: render the pages for survey taking
* layout_user_forms.twig: renders the user forms such as: token (survey participant), and register.
* layout_errors.twig: used to render errors that block survey rendering. ( wrong survey id, empty group in preview group, etc.)

Each one of the layout can have a complete different look & feel from the other layouts.
In vanilla, layout_user_forms and layout_global are pretty similar, and share some subviews: this is not an obligation at all.

## Content system
layout_global.twig renders the specific content via this statement:
{% include './subviews/content/outerframe.twig' with {'include_content': aSurveyInfo.include_content} %}

see: https://github.com/LimeSurvey/LimeSurvey/blob/1186f6f331b12cf52643e7b79196d7dce673ff4a/themes/survey/vanilla/views/layout_global.twig#L103-L110
