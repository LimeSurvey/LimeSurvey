![LimeSurvey Logo](https://www.limesurvey.org/images/logos/logo_main.png)
# LimeSurvey Bootstrap Vanilla Survey Theme

## Survey directory
The subviews to render the question groups and the questions.

group.twig is the main view here. It's rendered from content/main.twig, and then via a each loop it renders each relevant group and question.
We tried to atomize it as much as possible, so if a user update one of those views via the template manager, only a small amount of HTML will be loaded from local template. 
