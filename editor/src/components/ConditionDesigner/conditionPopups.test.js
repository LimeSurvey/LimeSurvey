import { act, fireEvent, render, waitFor } from '@testing-library/react'

import { htmlPopup, STATES, SwalAlert } from 'helpers'
import { useCloseConditionPopups } from 'hooks/useCloseConditionPopups'
import { queryClient } from 'queryClient'

import { ExpressionScriptInputBox } from './ExpressionScript/ExpressionScriptInputBox'
import { ScenarioList } from './ScenarioList'
import { CONDITION_POPUP_CLASS, syncSurveyQuestionWithScenarios } from './utils'

// The popups' open / replace / close behavior is what is tested here, so use
// the real SweetAlert instead of the shared stubs (__mocks__/sweetalert2.js
// and the sweetalert2-react-content mock in setupTests.js)
jest.unmock('sweetalert2')
jest.unmock('sweetalert2-react-content')

const mockAddToBuffer = jest.fn()
const mockUpdate = jest.fn()

jest.mock('react-router-dom', () => ({
  ...jest.requireActual('react-router-dom'),
  useParams: () => ({ surveyId: '1' }),
}))

jest.mock('hooks', () => ({
  ...jest.requireActual('hooks'),
  useBuffer: () => ({ addToBuffer: mockAddToBuffer }),
  useSurvey: () => ({ update: mockUpdate }),
  useFocused: () => ({ focused: { qid: 11 } }),
  useExpressionScriptValidation: () => jest.fn(),
}))

// CodeMirror can't be typed into in jsdom. Mock the defining file only:
// spreading the components/UIComponents barrel copies exports that are not
// defined yet due to circular imports.
jest.mock(
  'components/UIComponents/ExpressionScriptEditor/ExpressionScriptEditor',
  () => {
    const React = require('react')
    return {
      ExpressionScriptEditor: ({ value, onChange }) =>
        React.createElement('textarea', {
          'data-testid': 'expression-script-editor',
          value,
          'onChange': (event) => onChange(event.target.value),
        }),
    }
  }
)

const createQuestion = () => ({
  qid: 11,
  relevance: 'Q1 == "A"',
  scenarios: [
    { scid: 1, conditions: [{ cid: 5 }] },
    { scid: 2, conditions: [{ cid: 6 }] },
  ],
})

const setCachedQuestionGroups = (questionGroups) =>
  queryClient.setQueryData([STATES.SURVEY], { survey: { questionGroups } })

// SwalAlert.isVisible() needs layout, which jsdom doesn't have; getPopup()
// returns the popup element while a popup is shown
const getVisiblePopup = () => SwalAlert.getPopup()

// The question's panel in QuestionSettings: closes the condition popups
const QuestionPanel = ({ question, isQuestionMissing = false }) => {
  useCloseConditionPopups(question.qid, isQuestionMissing)
  return (
    <ExpressionScriptInputBox
      question={question}
      onShowPanel={jest.fn()}
      onNavigateBack={jest.fn()}
    />
  )
}

/**
 * Opens the editor popup and applies a changed script. The question has
 * scenarios, so the overwrite confirmation replaces the editor popup.
 */
const openOverwriteConfirmation = async (container) => {
  // The expand button
  fireEvent.click(container.querySelectorAll('button')[0])
  const editor = await waitFor(() => {
    const element = document.querySelector(
      '[data-testid="expression-script-editor"]'
    )
    expect(element).toBeInTheDocument()
    return element
  })
  expect(getVisiblePopup()).toHaveClass(CONDITION_POPUP_CLASS)

  fireEvent.change(editor, { target: { value: 'Q1 == "B"' } })
  fireEvent.click(
    document.querySelector('.condition-designer-overlay-primary-button')
  )
  await waitFor(() =>
    expect(
      document.querySelector('[data-testid="expression-script-editor"]')
    ).not.toBeInTheDocument()
  )
  expect(getVisiblePopup()).toHaveClass(CONDITION_POPUP_CLASS)
}

const openDeleteScenarioPopup = async (container) => {
  fireEvent.click(container.querySelector('.scenario-list-item button'))
  await waitFor(() =>
    expect(
      document.querySelector('.condition-designer-overlay-danger-button')
    ).toBeInTheDocument()
  )
}

const confirmDeleteScenario = async () => {
  await act(async () => {
    fireEvent.click(
      document.querySelector('.condition-designer-overlay-danger-button')
    )
  })
}

describe('condition popups', () => {
  afterEach(async () => {
    await act(async () => SwalAlert.close())
    queryClient.removeQueries({ queryKey: [STATES.SURVEY] })
    jest.clearAllMocks()
  })

  it('closes the confirmation that replaced the editor popup when the question goes missing', async () => {
    const question = createQuestion()
    setCachedQuestionGroups([{ gid: 1, questions: [question] }])
    const { container, rerender } = render(
      <QuestionPanel question={question} />
    )
    await openOverwriteConfirmation(container)

    await act(async () => {
      rerender(<QuestionPanel question={question} isQuestionMissing />)
    })

    await waitFor(() => expect(getVisiblePopup()).toBeNull())
    expect(mockAddToBuffer).not.toHaveBeenCalled()
    expect(mockUpdate).not.toHaveBeenCalled()
  })

  it('closes the condition popups when the question panel unmounts', async () => {
    const question = createQuestion()
    setCachedQuestionGroups([{ gid: 1, questions: [question] }])
    const { container, unmount } = render(<QuestionPanel question={question} />)
    await openOverwriteConfirmation(container)

    await act(async () => unmount())

    await waitFor(() => expect(getVisiblePopup()).toBeNull())
    expect(mockAddToBuffer).not.toHaveBeenCalled()
  })

  it('leaves popups that are not condition popups open', async () => {
    const { unmount } = render(<QuestionPanel question={createQuestion()} />)

    act(() => {
      htmlPopup({ html: 'Other popup', popupClass: 'other-popup' })
    })
    await waitFor(() => expect(getVisiblePopup()).toHaveClass('other-popup'))

    await act(async () => unmount())

    expect(getVisiblePopup()).toHaveClass('other-popup')
  })

  it('does not delete a scenario of a question removed while the popup was open', async () => {
    const question = createQuestion()
    setCachedQuestionGroups([{ gid: 1, questions: [question] }])
    const { container } = render(
      <ScenarioList question={question} setScenarioToPatch={jest.fn()} />
    )
    await openDeleteScenarioPopup(container)

    // The question's group is deleted
    setCachedQuestionGroups([{ gid: 2, questions: [] }])
    await confirmDeleteScenario()

    expect(mockAddToBuffer).not.toHaveBeenCalled()
    expect(mockUpdate).not.toHaveBeenCalled()
  })

  it("deletes the scenario at the question's current position after a reorder", async () => {
    const question = createQuestion()
    const otherQuestion = { qid: 12, scenarios: [] }
    setCachedQuestionGroups([
      { gid: 1, questions: [question] },
      { gid: 2, questions: [otherQuestion] },
    ])
    const { container } = render(
      <ScenarioList question={question} setScenarioToPatch={jest.fn()} />
    )
    await openDeleteScenarioPopup(container)

    // The groups are swapped, the question is now in the second group
    setCachedQuestionGroups([
      { gid: 2, questions: [otherQuestion] },
      { gid: 1, questions: [question] },
    ])
    await confirmDeleteScenario()

    expect(mockAddToBuffer).toHaveBeenCalledTimes(1)
    // setQueryData copies changed parts of the cache, so compare by value
    const { questionGroups } = mockUpdate.mock.calls[0][0]
    expect(questionGroups[0].questions[0]).toEqual(otherQuestion)
    expect(questionGroups[1].questions[0].qid).toBe(question.qid)
    expect(
      questionGroups[1].questions[0].scenarios.map((scenario) => scenario.scid)
    ).toEqual([2])
  })
})

describe('syncSurveyQuestionWithScenarios', () => {
  afterEach(() => {
    queryClient.removeQueries({ queryKey: [STATES.SURVEY] })
  })

  it('writes the question at its current position', () => {
    const otherQuestion = { qid: 12 }
    setCachedQuestionGroups([
      { gid: 2, questions: [otherQuestion] },
      { gid: 1, questions: [{ qid: 11 }] },
    ])
    const update = jest.fn()
    const updatedQuestion = { qid: 11, scenarios: [{ scid: 3 }] }

    syncSurveyQuestionWithScenarios(updatedQuestion, update, {})

    const { questionGroups } = update.mock.calls[0][0]
    expect(questionGroups[0].questions[0]).toBe(otherQuestion)
    expect(questionGroups[1].questions[0]).toBe(updatedQuestion)
  })

  it('does nothing when the question no longer exists', () => {
    setCachedQuestionGroups([{ gid: 2, questions: [{ qid: 12 }] }])
    const update = jest.fn()

    syncSurveyQuestionWithScenarios({ qid: 11, scenarios: [] }, update, {})

    expect(update).not.toHaveBeenCalled()
  })
})
