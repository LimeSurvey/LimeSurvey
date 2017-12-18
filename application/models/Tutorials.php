<?php

/**
 * This is the model class for table "{{tutorials}}".
 *
 * The followings are the available columns in table '{{tutorials}}':
 * @property integer $tid
 * @property string $name
 * @property string $description
 * @property integer $active
 * @property string $permission
 * @property string $permission_grade
 *
 * The followings are the available model relations:
 * @property TutorialEntry[] $tutorialEntries
 */
class Tutorials extends LSActiveRecord
{
    private $preBuiltPackage = [];

    public function __construct()
    {
        $this->_generatePreBuiltPackage();
        parent::__construct();
    }

    private function _generatePreBuiltPackage()
    {
        $this->preBuiltPackage = array(
            'firstStartTour' => array(
                'name' => 'firstStartTour',
                'steps' => array(
                                array( //1
                                    'element' => '#lime-logo',
                                    'orphan' => true,
                                    'backdrop' => true,
                                    'path' => Yii::app()->createUrl('/admin/index'),
                                    'title' => gT('Welcome to LimeSurvey!'),
                                    'placement' => 'bottom',
                                    'content' => gT("This tour will help you to easily get a basic understanding of LimeSurvey.")."<br/>"
                                        .gt("We would like to help you with a quick tour of the most essential functions and features."),
                                    'redirect' => false,
                                    'onShow' => "(function(tour){ $('#welcomeModal').modal('hide'); })"
                                ),
                                array( //2
                                    'element' => '.selector__create_survey',
                                    'path' => Yii::app()->createUrl('/admin/index'),
                                    'backdrop' => true,
                                    'title' => gT('The basic functions'),
                                    'content' => gT("The three top boxes are the most basic functions of LimeSurvey.")."<br/>"
                                    .gT("From left to right it should be 'Create survey', 'List surveys' and 'Global settings'. Best we start by creating a survey.")
                                    .'<p class="alert bg-warning">'.gT("Click on the 'Create survey' box - or 'Next' in this tutorial").'</p>',
                                    'reflex' => true,
                                    'redirect' => true,
                                    'onShow' => "(function(tour){ $('#welcomeModal').modal('hide'); $('.selector__create_survey').on('click', function(){tour.next();});})"
                                ),
                                array( //3
                                    'element' => '#surveyls_title',
                                    'path' => Yii::app()->createUrl('/admin/survey/sa/newsurvey'),
                                    'title' => gT('The survey title'),
                                    'content' => gT("This is the title of your survey.")."<br/>"
                                        .gT("Your participants will see this title in the browser's title bar and on the welcome screen.")
                                        ."<p class='bg-warning alert'>".gT("You have to put in at least a title for the survey to be saved.").'</p>',
                                    'redirect' => true,
                                ),
                                array( //4
                                    'element' => '#cke_description',
                                    'path' => Yii::app()->createUrl('/admin/survey/sa/newsurvey'),
                                    'title' => gT('The survey description'),
                                    'placement' => 'top',
                                    'content' => gT("In this field you may type a short description of your survey.")."<br/>"
                                        .gT("The text inserted here will be displayed on the welcome screen, which is the first thing that your respondents will see when they access your survey..").' '
                                        .gT("Describe your survey, but do not ask any question yet."),
                                    'redirect' => false,
                                ),
                                array( //5
                                    'element' => '.bootstrap-switch-id-createsample',
                                    'path' => Yii::app()->createUrl('/admin/survey/sa/newsurvey'),
                                    'title' => gT('Create a sample question and question group'),
                                    'content' => gT("We will be creating a question group and a question in this tutorial. There is need to automatically create it."),
                                    'redirect' => false,
                                ),
                                array( //6
                                    'element' => '#cke_welcome',
                                    'path' => Yii::app()->createUrl('/admin/survey/sa/newsurvey'),
                                    'title' => gT('The welcome message'),
                                    'placement' => 'top',
                                    'content' => gT("This message is shown directly below the survey description on the welcome page. You may leave this blank for now but it is a good way to introduce your participants to the survey."),
                                    'redirect' => false,
                                ),
                                array( //7
                                    'element' => '#cke_endtext',
                                    'path' => Yii::app()->createUrl('/admin/survey/sa/newsurvey'),
                                    'title' => gT('The end message'),
                                    'placement' => 'top',
                                    'content' => gT("This message is shown at the end of your survey to every participant. It's a great way to say thank you or give some links or hints where to go next."),
                                    'redirect' => false,
                                ),
                                array( //8
                                    'element' => '#save-form-button',
                                    'path' => Yii::app()->createUrl('/admin/survey/sa/newsurvey'),
                                    'title' => gT('Now save your survey'),
                                    'placement' => 'bottom',
                                    'content' => gT("You may play around with more settings, but let's save and start adding questions to your survey now. Just click on 'Save'."),
                                    'reflex' => true,
                                    'redirect' => false,
                                    'onNext' => "(function(tour){
                                                    $('#save-form-button').trigger('click');
                                                    return Promise.resolve(tour);
                                                })",
                                ),
                                array( //9
                                    'element' => '#sidebar',
                                    'path' => [Yii::app()->createUrl('/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}'])],
                                    'placement' => 'right',
                                    'title' => gT('The sidebar'),
                                    'content' => gT('This is the sidebar.').'<br/>'
                                        .gT('All important settings can be reached in this sidebar.').'<br/>'
                                        .gT('The most important settings of your survey can be reached from this sidebar: the survey settings menu and the survey structure menu.').' '
                                        .gT('You may resize it to fit your screen to easily navigate through the available options.'
                                        .' If the size of the sidebar is too small, the options get collapsed and the quick-menu is displayed.'
                                        .' If you wish to work from the quick-menu, either click on the arrow button or drag it to the left.'),
                                    'redirect' => false,
                                    'onShow' => "(function(tour){
                                                    return Promise.resolve(tour);
                                                })"
                                ),
                                array( //10
                                    'element' => '#adminpanel__sidebar--selectorSettingsButton',
                                    'path' => [Yii::app()->createUrl('/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}'])],
                                    'placement' => 'bottom',
                                    'title' => gT('The settings tab with the survey menu'),
                                    'content' => gT('If you click on this tab, the survey settings menu will be displayed.').' '
                                    .gT('The most important settings of your survey are accessible from this menu.'). '<br/>'
                                    .gT('If you want to know more about them, check our manual.'),
                                    'redirect' => false,
                                ),
                                array( //11
                                    'element' => '#surveybarid',
                                    'path' => [Yii::app()->createUrl('/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}'])],
                                    'placement' => 'bottom',
                                    'title' => gT('The top bar'),
                                    'content' => gT('This is the top bar.').'<br/>'
                                        .gT('This bar will change as you move through the functionalities.').' '
                                        .gT('The current bar corresponds to the "overview" tab. It contains the most important LimeSurvey functionalities such as preview and activate survey.'),
                                    'redirect' => false,
                                ),
                                array( //12
                                    'element' => '#adminpanel__sidebar--selectorStructureButton',
                                    'path' => [Yii::app()->createUrl('/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}'])],
                                    'placement' => 'bottom',
                                    'title' => gT('The survey structure'),
                                    'content' => gT('This is the structure view of your survey. Here you can see all your question groups and questions.'),
                                    'redirect' => false,
                                    'onShow' => "(function(tour){
                                                    $('#adminpanel__sidebar--selectorStructureButton').trigger('click');
                                                    return Promise.resolve(tour);
                                                })",
                                ),
                                array( //13
                                    'element' => '#adminpanel__sidebar--selectorCreateQuestionGroup',
                                    'path' => [Yii::app()->createUrl('/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}'])],
                                    'placement' => 'right',
                                    'title' => gT("Let's add a question group"),
                                    'content' => gT("What good would your survey be without questions?").'<br/>'
                                        .gT('In LimeSurvey a survey is organized in question groups and questions. To begin creating questions, we first need a question group.')
                                        .'<p class="alert bg-warning">'.gT("Click on the 'Add question group' button").'</p>',
                                    'reflex' => true,
                                    'redirect' => false,
                                    'onNext' => "(function(tour){
                                                    document.location.href = $('#adminpanel__sidebar--selectorCreateQuestionGroup').attr('href');
                                                    return Promise.resolve(tour);
                                                })",
                                ),
                                array( //14
                                    'element' => '#group_name_en',
                                    'path' => [Yii::app()->createUrl('/admin/questiongroups/sa/add', ['surveyid' => '[0-9]{4,25}'])],
                                    'placement' => 'bottom',
                                    'title' => gT('Enter a title for your first question group'),
                                    'content' => gT('The title of the question group is visible to your survey participants (this setting can be changed later) and it cannot be empty.').' '
                                    .gT('Question groups are important because they allow the survey administrators to logically group the questions.'
                                    .' By default, each question group (including its questions) is shown on its own page (this setting can be changed later).'),
                                    'redirect' => false,
                                ),
                                array( //15
                                    'element' => 'label[for=description_en]',
                                    'path' => [Yii::app()->createUrl('/admin/questiongroups/sa/add', ['surveyid' => '[0-9]{4,25}'])],
                                    'placement' => 'top',
                                    'title' => gT('A description for your question group'),
                                    'content' => gT('This description is also visible to your participants.').'<br/>'
                                    .gT('You do not need to add a description to your question group, but sometimes it makes sense to add a little extra information for your participants.'),
                                    'redirect' => false,
                                ),
                                array( //16
                                    'element' => '#randomization_group',
                                    'path' => [Yii::app()->createUrl('/admin/questiongroups/sa/add', ['surveyid' => '[0-9]{4,25}'])],
                                    'placement' => 'left',
                                    'title' => gT('Advanced settings'),
                                    'content' => gT("For now it's best to leave these additional settings as they are. If you want to know more about randomization and relevance settings, have a look at our manual."),
                                    'redirect' => false,
                                ),
                                array( //17
                                    'element' => '#save-and-new-question-button',
                                    'path' => [Yii::app()->createUrl('/admin/questiongroups/sa/add', ['surveyid' => '[0-9]{4,25}'])],
                                    'placement' => 'bottom',
                                    'title' => gT('Save and add a new question'),
                                    'content' => gT("Now when you are finished click on 'Save and add question'.").'<br/>'
                                        .gT('This will directly add a question to the current question group.')
                                        .'<p class="alert bg-warning">'.gT("Now click on 'Save and add question'.").'</p>',
                                    'reflex' => true,
                                    'redirect' => false,
                                    'onNext' => "(function(tour){
                                                    $('#save-and-new-question-button').trigger('click');
                                                    return Promise.resolve(tour);
                                                })",
                                ),
                                array( //18
                                    'element' => '#title',
                                    'path' => [Yii::app()->createUrl('/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}', 'gid' => '[0-9]{1,25}', 'qid' => '[0-9]{4,25}'])],
                                    'placement' => 'top',
                                    'title' => gT('The title of your question'),
                                    'content' => 
                                        gT("This code is normally not shown to your participants, still it is necessary and has to be unique for the survey.").'<br>'
                                        .gT("This code is also the name of the variable that will be exported to SPSS or Excel.")
                                        .'<p class="alert bg-warning">'.gT("Please type in a code that consists only of letters and numbers, and doesn't start with a number.").'</p>',
                                    'redirect' => false,
                                ),
                                array( //19
                                    'element' => '#cke_question_en',
                                    'path' => [Yii::app()->createUrl('/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}', 'gid' => '[0-9]{1,25}', 'qid' => '[0-9]{4,25}'])],
                                    'placement' => 'top',
                                    'title' => gT('The actual question text'),
                                    'content' => gT('The content of this box is the actual question text shown to your participants.').' '
                                    .gT('It may be empty, but that is not recommended. You may use all the power of our WYSIWYG editor to make your question shine.'),
                                    'redirect' => false,
                                ),
                                array( //20
                                    'element' => '#cke_help_en',
                                    'path' => [Yii::app()->createUrl('/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}', 'gid' => '[0-9]{1,25}', 'qid' => '[0-9]{4,25}'])],
                                    'placement' => 'top',
                                    'title' => gT('An additional help text for your question'),
                                    'content' => gT('You can add some additional help text to your question.'
                                    .' If you decide not to offer any additional question hints, then no help text will be displayed to your respondents.'),
                                    'redirect' => false,
                                ),
                                array( //21
                                    'element' => '#question_type_button',
                                    'path' => [Yii::app()->createUrl('/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}', 'gid' => '[0-9]{1,25}', 'qid' => '[0-9]{4,25}'])],
                                    'placement' => 'left',
                                    'title' => gT('Set your question type.'),
                                    'content' => gT("LimeSurvey offers you a lot of different question types.").'<br/>'
                                        .gT("As you can see, the preselected question type is the 'Long free text' one. We will use in this example the 'Array' question type.").'<br/>'
                                        .gT("This type of question allows you to add multiple subquestions and a set of answers.")
                                        .'<p class="alert bg-warning">'.gT("Please select the 'Array'-type.").'</p>',
                                    'redirect' => false,
                                ),
                                array( //22
                                    'element' => '#save-button',
                                    'path' => [Yii::app()->createUrl('/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}', 'gid' => '[0-9]{1,25}', 'qid' => '[0-9]{4,25}'])],
                                    'placement' => 'left',
                                    'title' => gT('Now save the created question'),
                                    'content' => gT('Next, we will create subquestions and answer options.').'<br/>'
                                        .gT('Please remember that in order to have a valid code, it must contain only letters and numbers,'
                                        .' also please check that it starts with a letter.'),
                                    'reflex' => true,
                                    'redirect' => false,
                                    'onNext' => "(function(tour){
                                                    $('#question_type').val('F');
                                                    $('#save-button').trigger('click');
                                                    return Promise.resolve(tour);
                                                })",
                                ),
                                array( //23
                                    'element' => '#questionbarid',
                                    'path' => [Yii::app()->createUrl('/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}', 'gid' => '[0-9]{1,25}', 'qid' => '[0-9]{4,25}'])],
                                    'placement' => 'bottom',
                                    'title' => gT('The question bar'),
                                    'content' => gT('This is the question bar.').'<br/>'
                                    .gt('The most important question-related options are displayed here.').'<br/>'
                                    .gT('The availability of options is related to the type of question you previously chose.'),
                                    'redirect' => false,
                                ),
                                array( //24
                                    'element' => '#adminpanel__topbar--selectorAddSubquestions',
                                    'placement' => 'bottom',
                                    'path' => [Yii::app()->createUrl('/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}', 'gid' => '[0-9]{1,25}', 'qid' => '[0-9]{4,25}'])],
                                    'title' => gT('Add some subquestions to your question'),
                                    'content' => gT("The array question is a type that creates a matrix for the participant.").'<br/>'
                                        .gT("To fully use it, you have to add subquestions as well as answer options.").'<br/>'
                                        .gT("Let's start with subquestions.")
                                        .'<p class="alert bg-warning">'.gT("Click on the 'Edit subquestions' button.").'</p>',
                                    'reflex' => true,
                                    'redirect' => false,
                                    'onNext' => "(function(tour){
                                                    document.location.href = $('#adminpanel__topbar--selectorAddSubquestions').attr('href');
                                                    return Promise.resolve(tour);
                                                })",
                                ),
                                array( //25
                                    'element' => '#rowcontainer',
                                    'path' => [Yii::app()->createUrl('admin/questions/sa/subquestions/surveyid/[0-9]{4,25}/gid/[0-9]{1,25}/qid/[0-9]{4,25}')],
                                    'placement' => 'bottom',
                                    'title' => gT('Edit subquestions'),
                                    'content' => gT("You should add some subquestions for your question here.").'<br/>'
                                        .gT("Every row is one subquestion. We recommend the usage of logical or numerical codes for subquestions.").' '
                                        .gT("Your participants cannot see the subquestion code, only the subquestion text itself.")
                                        ."<p class='bg-info alert'>".gT("Pro tip: The subquestion may even contain HTML code.").'</p>',
                                    'redirect' => false,
                                ),
                                array( //26
                                    'element' => '#rowcontainer>tr:first-of-type .btnaddanswer',
                                    'path' => [Yii::app()->createUrl('admin/questions/sa/subquestions/surveyid/[0-9]{4,25}/gid/[0-9]{1,25}/qid/[0-9]{4,25}')],
                                    'placement' => 'left',
                                    'title' => gT('Add subquestion row'),
                                    'content' => sprintf(gT('Click on the plus sign %s to add another subquestion to your question.'), '<i class="icon-add text-success"></i>')
                                        ."<p class='bg-warning alert'>".gT('Please add at least two subquestions')."</p>",
                                    'redirect' => false,
                                ),
                                array( //27
                                    'element' => '#save-and-close-button',
                                    'path' => [Yii::app()->createUrl('admin/questions/sa/subquestions/surveyid/[0-9]{4,25}/gid/[0-9]{1,25}/qid/[0-9]{4,25}')],
                                    'placement' => 'left',
                                    'title' => gT('Now save the subquestions'),
                                    'content' => gT("You may save empty subquestions, but that would be pointless.")
                                        ."<p class='bg-warning alert'>".gT("Save and close now and let's edit the answer options.").'</p>',
                                    'reflex' => true,
                                    'redirect' => false,
                                    'onNext' => "(function(tour){
                                                    $('#save-and-close-button').trigger('click');
                                                    return Promise.resolve(tour);
                                                })"
                                ),
                                array( //28
                                    'element' => '#adminpanel__topbar--selectorAddAnswerOptions',
                                    'path' => [Yii::app()->createUrl('/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}', 'gid' => '[0-9]{1,25}', 'qid' => '[0-9]{4,25}'])],
                                    'placement' => 'bottom',
                                    'title' => gT('Add some answer options to your question'),
                                    'content' => gT("Now that we've got some subquestions, we have to add answer options as well.").'<br/>'
                                        .gT("The answer options will be shown for each subquestion.")
                                        .'<p class="alert bg-warning">'.gT("Click on the 'Edit answer options' button.").'</p>',
                                    'reflex' => true,
                                    'redirect' => false,
                                    'onNext' => "(function(tour){
                                                    document.location.href = $('#adminpanel__topbar--selectorAddAnswerOptions').attr('href');
                                                    return Promise.resolve(tour);
                                                })",
                                ),
                                array( //29
                                    'element' => '#rowcontainer',
                                    'path' => [Yii::app()->createUrl('admin/questions/sa/answeroptions/surveyid/[0-9]{4,25}/gid/[0-9]{1,25}/qid/[0-9]{4,25}')],
                                    'placement' => 'bottom',
                                    'title' => gT('Edit answer options'),
                                    'content' => gT("As you can see the editing of answer options and subquestions is really not much different.").'<br/>'
                                        .sprintf(gT('Rember the plus button %s ?'), '<i class="icon-add text-success"></i>').'<br/>'
                                        .'<p class="alert bg-warning">'.gT("Please add at least two answer options to proceed.").'</p>',
                                    'redirect' => false,
                                ),
                                array( //30
                                    'element' => '#save-and-close-button',
                                    'path' => [Yii::app()->createUrl('admin/questions/sa/answeroptions/surveyid/[0-9]{4,25}/gid/[0-9]{1,25}/qid/[0-9]{4,25}')],
                                    'placement' => 'left',
                                    'title' => gT('Now save the answer options'),
                                    'content' => gT("Click on 'Save and close' or 'Next' to proceed."),
                                    'reflex' => true,
                                    'redirect' => false,
                                    'onNext' => "(function(tour){
                                                    $('#save-and-close-button').trigger('click');
                                                    return Promise.resolve(tour);
                                                })"
                                ),
                                array( //31
                                    'element' => '.selector__topbar--previewSurvey',
                                    'path' => [Yii::app()->createUrl('/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}', 'gid' => '[0-9]{1,25}', 'qid' => '[0-9]{4,25}'])],
                                    'placement' => 'bottom',
                                    'title' => gT('Preview survey'),
                                    'content' => gT("Now is the time to preview your first survey.").'<br/>'
                                        .gT("Just click on this button and a new window will open, where you can test run your survey.").'<br/>'
                                        .gT("Please be aware that your answers will not be saved, because the survey isn't active yet.")
                                        .'<p class="alert bg-warning">'.gT("Click on 'Preview survey' and return to this window when you are done testing.").'</p>',
                                    'redirect' => false,
                                ),
                                array( //32
                                    'element' => '#breadcrumb-container',
                                    'path' => [Yii::app()->createUrl('/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}', 'gid' => '[0-9]{1,25}', 'qid' => '[0-9]{4,25}'])],
                                    'placement' => 'bottom',
                                    'title' => gT('Easy navigation with the "breadcrumbs"'),
                                    'content' => gT('In the top bar of the admin interface you see the "breadcrumbs".').'<br/>'
                                        .gT("These will always be an easy way to get back to any previous setting.")
                                        .'<p class="alert bg-warning">'.gT("Click on the name of your survey to get back to the survey settings overview.").'</p>',
                                    'reflex' => '#breadcrumb__survey--overview',
                                    'redirect' => false,
                                    'onNext' => "(function(tour){
                                                    document.location.href = $('#breadcrumb__survey--overview').attr('href');
                                                    return Promise.resolve(tour);
                                                })",
                                ),
                                array( //33
                                    'element' => '#ls-activate-survey',
                                    'path' => [Yii::app()->createUrl('/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}'])],
                                    'placement' => 'bottom',
                                    'title' => gT('Finally, activate your survey'),
                                    'content' => gT("Now activate this simple survey.").'<br/>'
                                        .gT("You can create as many surveys as you like.")
                                        .'<p class="alert bg-warning">'.gT("Click on 'Activate this survey'").'</p>',
                                    'reflex' => true,
                                    'redirect' => false,
                                    'onNext' => "(function(tour){
                                            document.location.href = $('#ls-activate-survey').attr('href');
                                            return Promise.resolve(tour);
                                        })",
                                ),
                                array( //34
                                    'element' => '#activateSurvey__basicSettings--proceed',
                                    'path' => [Yii::app()->createUrl('/admin/survey/sa/activate', ['surveyid' => '[0-9]{4,25}'])],
                                    'placement' => 'bottom',
                                    'title' => gT('Activation settings'),
                                    'content' => gT('These settings cannot be changed once the survey is online.').'<br/>'
                                        .gT("For this simple survey the default settings are ok, but read the disclaimer carefully when you activate your own surveys.").'<br/>'
                                        .gT("For more information consult our manual, or our forums.")
                                        .'<p class="alert bg-warning">'.gT('Now click on "Save & activate survey"').'</p>',
                                    'reflex' => true,
                                    'redirect' => false,
                                    'onNext' => "(function(tour){
                                            $('#activateSurvey__basicSettings--proceed').trigger('click');
                                            return Promise.resolve(tour);
                                        })",
                                ),
                                array( //35
                                    'element' => '#activateTokenTable__selector--no',
                                    'path' => [Yii::app()->createUrl('/admin/survey/sa/activate', ['surveyid' => '[0-9]{4,25}'])],
                                    'placement' => 'bottom',
                                    'title' => ('Activate token table'),
                                    'content' => gT("Here you can select to start your survey in closed access mode.")."<br/>"
                                        .gT("For our simple survey it is better to start in open access mode.")."<br/>"
                                        .gT("The closed access mode needs a participant list, which you may create by clicking on the menu entry 'Participants'.")."<br/>"
                                        .gT("For more information please consult our manual or our forum.")
                                        .'<p class="alert bg-warning">'.gT("Click on 'No, thanks'").'</p>',
                                    'reflex' => true,
                                    'redirect' => false,
                                    'onNext' => "(function(tour){
                                            $('#activateTokenTable__selector--no').trigger('click');
                                            return Promise.resolve(tour);
                                        })",
                                ),
                                array( //36
                                    'element' => '#adminpanel__surveysummary--mainLanguageLink',
                                    'path' => [Yii::app()->createUrl('/').'(index.php)?'],
                                    'placement' => 'top',
                                    'title' => gT('Share this link'),
                                    'content' => gT("Just share this link with some of your friends and of course, test it yourself.")
                                        .'<p class="alert bg-success lstutorial__typography--white">'.gT("Thank you for taking the tour!").'</p>',
                                    'redirect' => false
                                ),
                ),
                'debug' => true,
                'orphan' => true,
                'keyboard' => false,
                'template' => "<div class='popover tour lstutorial__template--mainContainer'> <div class='arrow'></div> <h3 class='popover-title lstutorial__template--title'></h3> <div class='popover-content lstutorial__template--content'></div> <div class='popover-navigation lstutorial__template--navigation'>     <div class='btn-group col-xs-8' role='group' aria-label='...'>         <button class='btn btn-default col-xs-6' data-role='prev'>".gT('Previous')."</button>         <button class='btn btn-primary col-xs-6' data-role='next'>".gT('Next')."</button>     </div>     <div class='col-xs-4'>         <button class='btn btn-warning' data-role='end'>".gT('End tour')."</button>     </div> </div></div>",
                'onShown' => "(function(tour){ console.log($('#notif-container').children()); $('#notif-container').children().remove(); })"
            )
        );
    }

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{tutorials}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('name, description, active, permission, permission_grade', 'required'),
            array('active', 'numerical', 'integerOnly'=>true),
            array('name, permission, permission_grade', 'length', 'max'=>128),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('tid, name, description, active, permission, permission_grade', 'safe', 'on'=>'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'tutorialEntries' => array(self::HAS_MANY, 'TutorialEntry', 'tid'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'tid' => 'Tid',
            'name' => 'Name',
            'description' => 'Description',
            'active' => 'Active',
            'permission' => 'Permission',
            'permission_grade' => 'Permission Grade',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * Typical usecase:
     * - Initialize the model fields with values from filter form.
     * - Execute this method to get CActiveDataProvider instance which will filter
     * models according to data in model fields.
     * - Pass data provider to CGridView, CListView or any similar widget.
     *
     * @return CActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
    public function search()
    {
        // @todo Please modify the following code to remove attributes that should not be searched.

        $criteria = new CDbCriteria;

        $criteria->compare('tid', $this->tid);
        $criteria->compare('name', $this->name, true);
        $criteria->compare('description', $this->description, true);
        $criteria->compare('active', $this->active);
        $criteria->compare('permission', $this->permission, true);
        $criteria->compare('permission_grade', $this->permission_grade, true);

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }

    public function getPrebuilt($prebuiltName)
    {
        if (isset($this->preBuiltPackage[$prebuiltName])) {
            return $this->preBuiltPackage[$prebuiltName];
        }
        return [];
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Tutorials the static model class
     */
    public static function model($className = __CLASS__)
    {
        /** @var Tutorials $model */
        $model = parent::model($className);
        return $model;
    }
}
