import { getQuestionTypeInfo, Tutorial } from 'components'
import SmallCheckMarkIcon from 'components/icons/SmallCheckMarkIcon'
import { getApiUrl, sleep, STATES, toastComoponent, TUTORIALS } from 'helpers'
import { getSurveyPanels } from 'helpers/options'
import { useAppState, useAuth, useFocused } from 'hooks'
import { useEffect, useMemo, useState } from 'react'
import { useNavigate, useParams } from 'react-router-dom'
import { UserSettingsService } from 'services'
import { format } from 'util'

export const EditorTutorial = ({ isSurveyActive = false, survey }) => {
  const { surveyId } = useParams()
  const [, setIsAddingQuestionOrGroup] = useAppState(
    STATES.IS_ADDING_QUESTION_OR_GROUP
  )
  const [hasEditorHelpAppeared, setHasEdtiroHelpAppeared] = useAppState(
    STATES.EDITOR_HELP_APPEARED,
    false,
    { staleTime: null, cacheTime: null }
  )
  const [startEditorTutorial, setStartEditorTutorial] = useAppState(
    STATES.START_EDITOR_TUTORIAL,
    false
  )
  const auth = useAuth()

  const navigate = useNavigate()
  const surveyPanels = getSurveyPanels()
  const { unFocus, setFocused } = useFocused()

  const tutorialSteps = [
    {
      title: t('Welcome to LimeSurvey'),
      content: t(
        "You're now using the LimeSurvey editor. This quick tutorial will guide you through what's most important in just a few steps."
      ),
      placement: 'center',
      target: '#editor',
    },
    {
      title: t('Canvas'),
      content: t(
        'This is where you create your questions and answer options. A sample question group and question is always created to get you started. Just click on any element to edit it.'
      ),
      placement: 'left-start',
      target: '#survey-col',
    },
    {
      title: t('Survey title'),
      content: t(
        'Click on the survey title to edit it, your changes will be saved automatically.'
      ),
      placement: 'bottom',
      target: '#top-bar-select',
    },
    {
      title: t('Add content'),
      content: t(
        "Select the plus symbol to add questions, groups or logic to the survey. For now, let's add a simple multiple-choice question."
      ),
      spotlightPadding: 20,
      target: '#topbar-add-question',
      onHighlightAreaClick: () => {
        setTutorialState((prevState) => {
          return {
            ...prevState,
            stepIndex: prevState.stepIndex + 1,
          }
        })
      },
      onNextStep: () => setIsAddingQuestionOrGroup(true),
    },
    {
      title: t('Select a question type'),
      content: t(
        'Select the question type for your new question from this menu. Try it and add a simple multiple-choice question for now.'
      ),
      spotlightPadding: 4,
      placement: 'left',
      target: '#topbar-question-inserter',
      highlight: `#question-type-${getQuestionTypeInfo().MULTIPLE_CHOICE.theme}-item`,
      disableNextButton: true,
      onPreviousStep: () => setIsAddingQuestionOrGroup(false),
      onHighlightAreaClick: async (_, __, ___, event) => {
        const target = event.target

        if (
          !target.id?.includes(
            `question-type-${getQuestionTypeInfo().MULTIPLE_CHOICE.theme}-item`
          )
        ) {
          event.preventDefault() // stop default browser action
          event.stopPropagation() // stop bubbling to parent listeners
        } else {
          await sleep(500)
          setTutorialState((prevState) => {
            return {
              ...prevState,
              stepIndex: prevState.stepIndex + 1,
            }
          })
        }
      },
    },
    {
      title: t('Edit text'),
      content: t(
        'Click and type to fill in your question text. You can also use the toolbar for formatting and more options.'
      ),
      spotlightPadding: 20,
      placement: 'left-start',
      target: '.question.focus-element',
      onPreviousStep: () => setIsAddingQuestionOrGroup(true),
    },
    {
      title: t('Delete question'),
      content: t(
        'Use the trash-icon to delete questions  or the X-icons to delete answer options.'
      ),
      spotlightPadding: 10,
      placement: 'top',
      target: '#question-footer-delete-icon',
      onHighlightAreaClick: async (_, __, ___, event) => {
        event.preventDefault()
        event.stopPropagation()
      },
    },
    {
      title: t('Question settings'),
      content: t(
        'Use this panel to change question types, logic, and display settings. You can also switch between simple and advanced question settings.'
      ),
      spotlightPadding: 20,
      placement: 'left',
      target: '#sidebar-question-settings',
      onNextStep: () => {
        navigate(`/survey/${surveyId}/${getSurveyPanels().structure.panel}`)
      },
    },
    {
      title: t('Reorder questions'),
      content: t(
        'You can reorder your questions and groups by drag and drop inside the survey structure.'
      ),
      spotlightPadding: 5,
      placement: 'right',
      target: '#survey-menu',
    },
    {
      title: t('Theme options'),
      content: t(
        "Open the settings to access theme options and change your survey's design. Try it and change the style of your survey."
      ),
      spotlightPadding: 5,
      placement: 'right',
      target: `#btn-${surveyPanels.presentation.panel}-open`,
      onNextStep: async () => {
        unFocus()
        navigate(
          `/survey/${surveyId}/${getSurveyPanels().presentation.panel}/${getSurveyPanels().presentation.defaultMenu}`
        )
      },
      onHighlightAreaClick: async () => {
        await sleep(500)
        setTutorialState((prevState) => {
          return {
            ...prevState,
            stepIndex: prevState.stepIndex + 1,
          }
        })
      },
    },
    {
      title: t('Theme options'),
      content: t(
        'The theme options allow you to change the appearance of your survey. Try it and change a style of your survey.'
      ),
      spotlightPadding: 5,
      placement: 'left',
      target: `#survey-settings`,
      onPreviousStep: () => {
        const firstQuestion = survey.questionGroups[0].questions[0]
        navigate(`/survey/${surveyId}/${getSurveyPanels().structure.panel}`)
        setFocused(firstQuestion, 0, 0)
      },
    },
    {
      title: t('Theme options'),
      content: t('You will see a preview of your changes on the right side.'),
      spotlightPadding: 40,
      placement: 'left',
      target: `#theme-options-preview-container`,
      onNextStep: () => {},
    },
    {
      title: t('Return to the editor'),
      content: t(
        'Use the left-side menu bar to navigate back to your survey at any time.'
      ),
      spotlightPadding: 5,
      placement: 'right',
      target: `#btn-${surveyPanels.structure.panel}-open`,
      onNextStep: () => {
        navigate(`/survey/${surveyId}/${getSurveyPanels().structure.panel}`)
      },
      onHighlightAreaClick: async () => {
        await sleep(500)
        setTutorialState((prevState) => {
          return {
            ...prevState,
            stepIndex: prevState.stepIndex + 1,
          }
        })
      },
    },
    {
      title: t('Autosave'),
      content: t(
        "See how LimeSurvey autosaves your work? You don't have to worry about saving your progress at any time."
      ),
      spotlightPadding: 5,
      placement: 'bottom',
      target: `#auto-saved`,
      onPreviousStep: () => {
        navigate(
          `/survey/${surveyId}/${getSurveyPanels().presentation.panel}/${getSurveyPanels().presentation.defaultMenu}`
        )
      },
    },
    {
      title: t('Preview survey'),
      content: t(
        'Use the survey preview to see how your survey will look like for your participants.'
      ),
      spotlightPadding: 5,
      placement: 'bottom',
      target: '#preview-button',
    },
    {
      title: t('Activate survey'),
      content: t(
        'When your survey is ready, use this button to activate it. Once activated, you can access the options for sharing your survey.'
      ),
      spotlightPadding: 5,
      placement: 'bottom',
      target: `#activate-survey-button`,
      onHighlightAreaClick: async () => {
        setTutorialState((prevState) => {
          return {
            ...prevState,
            stepIndex: prevState.stepIndex + 1,
          }
        })
      },
    },
  ]

  const [tutorialState, setTutorialState] = useState({
    run: false,
    steps: tutorialSteps,
    stepIndex: 0,
  })

  const userSettingsService = useMemo(
    () => new UserSettingsService(auth, getApiUrl()),
    [auth]
  )

  useEffect(() => {
    if (process.env.REACT_APP_DEMO_MODE === 'true') {
      return
    }

    userSettingsService
      .getUserSettingByName(TUTORIALS.EDITOR_TUTORIAL)
      .then((response) => {
        // starting the tutorial automatically if the user has never started the tutorial before
        if (
          response.httpStatus === 404 &&
          !isSurveyActive &&
          !hasEditorHelpAppeared
        ) {
          setHasEdtiroHelpAppeared(true)
          handleTutorialStart()
        }
      })
  }, [isSurveyActive, surveyId])

  const handleOnStepChange = (data) => {
    userSettingsService.setUserSettingByName(
      TUTORIALS.EDITOR_TUTORIAL,
      data.index
    )
  }

  const handleTutorialStart = () => {
    window.scrollTo(0, 0)

    unFocus()
    navigate(`/survey/${surveyId}/${getSurveyPanels().structure.panel}`)
    setTutorialState({ ...tutorialState, stepIndex: 0, run: true })
    setStartEditorTutorial(false)
  }

  const handleOnTutorialFinish = () => {
    toastComoponent({
      Component: (
        <div className="tutorial-finished-content">
          <div className="green-mark"></div>
          <span className="reg14">
            <SmallCheckMarkIcon />
            {format(t('%s Congrats! You have completed the tour.'), '🎉')}
          </span>
        </div>
      ),
      position: 'bottom-center',
      className: 'tutorial-finish-modal',
    })
  }

  useEffect(() => {
    if (startEditorTutorial) {
      handleTutorialStart()
    }
  }, [startEditorTutorial])

  return (
    <>
      <Tutorial
        tutorialState={tutorialState}
        setTutorialState={setTutorialState}
        onStepChange={handleOnStepChange}
        onTutorialFinish={handleOnTutorialFinish}
      />
    </>
  )
}
