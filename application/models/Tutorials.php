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

    public function __construct(){
        $this->_generatePreBuiltPackage();
        parent::__construct();
    }

    private function _generatePreBuiltPackage (){
        $this->preBuiltPackage =  array(
            'firstStartTour' => array(
                'name' => 'firstStartTour',
                'steps' =>
                array(
                array( //1
                    'element' => '#lime-logo',
                    'orphan' => true,
                    'backdrop' => true,
                    'path' => Yii::app()->createUrl('/admin/index'),
                    'title' => gT('Welcome to LimeSurvey!'),
                    'placement' => 'bottom',
                    'content' => sprintf(
                        gT("This tour will help you get a hold of LimeSurvey.%s
                        We would like to help you with a quick tour of the most essential functions and features"),
                        '<br/>'
                    ),
                    'redirect' => false,
                ),
                array( //2
                    'element' => '.selector__lstour--mainfunctionboxes',
                    'path' => Yii::app()->createUrl('/admin/index'),
                    'title' => gT('The basic functions'),
                    'content' => sprintf(
                        gT("The three top boxes are the most basic functions of LimeSurvey. %s
                        From left to right it should be 'Create survey', 'List surveys' and 'Global settings'. Best we start by creating a survey.
                        %sClick on Create survey or Next in this box.%s"),
                        '<br/>','<p class="alert bg-warning">','</p>'
                    ),
                    'reflex' => '.selector__lstour--createsurvey',
                    'redirect' => false,
                ),
                array( //3
                    'element' => '#surveyls_title',
                    'path' => Yii::app()->createUrl('/admin/survey/sa/newsurvey'),
                    'title' => gT('The survey title'),
                    'content' => sprintf(
                        gT("This is the title of your survey. %s
                        Your participants will see this title as well in the browser's title bar, as also on the welcome screen.
                        %sYou have to put in at least a title for the survey to be saved.%s"),
                        '<br/>',"<p class='bg-warning alert'>",'</p>'
                    ),
                    'redirect' => true,
                ),
                array( //4
                    'element' => '#cke_description',
                    'path' => Yii::app()->createUrl('/admin/survey/sa/newsurvey'),
                    'title' => gT('The survey description'),
                    'placement' => 'top',
                    'content' => sprintf(
                        gT("This is the description of the survey.%s
                        Your participants will see this at first on their welcome screen.
                        Try to describe what your survey is about, but don't ask any question just yet."),
                        '<br/>'
                    ),
                    'redirect' => false,
                ),
                array( //5
                    'element' => '.bootstrap-switch-id-createsample',
                    'path' => Yii::app()->createUrl('/admin/survey/sa/newsurvey'),
                    'title' => gT('Create a sample question and question group'),
                    'content' => gT("In this tutorial we will be creating a question group and a question, so no need to automatically create it."),
                    'redirect' => false,
                ),
                array( //6
                    'element' => '#cke_welcome',
                    'path' => Yii::app()->createUrl('/admin/survey/sa/newsurvey'),
                    'title' => gT('The welcome message'),
                    'placement' => 'top',
                    'content' => gT("This message is shown directly under the survey description on the welcome page.
                    You may leave this blank and concentrate on a good text for your description, or vice versa."),
                    'redirect' => false,
                ),
                array( //7
                    'element' => '#cke_endtext',
                    'path' => Yii::app()->createUrl('/admin/survey/sa/newsurvey'),
                    'title' => gT('The end message'),
                    'placement' => 'top',
                    'content' => gT("This message is shown at the end of your survey to every participant.
                    It's a great way to say thank you or give some links or hints where to go next."),
                    'redirect' => false,
                ),
                array( //8
                    'element' => '#save-form-button',
                    'path' => Yii::app()->createUrl('/admin/survey/sa/newsurvey'),
                    'title' => gT('Now save sour survey'),
                    'placement' => 'bottom',
                    'content' => gT('You may play around with more settings, or edit your survey now. Just click on save.'),
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
                    'content' => sprintf(
                        gT('This is the sidebar.%s
                        All important settings can be reached in this sidebar.%s
                        You may resize it to fit your screen, or largen it to better control your survey structure.
                        It may be collapsed to show the quick-menu.
                        To collapse it either click on the arrow button or resize it to the left.'),
                        '<br/>','<br/>'
                    ),
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
                    'content' => gT('This tab shows the survey settings.
                    Any setting to your survey is accessible in this menu.
                    If you want to know more about the settings, have a look at our manual.'),
                    'redirect' => false,
                ),
                array( //11
                    'element' => '#surveybarid',
                    'path' => [Yii::app()->createUrl('/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}'])],
                    'placement' => 'bottom',
                    'title' => gT('The top bar'),
                    'content' => sprintf(
                        gT('This is the top bar.
                        This bar will change as you move through the functionalities.
                        In this view it contains the most important LimeSurvey functionalities like activating and previewing the survey'),
                        '<br/>'
                    ),
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
                    'title' => gT("Let's add another question group"),
                    'content' => sprintf(
                        gT("What good would your survey be without questions?%s
                        In LimeSurvey a survey is organized in question groups and questions. To begin creating questions we first need a question group.
                        %sClick on the 'Add questiongroup' button%s"),
                        '<br/>','<p class="alert bg-warning">','</p>'
                    ),
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
                    'title' => gT('Add the title to your question group'),
                    'content' => gT('The title will be visible to your participants and cannot be empty.
                    Question groups are important to logically divide your questions, also in the default setting your survey is shown question group-wise.'),
                    'redirect' => false,
                ),
                array( //15
                    'element' => 'label[for=description_en]',
                    'path' => [Yii::app()->createUrl('/admin/questiongroups/sa/add', ['surveyid' => '[0-9]{4,25}'])],
                    'placement' => 'top',
                    'title' => gT('A description for your question group'),
                    'content' => gT('This description is also visible to your participants.
                    You do not need to add a description to your question group, but sometimes it makes sense to add a little extra information for your participants.'),
                    'redirect' => false,
                ),
                array( //16
                    'element' => '#randomization_group',
                    'path' => [Yii::app()->createUrl('/admin/questiongroups/sa/add', ['surveyid' => '[0-9]{4,25}'])],
                    'placement' => 'left',
                    'title' => gT('Advanced settings'),
                    'content' => gT('Best to leave them like they are.
                    If you want to know more about randomization and relevance settings, have a look at our manual.'),
                    'redirect' => false,
                ),
                array( //17
                    'element' => '#save-and-new-question-button',
                    'path' => [Yii::app()->createUrl('/admin/questiongroups/sa/add', ['surveyid' => '[0-9]{4,25}'])],
                    'placement' => 'bottom',
                    'title' => gT('Save and add a new question'),
                    'content' => sprintf(
                        gT("Now when you are finished click on 'Save and add question'.%s
                        This will directly add a question to the current question group.
                        %sNow click on Save and add a new question%s"),
                        '<br/>','<p class="alert bg-warning">','</p>'
                    ),
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
                    'content' => sprintf(
                        gT('This code is normally not shown to your participants, still it is necessary and has to be unique for the survey.%s
                        This code is also the name of the variable that will be exported to SPSS or Excel.
                        %sPlease type in a code that consists only of letters and numbers, and doesn\'t start with a number.%s'),
                        '<br/>','<p class="alert bg-warning">','</p>'
                    ),
                    'redirect' => false,
                ),
                array( //19
                    'element' => '#cke_question_en',
                    'path' => [Yii::app()->createUrl('/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}', 'gid' => '[0-9]{1,25}', 'qid' => '[0-9]{4,25}'])],
                    'placement' => 'top',
                    'title' => gT('The actual question text'),
                    'content' => gT('The content of this box is the actual question text shown to your participants.
                    It may be empty, but that is not recommended. You may use all the power of our WYSIWYG editor to make your question shine.'),
                    'redirect' => false,
                ),
                array( //20
                    'element' => '#cke_help_en',
                    'path' => [Yii::app()->createUrl('/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}', 'gid' => '[0-9]{1,25}', 'qid' => '[0-9]{4,25}'])],
                    'placement' => 'top',
                    'title' => gT('An additional help text for your question'),
                    'content' => gT('You can add some additional help text to your question.
                    This may also be empty, then it will not be shown.'),
                    'redirect' => false,
                ),
                array( //21
                    'element' => '#questionTypeContainer',
                    'path' => [Yii::app()->createUrl('/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}', 'gid' => '[0-9]{1,25}', 'qid' => '[0-9]{4,25}'])],
                    'placement' => 'left',
                    'title' => gT('Set your question type.'),
                    'content' => sprintf(
                        gT("LimeSurvey offers you a lot of different question types.%s
                        The example question created for you as well as the default setting is the'Long free text'-type.%s
                        This type will create a big text input for your participants.
                        %sPlease select the 'Array'-type.%s"),
                        '<br/>','<br/>','<p class="alert bg-warning">','</p>'
                    ),
                    'redirect' => false,
                ),
                array( //22
                    'element' => '#save-button',
                    'path' => [Yii::app()->createUrl('/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}', 'gid' => '[0-9]{1,25}', 'qid' => '[0-9]{4,25}'])],
                    'placement' => 'left',
                    'title' => gT('Now save the created question'),
                    'content' => sprintf(
                        gT('Next we will create subquestions and answer options.%s
                        Please be sure the question has a legal title with only letters and numbers starting with a letter.'),
                        '<br/>'
                    ),
                    'reflex' => true,
                    'redirect' => false,
                    'onNext' => "(function(tour){
                                    $('#save-button').trigger('click');
                                    return Promise.resolve(tour);
                                })",
                ),
                array( //23
                    'element' => '#questionbarid',
                    'path' => [Yii::app()->createUrl('/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}', 'gid' => '[0-9]{1,25}', 'qid' => '[0-9]{4,25}'])],
                    'placement' => 'bottom',
                    'title' => gT('The question bar'),
                    'content' => gT('This is the question bar.
                    The most important option here is the edit button.
                    Also important are the preview buttons, which we will show in one of the next steps.'),
                    'redirect' => false,
                ),
                array( //24
                    'element' => '#adminpanel__topbar--selectorAddSubquestions',
                    'placement' => 'bottom',
                    'path' => [Yii::app()->createUrl('/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}', 'gid' => '[0-9]{1,25}', 'qid' => '[0-9]{4,25}'])],
                    'title' => gT('Add some subquestions to your question'),
                    'content' => sprintf(
                        gT("The array question is a type that creates a matrix for the participant.%s
                        To fully use it you have to add subquestions as well as answer options.%s
                        Let's start with subquestions.
                        %sClick on the 'Edit subquestions' button.%s"),
                        '<br/>','<br/>','<p class="alert bg-warning">','</p>'
                    ),
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
                    'content' => sprintf(
                        gT("Here you may add some subquestions for your question.%s
                        Every row is one subquestion. It's best practice to use logical or numerical codes for the subquestions.
                        Your participants cannot see the subquestion code, only the subquestion text itself.
                        %sPro tip: the subquestion may contain HTML code if you are logged in with admin mode.%s"),
                        "<br/>","<p class='bg-info alert'>","</p>"
                    ),
                    'redirect' => false,
                ),
                array( //26
                    'element' => '#rowcontainer>tr:first-of-type .btnaddanswer',
                    'path' => [Yii::app()->createUrl('admin/questions/sa/subquestions/surveyid/[0-9]{4,25}/gid/[0-9]{1,25}/qid/[0-9]{4,25}')],
                    'placement' => 'left',
                    'title' => gT('Add subquestion row'),
                    'content' => sprintf(
                        gT('Click on the plus sign to add another subquestion to your question.
                        %sPlease add at least two subquestions%s'),
                        "<p class='bg-warning alert'>","</p>"
                    ),
                    'redirect' => false,
                ),
                array( //27
                    'element' => '#save-button',
                    'path' => [Yii::app()->createUrl('admin/questions/sa/subquestions/surveyid/[0-9]{4,25}/gid/[0-9]{1,25}/qid/[0-9]{4,25}')],
                    'placement' => 'left',
                    'title' => gT('Now save the subquestions'),
                    'content' => sprintf(
                        gT("You may save empty subquestions, but that would useless.
                        %sSave now and let's edit the answer options.%s"),
                        "<p class='bg-warning alert'>","</p>"
                    ),
                    'reflex' => true,
                    'redirect' => false,
                    'onNext' => "(function(tour){
                                    $('#save-button').trigger('click');
                                    return Promise.resolve(tour);
                                })"
                ),
                array( //28
                    'element' => '#adminpanel__topbar--selectorAddAnswerOptions',
                    'path' => [Yii::app()->createUrl('/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}', 'gid' => '[0-9]{1,25}', 'qid' => '[0-9]{4,25}'])],
                    'placement' => 'bottom',
                    'title' => gT('Add some answer options to your question'),
                    'content' => sprintf(
                        gT("Now that we've got some subquestions, we have to add answer options as well%s
                        The answer options will define the values that represent your subquestions.
                        %sClick on the 'Edit subquestions' button.%s"),
                        '<br/>','<p class="alert bg-warning">','</p>'
                    ),
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
                    'content' => sprintf(
                        gT("As you can see answer options and subquestions really don't differ much.
                        %sPlease add at least two answer options to proceed.%"),
                        '<p class="alert bg-warning">','</p>'
                    ),
                    'redirect' => false,
                ),
                array( //30
                    'element' => '#save-button',
                    'path' => [Yii::app()->createUrl('admin/questions/sa/answeroptions/surveyid/[0-9]{4,25}/gid/[0-9]{1,25}/qid/[0-9]{4,25}')],
                    'placement' => 'left',
                    'title' => gT('Now save the answer options'),
                    'content' => gT('Click on save or next to proceed'),
                    'reflex' => true,
                    'redirect' => false,
                    'onNext' => "(function(tour){
                                    $('#save-button').trigger('click');
                                    return Promise.resolve(tour);
                                })"
                ),
                array( //31
                    'element' => '.selector__topbar--previewSurvey',
                    'path' => [Yii::app()->createUrl('/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}', 'gid' => '[0-9]{1,25}', 'qid' => '[0-9]{4,25}'])],
                    'placement' => 'bottom',
                    'title' => gT('Preview survey'),
                    'content' => sprintf(
                        gT("Now is the time to preview your first survey.%s
                        Just click on this button and a new window will open, where you can test run your survey.%s
                        Please be aware that your answers will not be saved, because the survey isn't active yet.
                        %sClick on 'Preview survey' and return to this window when you are done testing.%s"),
                        '<br/>','<br/>','<p class="alert bg-warning">','</p>'
                    ),
                    'redirect' => false,
                ),
                array( //32
                    'element' => '#breadcrumb-container',
                    'path' => [Yii::app()->createUrl('/admin/survey/sa/view', ['surveyid' => '[0-9]{4,25}', 'gid' => '[0-9]{1,25}', 'qid' => '[0-9]{4,25}'])],
                    'placement' => 'bottom',
                    'title' => gT('Easy navigation with the "breadcrumbs"'),
                    'content' => sprintf(
                        gT('In the top bar of the admin interface you see the "breadcrumbs".%s
                        These will always be an easy way to get back to any previous setting.
                        %sClick on the name of your survey to get back to the survey settings overview.%s'),
                        '<br/>','<p class="alert bg-warning">','</p>'
                    ),
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
                    'content' => sprintf(
                        gT("Now activate this simple survey.%s
                        You can have as many surveys as you like.
                        %sClick on 'Activate this survey'%s"),
                        '<br/>','<p class="alert bg-warning">','</p>'
                    ),
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
                    'content' => sprintf(
                        gT('These settings cannot be changed once the survey is online.%s
                        For this simple survey the default settings are ok, but read the disclaimer carefully when you activate your own surveys.%s
                        For more information consult or manual, or our forum.
                        %sNow click on "Save & activate survey"%s'),
                        '<br/>','<br/>','<p class="alert bg-warning">','</p>'
                    ),
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
                    'content' => sprintf(
                        gT("Here you can select to start your survey in closed access mode.%s
                        For our simple survey it is better to start in open access mode.%s
                        The closed access mode needs a participant list, which you may create by clicking on the menu entry 'Participant tokens'.%s
                        For more information please consult our manual or our forum.
                        %sClick on 'No, thanks'%s"),
                        '<br/>','<br/>','<br/>','<p class="alert bg-warning">','</p>'
                    ),
                    'reflex' => true,
                    'redirect' => false,
                    'onNext' => "(function(tour){
                            $('#activateTokenTable__selector--no').trigger('click');
                            return Promise.resolve(tour);
                        })",
                ),
                array( //36
                    'element' => '#adminpanel__surveysummary--mainLanguageLink',
                    'path' => Yii::app()->createUrl('/'),
                    'placement' => 'top',
                    'title' => gT('Share this link'),
                    'content' => sprintf(
                        gT("Just share this link with some of your friends and of course, test it yourself.
                        %sThank you for taking the tour!%s"),
                        '<p class="alert bg-success lstutorial__typography--white">','</p>'
                    ),
                    'redirect' => false
                ),
                ),
                'debug' => true,
                'orphan' => true,
                'keyboard' => false,
                'template' => "<div class='popover tour lstutorial__template--mainContainer'> <div class='arrow'></div> <h3 class='popover-title lstutorial__template--title'></h3> <div class='popover-content lstutorial__template--content'></div> <div class='popover-navigation lstutorial__template--navigation'>     <div class='btn-group col-xs-8' role='group' aria-label='...'>         <button class='btn btn-default col-xs-6' data-role='prev'>« Prev</button>         <button class='btn btn-primary col-xs-6' data-role='next'>Next »</button>     </div>     <div class='col-xs-4'>         <button class='btn btn-warning' data-role='end'>End tour</button>     </div> </div></div>",
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

		$criteria=new CDbCriteria;

		$criteria->compare('tid',$this->tid);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('active',$this->active);
		$criteria->compare('permission',$this->permission,true);
		$criteria->compare('permission_grade',$this->permission_grade,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    public function getPrebuilt($prebuiltName){
        if(isset($this->preBuiltPackage[$prebuiltName])){
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
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
