import { act, fireEvent, render, waitFor } from '@testing-library/react'

import { STATES, SwalAlert } from 'helpers'
import { queryClient } from 'queryClient'

import { QuestionSettings } from './QuestionSettings'

// The condition popups are opened, replaced and closed for real here, so use
// the real SweetAlert instead of the shared stubs (__mocks__/sweetalert2.js
// and the sweetalert2-react-content mock in setupTests.js)
jest.unmock('sweetalert2')
jest.unmock('sweetalert2-react-content')

const mockUseCloseConditionPopups = jest.fn()
const mockAddToBuffer = jest.fn()
const mockUpdate = jest.fn()
let mockSurvey
let mockFocused

jest.mock('react-router-dom', () => ({
  ...jest.requireActual('react-router-dom'),
  useParams: () => ({ surveyId: '1' }),
}))

// Mock the defining files, not the hooks barrel: spreading
// jest.requireActual('hooks') copies exports that are not defined yet due to
// circular imports (useAuth imports useAuthService from the barrel)
jest.mock('hooks/useAppState', () => ({
  useAppState: (key, initValue) => [initValue, jest.fn()],
}))
jest.mock('hooks/useSurvey', () => ({
  useSurvey: () => ({ survey: mockSurvey, update: mockUpdate, language: 'en' }),
}))
jest.mock('hooks/useBuffer', () => ({
  useBuffer: () => ({
    addToBuffer: mockAddToBuffer,
    removeAnswersFromBuffer: jest.fn(),
  }),
}))
jest.mock('hooks/useFocused', () => ({
  useFocused: () => ({
    focused: mockFocused,
    unFocus: jest.fn(),
    setFocused: jest.fn(),
  }),
}))
jest.mock('hooks/useExpressionScriptValidation', () => ({
  useExpressionScriptValidation: () => jest.fn(),
}))
// The question theme attributes come from the API, these tests don't need them
jest.mock('hooks/useQuestionAttributes', () => ({
  useQuestionAttributes: () => ({ attributes: {}, isFetching: false }),
}))
// Records the arguments, and closes the popups for real
jest.mock('hooks/useCloseConditionPopups', () => {
  const actual = jest.requireActual('hooks/useCloseConditionPopups')
  return {
    ...actual,
    useCloseConditionPopups: (...args) => {
      mockUseCloseConditionPopups(...args)
      return actual.useCloseConditionPopups(...args)
    },
  }
})

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

const setSurvey = (survey) => {
  mockSurvey = survey
  queryClient.setQueryData([STATES.SURVEY], { survey })
}

describe('QuestionSettings', () => {
  beforeEach(() => {
    mockFocused = { qid: 11 }
    jest.clearAllMocks()
  })

  afterEach(async () => {
    await act(async () => SwalAlert.close())
    queryClient.removeQueries({ queryKey: [STATES.SURVEY] })
  })

  it('closes the condition popups when the focused question is missing from the survey', () => {
    // E.g. its group was deleted, or it was deleted in another session
    setSurvey({ questionGroups: [{ gid: 1, questions: [{ qid: 12 }] }] })

    const { container } = render(<QuestionSettings surveyId="1" />)

    expect(mockUseCloseConditionPopups).toHaveBeenLastCalledWith(11, true)
    expect(container).toBeEmptyDOMElement()
  })

  it('keeps the condition popups open while the focused question exists', () => {
    setSurvey({ questionGroups: [{ gid: 1, questions: [{ qid: 11 }] }] })

    render(<QuestionSettings surveyId="1" />)

    expect(mockUseCloseConditionPopups).toHaveBeenLastCalledWith(11, false)
  })

  it('keeps the condition popups open while the survey is not loaded yet', () => {
    setSurvey({})

    render(<QuestionSettings surveyId="1" />)

    expect(mockUseCloseConditionPopups).toHaveBeenLastCalledWith(11, false)
  })

  it('closes the overwrite confirmation when the focused question is removed', async () => {
    const question = {
      qid: 11,
      relevance: 'Q1 == "A"',
      scenarios: [{ scid: 1, conditions: [{ cid: 5 }] }],
    }
    setSurvey({ questionGroups: [{ gid: 1, questions: [question] }] })
    const { container, rerender } = render(<QuestionSettings surveyId="1" />)

    // Open the expression editor popup and apply a changed script: the
    // question has scenarios, so the overwrite confirmation replaces it
    fireEvent.click(container.querySelector('.condition-script-trigger button'))
    const editor = await waitFor(() => {
      const element = document.querySelector(
        '[data-testid="expression-script-editor"]'
      )
      expect(element).toBeInTheDocument()
      return element
    })
    fireEvent.change(editor, { target: { value: 'Q1 == "B"' } })
    fireEvent.click(
      document.querySelector('.condition-designer-overlay-primary-button')
    )
    await waitFor(() =>
      expect(
        document.querySelector('[data-testid="expression-script-editor"]')
      ).not.toBeInTheDocument()
    )
    expect(SwalAlert.getPopup()).not.toBeNull()

    // The question is removed from the loaded survey, e.g. its group was deleted
    setSurvey({ questionGroups: [{ gid: 1, questions: [] }] })
    await act(async () => {
      rerender(<QuestionSettings surveyId="1" />)
    })

    // SwalAlert.isVisible() needs layout, which jsdom doesn't have; getPopup()
    // returns the popup element while a popup is shown
    await waitFor(() => expect(SwalAlert.getPopup()).toBeNull())
    expect(mockAddToBuffer).not.toHaveBeenCalled()
    expect(mockUpdate).not.toHaveBeenCalled()
  })
})
