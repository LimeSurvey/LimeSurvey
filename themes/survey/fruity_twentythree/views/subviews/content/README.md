![LimeSurvey Logo](https://account.limesurvey.org/images/logos/logo_main.png)
# LimeSurvey Bootstrap Vanilla Survey Theme

## content directory


layout_global.twig renders the specific content via this statement:
```
{% set sViewContent =  './subviews/content/' ~ aSurveyInfo.include_content ~ '.twig'%}
{% include './subviews/content/outerframe.twig' with {'include_content': sViewContent } %}
```

see: https://github.com/LimeSurvey/LimeSurvey/blob/7ffc17fbb872791a9ba1a6b6ab68cec0263f3eca/themes/survey/vanilla/views/layout_global.twig#L103-L111

Here are the subviews to render the content. Most of them corresponds to the old pstpl files for limesurvey 2.x

* mainrow.twig
* submit_preview.twig  
* quotas.twig
* survey_list.twig
* clearall.twig
* register.twig
* firstpage.twig
* load.twig
* outerframe.twig
* submit.twig
* save.twig
* main.twig


If you're creating a template from scratch, you're free to place the content views where ever you want. Just update the definition of sViewContent in layout_global.
**But you must keep the filename as they are.**

Please, contact LimeSurvey team if you think a file name should be changed.
