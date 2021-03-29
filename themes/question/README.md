# Question templates for LimeSurvey

Coming with the 3.X Verson of LimeSurvey you will be able to create your 
own set of question types and alternative views for questions.

All you need to know is a little HTML and XML.
(And for the more advanced stuff also JavaScript and CSS would help.)

## The config.xml

XML config files are very common, a.e. Joomla. The config file for the question-template should contain a few basic things.

### Metadata - Or, what is this about and who has written it?

Your question view or type should have your name and your email on it, so people can ask questions and congratulate you to 
your awesome question type

Also you should give some information about the used licence (remember LimeSurvey is GPL) and a short description.

It makes a lot of sense to write which version of the view it is, to let people know If they would have to update.

Also, there is a difference between `name` and `title` tags.
`name` tag is used to enter a question theme code, without spaces.
`title` tag is used to enter a nice title which would be visible on Question theme dropdown on edit question page.
Both tags are required.

The `metadata` part should therefore look something like this:

```xml
    <metadata>
        <name>MyAwesomeQuestionView</name> 
        <title>My Awesome Question View</title> 
        <creationDate>23/12/2016</creationDate>
        <author>LimeSurvey Programmer</author>
        <authorEmail>info@limesurvey.org</authorEmail>
        <authorUrl>http://www.limesurvey.org</authorUrl>
        <copyright>Copyright (C) 2005 - 2016 LimeSurvey Gmbh, Inc. All rights reserved.</copyright>
        <license>GNU General Public License version 2 or later</license>
        <version>1.0</version>
        <apiVersion>1</apiVersion>
        <description>Everything will be better with this question type</description>
    </metadata>
```

### Files - Or, do we need some other stuff?

You can add additional files to the question view. 

`preview` tag is used to show a preview image for question type on edit question page right side accordion. If this tag is missing or empty, default question type preview image would be used.

Please make sure, that you put your own files in an `asset` folder in the base folder of your question view.

The `files` part should look something like this:
```xml
<files>
    <css>
        <filename>css/mycss.css</filename>
    </css>
    <js>
        <filename>scripts/myscript.js</filename>
    </js>
    <preview>
        <filename>question_theme_preview.png</filename>
    </preview>
</files>
```

### Custom Attributes - Or, I want it my way.

You can add your own attributes.

These will be visible and editable in the question edit view in the backend.

So if you would like to add some more power to your question view, like a unified greeting message over every question of this type.
Or fixed width and height of images in the question. Anything you can think of.

You can have as many extra attributes as you want. But be careful not to flood the question edit view with a million new attributes.

`category` tag is used to categorize attributes into different categories on edit question page right side accordion.

Existing attribute can be removed from edit question page, if `inputtype` tag is left empty (like ```xml <inputtype></inputtype> ```). 
This is just a temporary way to hide attributes, a proper way to hide attribute would be created soon. 

For a full list of possible input types please have a look at the questionHelper in application/helpers.

A full list is coming someday.

The `attributes` part should look something like this:
```xml
<attributes>
    <attribute>
        <name>myCustomAttribute</name>
        <category>Display</category>
        <sortorder>90</sortorder>
        <inputtype>text</inputtype>
        <default>defaulttext</default>
        <help>Describing what this custom attribute will do.</help>
        <caption>My custom Attribute: </caption>
        <i18n>en</i18n>
    </attribute>
</attributes>
```

### Engine - Or, Somehow the system has to know about this.

Last but not least you have to tell LimeSurvey where to put and what to do with your question view/type.
And if your extra css/js should be loaded.

You can choose to make it visible as well as a question template as a new question type.

This is rather important, because you won't be able to use your question template if you do not make it visible.

The `engine` part should look like this:
```xml
    <engine>
        <load_core_css>true</load_core_css>
        <load_core_js>true</load_core_js>
        <show_as_template>true</show_as_template>
        <show_as_question_type>true</show_as_question_type>
    </engine>
```

### A working example - Or, just that.

Here is a complete example of a config.xml file for your own question view.

Just take this as a base and build on top of it.

```xml
<?xml version="1.0" encoding="UTF-8"?>
<config>

    <metadata>
        <name>MyAwesomeQuestionView</name>  
        <title>My Awesome Question View</title>
        <creationDate>23/12/2016</creationDate>
        <author>LimeSurvey Programmer</author>
        <authorEmail>info@limesurvey.org</authorEmail>
        <authorUrl>http://www.limesurvey.org</authorUrl>
        <copyright>Copyright (C) 2005 - 2016 LimeSurvey Gmbh, Inc. All rights reserved.</copyright>
        <license>GNU General Public License version 2 or later</license>
        <version>1.0</version>
        <apiVersion>1</apiVersion>
        <description>Everything will be better with this question type</description>
    </metadata>

    <files>
        <css>
            <filename>css/mycss.css</filename>
        </css>
        <js>
            <filename>scripts/myscript.js</filename>
        </js>
        <preview>
            <filename>question_theme_preview.png</filename>
        </preview>
    </files>

    <attributes>
        <attribute>
            <name>customAttribute1</name>
            <category>My attributes</category>
            <sortorder>1</sortorder>
            <inputtype>text</inputtype>
            <default>defaulttext</default>
            <help>Describing what this custom attribute will do.</help>
            <caption>My custom Attribute 1: </caption>
            <i18n>en</i18n>
        </attribute>
        <attribute>
            <name>customAttribute2</name>
            <category>My attributes</category>
            <sortorder>2</sortorder>
            <inputtype>text</inputtype>
            <default>defaulttext</default>
            <help>Describing what this custom attribute will do.</help>
            <caption>My custom Attribute 2: </caption>
            <i18n>en</i18n>
        </attribute>
    </attributes>

    <engine>
        <load_core_css>true</load_core_css>
        <load_core_js>true</load_core_js>
        <show_as_template>true</show_as_template>
        <show_as_question_type>true</show_as_question_type>
    </engine>
</config>
```

## The folder structure

To be able to work the question template needs a dedicated structure.

It is rather important, because otherwise the framework would not be able to get the files from 
the correct location and that would lead to massive errors.

So here is an example structure for working on top of a multiplechoice question:

```tree
upload/
└── question_templates/
    └── my_awesome_template
        └── survey
            └── questions
                └── answer
                    └── multiplechoice
                        ├── answer.php
                        ├── assets
                        │   ├── css
                        │   │   └── my_awesome_template.css
                        │   ├── scripts
                        │   |   └── my_awesome_template.js
                        |   └── question_theme_preview.png
                        ├── columns
                        │   ├── column_footer.php
                        │   └── column_header.php
                        ├── rows
                        │   ├── answer_row_other.php
                        │   └── answer_row.twig
                        └── config.xml

``` 
