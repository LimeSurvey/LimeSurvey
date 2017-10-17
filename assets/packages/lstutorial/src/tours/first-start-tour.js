
const firstStartTour = {
    name: 'first-start-tour',
    steps: [
        {
            //container : '#in_survey_common_action',
            title: 'Welcome to LimeSurvey!',
            content: `
            <p>This tour will help you get a hold of LimeSurvey.</p>
            <p>We know that LimeSurvey may be hard to use at the beginning, that's why we would like to help with a quick tour through the most essential functions and features</p>
            `,
            backdrop: true,
            element: '#lime-logo',
        },
        {
            element: '.selector__lstour--mainfunctionboxes',
            title: 'The basic functions',
            content: `The three top boxes are the most basic functions of LimeSurvey.
            <p>From left to right it should be "Create survey", "List surveys" and "Global settings"
            At best we start by creating a survey.</p>
            <p>Click on Create survey or Next in this box</p>`,
            reflex: '.selector__lstour--createsurvey',
            backdrop: false,
        },
        {
            element: '#surveyls_title',
            title: 'The survey title',
            content: `This is the title of your survey.
            <p>Your participants will see this title as well in the browsers titlebar, als also on the welcome screen.</p>
            <p>You have to put in at least a title for the survey to be saved.</p>
            <p class="bg-info alert">=> Tip: Make your surveys shine with a meaningful title</p>`,
            path: (/\/index\.php(\/)\?\?r=admin/.test(window.location.href) ? '/index.php?r=admin/survey&sa=newsurvey' : '/index.php/admin/survey/sa/newsurvey'),
            redirect: true
        },
        {
            element: '#cke_description',
            title: 'The survey description',
            placement: 'top',
            content: `<p>This is the description of the survey.</p>
            <p>Your participants will see this at first on their welcome screen.
            Try to describe what your survey is about, but don't ask any qustion, yet.</p>`
        },
        {
            element: '.bootstrap-switch-id-createsample',
            title: 'Create a sample question and question group',
            content: 'Since we are just beginning it is a good practice to let the wizard create a sample question and questiongroup for you.'
        },
        {
            element: '#cke_welcome',
            title: 'The welcome message',
            placement: 'top',
            content: `<p>This message is shown directly under the survey description on the welcome page.
            You may leave this blank and concentrate on a good text for your description, or vice versa.</p>`
        },
        {
            element: '#cke_endtext',
            title: 'The end message',
            placement: 'top',
            content: `<p>This message is shown at the end of your survey to every participant.
            It's a great way to say thank you, or to give some links or hints where to go next.</p>`
        },
        {
            element: '#save-form-button',
            title: 'Thats it for now',
            placement: 'bottom',
            content: `<p>You may play around with more settings, or just get to editing your survey now.
            Just click on save.</p>`,
            reflex: '#save-form-button',
            onNext(){ $('#save-form-button').trigger('click'); return (new jQuery.Deferred()).promise(); }
        },
        {
            element: '#sidebar',
            backdrop: true,
            placement: 'right',
            title: 'The sidebar',
            content: `<p>This is the sidebar.</p>
            <p>All important settings can be reached in this sidebar.</p>
            <p>You may resize it to fit on your screen, or make it bigger to better control your surveytructure.
            It may be collapsed to show the quick-menu. To collapse it either click on the arrow button or resize it to the left.</p>`
        },
        {
            element: '#adminpanel__sidebar--selectorSettingsButton',
            backdrop: true,
            placement: 'top',
            title: 'The settings tab with the surveymenu',
            content: `<p>This tab shows the surveysettings.</p>
            <p>Any setting to your survey can be reached through this menu.
            If you want to know more about the settings, have a look at our manual.</p>`
        },
        {
            element: '#surveybarid',
            backdrop: true,
            placement: 'right',
            title: 'The top-bar',
            content: `<p>This is the top bar.</p>
            <p>This bar will change, as you move through the functionalities. 
            In this view it contains the most important LimeSurvey functionalities, like activating and previewing the survey</p>
            <p>An advanced user may even create custom menues and entries here.</p>`
        },
        {
            element: '#adminpanel__sidebar--selectorStructureButton',
            backdrop: true,
            placement: 'top',
            title: 'The surveystructure',
            content: `<p>This is the structure view of your survey.</p>
            <p>Here you can see all your questiongroups and questions.</p>
            <p>Click on the first question in the first questiongroup.</p>`,
            reflex: '#questionexplorer>ul.list-group>li:first-of-type>ul>li:first-of-type>a'
        },
        {
            element: '#questionbarid',
            backdrop: true,
            placement: 'bottom',
            title: 'The question bar',
            content: `<p>This is the question bar.</p>
            <p> The most important option here is the edit button. Also important are the preview buttons, which we will show in on of the next steps.</p>`
        },
        {
            element: '#adminpanel__sidebar--selectorCreateQuestionGroup',
            placement: 'right',
            title: 'Let\'s add another questiongroup',
            content: '<p>Click on the add questiongroup button</p>'
        },
        {
            element: '#group_name_en',
            placement: 'bottom',
            title: 'Put in the title to your question group',
            content: `<p>The title will be visible to your participants and cannot be empty</p>
            <p>Questiongroups are important to logically divie your questions, also in the default setting your survey is shown questiongroupwise.</p>`
        },
        {
            element: '#description_en',
            placement: 'top',
            title: 'A description for your questiongroup',
            content: `<p>This description is also visible to your participants.</p>
            <p>You do not need to add a description to your questiongroup, but sometimes it makes sense to add a little extra information for your participants.</p>`
        },
        {
            element: '#randomization_group',
            placement: 'left',
            title: 'Advanced settings',
            content: `<p>Best to leave them like they are.</p>
            <p>If you want to know more about randomization have a look at our manual.</p>`
        },


    ],
    debug: true,
    container: '#in_survey_common_action'
};

export default firstStartTour;
