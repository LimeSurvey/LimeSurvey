![LimeSurvey Logo](https://account.limesurvey.org/images/logos/logo_main.png)
# LimeSurvey Bootstrap Vanilla Survey Theme

## Views directory
The view directory contains all the necessary views to render the frontend.

* layout_global.twig: render the pages for survey taking
* layout_user_forms.twig: renders the user forms such as: token (survey participant), and register.
* layout_survey_list.twig: render the survey list (if this theme is set as default)
* layout_errors.twig: used to render errors that block survey rendering. ( wrong survey id, empty group in preview group, etc.)


Each one of the layout can have a complete different look & feel from the other layouts.
In vanilla, layout_user_forms and layout_global are pretty similar, and share some subviews: this is not an obligation at all.

## Content system
layout_global.twig renders the specific content via this statement:
```
{% set sViewContent =  './subviews/content/' ~ aSurveyInfo.include_content ~ '.twig' %}
{% include './subviews/content/outerframe.twig' with {'include_content': sViewContent } %}
```

see: https://github.com/LimeSurvey/LimeSurvey/blob/7ffc17fbb872791a9ba1a6b6ab68cec0263f3eca/themes/survey/vanilla/views/layout_global.twig#L103-L111

So, if you're creating a template from scratch, you're free to place the content views where ever you want. Just update the definition of sViewContent in layout_global
