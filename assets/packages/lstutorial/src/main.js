import Tour from 'bootstrap-tour';

const firstStartTour = new Tour({
    steps: [
        {
            orphan: true,
            title: 'Welcome to LimeSurvey!',
            content: `
            <p>This tour will help you get a hold of LimeSurvey.</p>
            <p>We know that LimeSurvey may be hard to use at the beginning, that's why we would like to help with a quick tour through the most essential functions and features</p>
            `,
            backdrop: true
        },
        {
            element: '.selector__lstour--mainfunctionboxes',
            title: 'The basic functions',
            content: `The three top boxes are the most basic functions of LimeSurvey.
            From left to right it should be "Create survey", "List surveys" and "Global settings"
            At best we start by creating a survey.
            Click on Create survey or Next in this box`,
            reflex: '.selector__lstour--createsurvey',
            redirect: (/\/index\.php(\/)?\?r=admin/.test(window.location.href) ? '/index.php?r=admin/survey?sa=newsurvey' : '/index.php/admin/survey/sa/newsurvey'),
            backdrop: false
        },
        {
            element: '#surveyls_title',
            title: 'The survey title',
            content: `This is the title of your survey.
            Your participants will see this title as well in the browsers titlebar, als also on the welcome screen.
            <p class="bg-info">Tip: Make your surveys shine with a meaningful title</p>`
        },
        {
            element: '#cke_description',
            title: 'The survey description',
            content: `This is the description of the survey.
            Your participants will see this at first on their welcome screen.
            Try to describe what your survey is about, but don't ask any qustion, yet.`
        },
        {
            element: '.bootstrap-switch-id-createsample',
            title: 'Create a sample question and question group',
            content: `Since we are just beginning it is a good practice to let the wizard create a sample question and questiongroup for you.
            If you like you can do this yourself, too`
        },
        {
            element: '#cke_welcome',
            title: 'The welcome message',
            content: `This message is shown directly under the survey description on the welcome page.
            You may leave this blank and concentrate on a good text for your description, or vice versa.`
        },
        {
            element: '#cke_endtext',
            title: 'The end message',
            content: `This message is shown at the end of your survey to every participant.
            It's a great way to say thank you, or to give some links or hints where to go next.`
        },
        {
            element: '#save-form-button',
            title: 'Thats it for now',
            content: `You may play around with more settings, or just get to editing your survey now.
            Just click on save.`
        }
    ],
});

export const tourLibrary = {
    firstStartTour() {
        firstStartTour.init();
        firstStartTour.start();
    }
};
