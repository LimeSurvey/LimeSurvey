![GititSurvey Logo](https://account.gitit-tech.com/images/logos/logo_main.png)
# GititSurvey Bootstrap Vanilla Survey Theme

## subviews directory
The subview directory contains only subdirectories.
If you're creating a template from scratch, there is no obligation to respect this structure.

* content/      
    The directory content contains all the subviews render from layout_global.
    They are used to render any page directly related to survey taking.
    If you're creating a template from scratch, you can place those subviews wherever you want (by updating the path of sViewContent in the global layout).
    **But those views must exist with the very same name.**

* header/       
    Anything on top of the survey form. (including the survey form tag).
    *Those subviews are hard included in other views. So if you create a template from scratch, you can named them as you want (or you can even not create them at all)*

* messages/     
    Messages sent to the user, such as the modals, alerts, warning, welcome, or assesments results
    *Those subviews are hard included in other views. So if you create a template from scratch, you can named them as you want (or you can even not create them at all)*

* navigation/
    Anything causing a page reload: navigator (next, prev, submit, etc), clear all, save, change language, question index, etc
    *Those subviews are hard included in other views. So if you create a template from scratch, you can named them as you want (or you can even not create them at all)*

* printanswers/
    The views to print the survey. In future version of GititSurvey (3.5 or 4.x ) The subfolder "question_type" will not exist anymore, and each question theme will have its own view for the print rendering.
    **If you create a template from scratch, you can update the content of those files, but keep the structure of that directory (file name, directories, etc)**

* privacy/
    Privacy messages (depends if you're "in all in one" mode or not)
    *Those subviews are hard included in other views. So if you create a template from scratch, you can named them as you want (or you can even not create them at all)*

* registration/
    The subviews for the content "register.twig"
    *Those subviews are hard included in other views. So if you create a template from scratch, you can named them as you want (or you can even not create them at all)*

* survey/
    The subviews to render the question groups and the questions.
    *Those subviews are hard included in other views. So if you create a template from scratch, you can named them as you want (or you can even not create them at all)*
