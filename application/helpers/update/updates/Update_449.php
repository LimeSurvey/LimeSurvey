
            //updating the default values for htmleditor
            //surveys_groupsettings htmlemail should be 'Y'
            alterColumn('{{surveys_groupsettings}}', 'htmlemail', 'string(1)', false, 'Y');
            alterColumn('{{surveys}}', 'htmlemail', 'string(1)', false, 'Y');

