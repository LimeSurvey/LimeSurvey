import { render } from '@testing-library/react'

import { QuestionSettings } from './QuestionSettings'

const mockUseCloseConditionPopups = jest.fn()
let mockSurvey
let mockFocused

jest.mock('hooks', () => ({
  ...jest.requireActual('hooks'),
  useAppState: (key, initValue) => [initValue, jest.fn()],
  useSurvey: () => ({ survey: mockSurvey, update: jest.fn(), language: 'en' }),
  useBuffer: () => ({
    addToBuffer: jest.fn(),
    removeAnswersFromBuffer: jest.fn(),
  }),
  useFocused: () => ({
    focused: mockFocused,
    unFocus: jest.fn(),
    setFocused: jest.fn(),
  }),
  useCloseConditionPopups: (...args) => mockUseCloseConditionPopups(...args),
}))

describe('QuestionSettings', () => {
  beforeEach(() => {
    mockFocused = { qid: 11 }
    jest.clearAllMocks()
  })

  it('closes the condition popups when the focused question is missing from the survey', () => {
    // E.g. its group was deleted, or it was deleted in another session
    mockSurvey = { questionGroups: [{ gid: 1, questions: [{ qid: 12 }] }] }

    const { container } = render(<QuestionSettings surveyId="1" />)

    expect(mockUseCloseConditionPopups).toHaveBeenLastCalledWith(11, true)
    expect(container).toBeEmptyDOMElement()
  })

  it('keeps the condition popups open while the focused question exists', () => {
    mockSurvey = { questionGroups: [{ gid: 1, questions: [{ qid: 11 }] }] }

    render(<QuestionSettings surveyId="1" />)

    expect(mockUseCloseConditionPopups).toHaveBeenLastCalledWith(11, false)
  })

  it('keeps the condition popups open while the survey is not loaded yet', () => {
    mockSurvey = {}

    render(<QuestionSettings surveyId="1" />)

    expect(mockUseCloseConditionPopups).toHaveBeenLastCalledWith(11, false)
  })
})
