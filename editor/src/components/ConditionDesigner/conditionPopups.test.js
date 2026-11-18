import {
  act,
  fireEvent,
  render,
  renderHook,
  waitFor,
} from '@testing-library/react'

import { htmlPopup, STATES, SwalAlert } from 'helpers'
import { useCloseConditionPopups } from 'hooks/useCloseConditionPopups'
import { useConditionDesigner } from 'hooks/useConditionDesigner'
import { queryClient } from 'queryClient'

import { ExpressionScriptInputBox } from './ExpressionScript/ExpressionScriptInputBox'
import { handleConditionScriptUpdate } from './ExpressionScript/handleConditionScriptUpdate'
import { ScenarioList } from './ScenarioList'
import {
  CONDITION_POPUP_CLASS,
  getConditionTypeInfo,
  getDefaultCondition,
  isConditionChangedSince,
  isScenarioDraftValid,
  replaceQuestionInSurvey,
  syncSurveyQuestionWithScenarios,
  updateQuestionScenarios,
} from './utils'

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
const openEditorPopup = async (container) => {
  // The expand button
  fireEvent.click(container.querySelector('.condition-script-trigger button'))
  const editor = await waitFor(() => {
    const element = document.querySelector(
      '[data-testid="expression-script-editor"]'
    )
    expect(element).toBeInTheDocument()
    return element
  })
  expect(getVisiblePopup()).toHaveClass(CONDITION_POPUP_CLASS)
  return editor
}

/**
 * Applies a changed script in the open editor popup. The question has
 * scenarios, so the overwrite confirmation replaces the editor popup.
 */
const applyEditedScript = async (editor) => {
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

const openOverwriteConfirmation = async (container) =>
  applyEditedScript(await openEditorPopup(container))

// Clicks "Save changes" in the open overwrite confirmation
const confirmOverwrite = async () => {
  await act(async () => {
    fireEvent.click(
      document.querySelector('.condition-designer-overlay-primary-button')
    )
  })
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
    // A scenario is left, so the condition is kept
    expect(questionGroups[1].questions[0].relevance).toBe(question.relevance)
  })

  it('does not delete a scenario that a refresh removed while the popup was open', async () => {
    const question = createQuestion()
    setCachedQuestionGroups([{ gid: 1, questions: [question] }])
    const { container } = render(
      <ScenarioList question={question} setScenarioToPatch={jest.fn()} />
    )
    await openDeleteScenarioPopup(container)

    // A refresh replaced the scenarios with an ExpressionScript condition
    setCachedQuestionGroups([
      {
        gid: 1,
        questions: [{ ...question, scenarios: [], relevance: 'Q2 == "B"' }],
      },
    ])
    await confirmDeleteScenario()

    expect(mockAddToBuffer).not.toHaveBeenCalled()
    expect(mockUpdate).not.toHaveBeenCalled()
  })

  it('deletes the last scenario without changing the cached question', async () => {
    const question = {
      ...createQuestion(),
      scenarios: [{ scid: 1, conditions: [{ cid: 5 }] }],
    }
    setCachedQuestionGroups([{ gid: 1, questions: [question] }])
    const { container } = render(
      <ScenarioList question={question} setScenarioToPatch={jest.fn()} />
    )
    await openDeleteScenarioPopup(container)
    await confirmDeleteScenario()

    const { questionGroups } = mockUpdate.mock.calls[0][0]
    const writtenQuestion = questionGroups[0].questions[0]
    expect(writtenQuestion.scenarios).toEqual([])
    // The last scenario is gone, so the question has no condition anymore
    expect(writtenQuestion.relevance).toBe('1')
    // The cached question only changes through `update`
    expect(writtenQuestion).not.toBe(question)
    expect(question.scenarios).toHaveLength(1)
    expect(question.relevance).toBe('Q1 == "A"')
  })

  it('asks for confirmation when a refresh added scenarios while the editor popup was open', async () => {
    // The question has no visual scenarios when the editor popup opens
    const question = { ...createQuestion(), scenarios: [] }
    setCachedQuestionGroups([{ gid: 1, questions: [question] }])
    const { container } = render(<QuestionPanel question={question} />)
    const editor = await openEditorPopup(container)

    // A refresh adds scenarios while the editor popup is open
    setCachedQuestionGroups([{ gid: 1, questions: [createQuestion()] }])

    // Applying asks to overwrite the new scenarios instead of applying
    await applyEditedScript(editor)

    expect(mockAddToBuffer).not.toHaveBeenCalled()
    expect(mockUpdate).not.toHaveBeenCalled()

    // Confirming applies: the confirmation was shown for the new scenarios
    await confirmOverwrite()

    expect(mockAddToBuffer).toHaveBeenCalledTimes(1)
    const { questionGroups } = mockUpdate.mock.calls[0][0]
    expect(questionGroups[0].questions[0]).toMatchObject({
      relevance: 'Q1 == "B"',
      scenarios: [],
    })
  })

  it('applies the confirmed script when the condition is unchanged', async () => {
    const question = createQuestion()
    setCachedQuestionGroups([{ gid: 1, questions: [question] }])
    const { container } = render(<QuestionPanel question={question} />)
    await openOverwriteConfirmation(container)

    await confirmOverwrite()

    expect(mockAddToBuffer).toHaveBeenCalledTimes(1)
    const { questionGroups } = mockUpdate.mock.calls[0][0]
    expect(questionGroups[0].questions[0]).toMatchObject({
      relevance: 'Q1 == "B"',
      scenarios: [],
    })
  })

  it('does not apply when the condition changed while the confirmation was open', async () => {
    const question = createQuestion()
    setCachedQuestionGroups([{ gid: 1, questions: [question] }])
    const { container } = render(<QuestionPanel question={question} />)
    await openOverwriteConfirmation(container)

    // A refresh replaces the scenarios with a script from another session
    setCachedQuestionGroups([
      {
        gid: 1,
        questions: [{ ...question, scenarios: [], relevance: 'Q3 == "C"' }],
      },
    ])
    await confirmOverwrite()

    // The user confirmed overwriting the scenarios, not the new script
    expect(mockAddToBuffer).not.toHaveBeenCalled()
    expect(mockUpdate).not.toHaveBeenCalled()
  })
})

describe('saving a scenario', () => {
  const conditionTypeInfo = getConditionTypeInfo()

  const createCondition = () => ({
    ...getDefaultCondition(),
    qid: 11,
    cqid: 5,
    cfieldname: '1X1X5',
    method: '==',
    value: 'A1',
    sourceType: conditionTypeInfo.SOURCE.QUESTION,
    targetType: conditionTypeInfo.TARGET.CONSTANT,
    scenario: 1,
  })

  const renderConditionDesigner = (
    question,
    conditions,
    scenarioToPatch = null
  ) =>
    renderHook(() =>
      useConditionDesigner({
        question,
        focused: {},
        scid: 1,
        scenarioToPatch,
        conditions,
        addToBuffer: mockAddToBuffer,
        setScid: jest.fn(),
        setConditions: jest.fn(),
        update: mockUpdate,
        setPendingScenarioName: jest.fn(),
        onNavigateBack: jest.fn(),
      })
    )

  afterEach(() => {
    queryClient.removeQueries({ queryKey: [STATES.SURVEY] })
    jest.clearAllMocks()
  })

  it('detects a condition that a refresh added while the confirmation was open', () => {
    // The question had no scenarios when the confirmation opened
    const capturedQuestion = { qid: 11, relevance: '1', scenarios: [] }
    // A refresh added the same condition
    setCachedQuestionGroups([
      {
        gid: 1,
        questions: [
          {
            ...capturedQuestion,
            scenarios: [
              { scid: 1, conditions: [{ ...createCondition(), cid: 7 }] },
            ],
          },
        ],
      },
    ])
    const { result } = renderConditionDesigner(capturedQuestion, [
      createCondition(),
    ])

    act(() => result.current.handleSavingScenario())

    expect(mockAddToBuffer).not.toHaveBeenCalled()
    expect(mockUpdate).not.toHaveBeenCalled()
  })

  it('saves a new condition without changing the cached question', () => {
    const question = { qid: 11, relevance: '1', scenarios: [] }
    setCachedQuestionGroups([{ gid: 1, questions: [question] }])
    const { result } = renderConditionDesigner(question, [createCondition()])

    act(() => result.current.handleSavingScenario())

    expect(mockAddToBuffer).toHaveBeenCalledTimes(1)
    const { questionGroups } = mockUpdate.mock.calls[0][0]
    expect(questionGroups[0].questions[0].scenarios).toHaveLength(1)
    expect(question.scenarios).toEqual([])
  })

  it('stops when the edited scenario no longer exists', () => {
    // A refresh replaced the scenarios with an ExpressionScript condition
    setCachedQuestionGroups([
      {
        gid: 1,
        questions: [{ qid: 11, relevance: 'Q2 == "B"', scenarios: [] }],
      },
    ])
    const scenario = { scid: 1, conditions: [{ ...createCondition(), cid: 7 }] }
    const { result } = renderConditionDesigner(
      { qid: 11, relevance: '1', scenarios: [scenario] },
      // The draft deletes the scenario's condition
      [{ ...createCondition(), cid: 7, isDeleted: true }],
      { ...scenario, qid: 11 }
    )

    act(() => result.current.handleSavingScenario())

    // No delete is queued, and the new condition is not reset
    expect(mockAddToBuffer).not.toHaveBeenCalled()
    expect(mockUpdate).not.toHaveBeenCalled()
  })

  it('keeps a cleared scenario number empty instead of scenario 0', () => {
    const setScid = jest.fn()
    const { result } = renderHook(() =>
      useConditionDesigner({
        question: { qid: 11, relevance: '1', scenarios: [] },
        focused: {},
        scid: 1,
        scenarioToPatch: null,
        conditions: [createCondition()],
        addToBuffer: mockAddToBuffer,
        setScid,
        setConditions: jest.fn(),
        update: mockUpdate,
        setPendingScenarioName: jest.fn(),
        onNavigateBack: jest.fn(),
      })
    )

    act(() =>
      result.current.handleScenarioNameChange({ target: { value: '' } })
    )

    expect(setScid).toHaveBeenCalledWith('')
  })

  it('does nothing when the question was removed', () => {
    setCachedQuestionGroups([{ gid: 2, questions: [] }])
    const { result } = renderConditionDesigner(
      { qid: 11, relevance: '1', scenarios: [] },
      [createCondition()]
    )

    act(() => result.current.handleSavingScenario())

    expect(mockAddToBuffer).not.toHaveBeenCalled()
    expect(mockUpdate).not.toHaveBeenCalled()
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
    // A copy, so later changes of the caller's object stay out of the cache
    expect(questionGroups[1].questions[0]).toEqual(updatedQuestion)
    expect(questionGroups[1].questions[0]).not.toBe(updatedQuestion)
  })

  it('does not change the cached survey', () => {
    const question = { qid: 11, scenarios: [] }
    const group = { gid: 1, questions: [question] }
    setCachedQuestionGroups([group])
    const update = jest.fn()

    replaceQuestionInSurvey({ ...question, scenarios: [{ scid: 1 }] }, update)

    expect(group.questions[0]).toBe(question)
    expect(question.scenarios).toEqual([])
    const { questionGroups } = update.mock.calls[0][0]
    expect(questionGroups[0]).not.toBe(group)
    expect(questionGroups[0].questions[0].scenarios).toEqual([{ scid: 1 }])
  })

  it('does nothing when the question no longer exists', () => {
    setCachedQuestionGroups([{ gid: 2, questions: [{ qid: 12 }] }])
    const update = jest.fn()

    syncSurveyQuestionWithScenarios({ qid: 11, scenarios: [] }, update, {})

    expect(update).not.toHaveBeenCalled()
  })
})

describe('condition helpers', () => {
  afterEach(() => {
    queryClient.removeQueries({ queryKey: [STATES.SURVEY] })
    jest.clearAllMocks()
  })

  it('keeps conditions that are not moved when a scenario is renamed', () => {
    // Condition 9 is not in the draft, e.g. a refresh added it
    const question = {
      qid: 11,
      scenarios: [{ scid: 1, conditions: [{ cid: 5 }, { cid: 9 }] }],
    }

    updateQuestionScenarios(
      [{ scid: 2, conditions: [{ cid: 5, scenario: 2 }] }],
      question,
      { current: 1 }
    )

    // As on the server: only the saved condition moved to scenario 2
    expect(question.scenarios).toEqual([
      { scid: 1, conditions: [{ cid: 9 }] },
      { scid: 2, conditions: [{ cid: 5, scenario: 2 }] },
    ])
  })

  it('removes the renamed scenario when all its conditions moved', () => {
    const question = {
      qid: 11,
      scenarios: [{ scid: 1, conditions: [{ cid: 5 }] }],
    }

    updateQuestionScenarios(
      [{ scid: 2, conditions: [{ cid: 5, scenario: 2 }] }],
      question,
      { current: 1 }
    )

    expect(question.scenarios).toEqual([
      { scid: 2, conditions: [{ cid: 5, scenario: 2 }] },
    ])
  })

  it('skips rows marked for deletion when validating a draft', () => {
    const conditionTypeInfo = getConditionTypeInfo()
    const validRow = {
      ...getDefaultCondition(),
      cqid: 5,
      method: '==',
      value: 'A1',
      sourceType: conditionTypeInfo.SOURCE.QUESTION,
    }
    // E.g. cleared by the first-question reset
    const clearedRow = {
      ...getDefaultCondition(),
      sourceType: conditionTypeInfo.SOURCE.PARTICIPANT_DATA,
    }

    expect(
      isScenarioDraftValid([validRow, { ...clearedRow, isDeleted: true }], 1)
    ).toBe(true)
    expect(isScenarioDraftValid([validRow, clearedRow], 1)).toBe(false)
    // Scenario numbers are whole numbers from 1
    expect(isScenarioDraftValid([validRow], '')).toBe(false)
    expect(isScenarioDraftValid([validRow], 0)).toBe(false)
    expect(isScenarioDraftValid([validRow], -1)).toBe(false)
    expect(isScenarioDraftValid([validRow], 1.5)).toBe(false)
    expect(isScenarioDraftValid([validRow], 2)).toBe(true)
  })

  it('replaces visual scenarios with an unchanged script', () => {
    // The script equals the relevance generated from the scenarios
    const question = { ...createQuestion(), relevance: 'Q1 == "A"' }
    setCachedQuestionGroups([{ gid: 1, questions: [question] }])
    const update = jest.fn()

    handleConditionScriptUpdate('Q1 == "A"', 11, mockAddToBuffer, update)

    expect(mockAddToBuffer).toHaveBeenCalledTimes(1)
    const { questionGroups } = update.mock.calls[0][0]
    expect(questionGroups[0].questions[0].scenarios).toEqual([])
  })

  it('does nothing for an unchanged script without scenarios', () => {
    const question = { ...createQuestion(), scenarios: [] }
    setCachedQuestionGroups([{ gid: 1, questions: [question] }])
    const update = jest.fn()

    handleConditionScriptUpdate('Q1 == "A"', 11, mockAddToBuffer, update)

    expect(mockAddToBuffer).not.toHaveBeenCalled()
    expect(update).not.toHaveBeenCalled()
  })

  it('does not report a removed question as changed', () => {
    setCachedQuestionGroups([{ gid: 1, questions: [] }])

    // The condition actions skip a removed question themselves
    expect(isConditionChangedSince(11, { relevance: '1', scenarios: [] })).toBe(
      false
    )
  })
})
