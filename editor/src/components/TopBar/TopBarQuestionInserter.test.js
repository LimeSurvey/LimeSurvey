import { render } from '@testing-library/react'

import { getQuestionTypeInfo } from 'components/QuestionTypes'

import { TopBarQuestionInserter } from './TopBarQuestionInserter'

let mockCallBack
jest.mock('components/QuestionTypeSelector', () => ({
  QuestionTypeSelector: ({ callBack }) => {
    mockCallBack = callBack
    return null
  },
}))

const mockUpdate = jest.fn()
const mockAddToBuffer = jest.fn()
const mockPrefixes = { answerPrefix: 'AB', subquestionPrefix: 'CD' }
jest.mock('hooks', () => ({
  useAppState: (_key, initial) => [initial, jest.fn()],
  useBuffer: () => ({ addToBuffer: mockAddToBuffer }),
  useCodePrefixes: () => mockPrefixes,
  useFocused: () => ({ setFocused: jest.fn(), groupIndex: 0 }),
  useSurvey: () => ({
    survey: {
      sid: 1,
      language: 'en',
      additionalLanguages: [],
      languages: ['en'],
      questionGroups: [{ gid: 1, sortOrder: 1, questions: [], l10ns: {} }],
    },
    update: mockUpdate,
    language: 'en',
  }),
}))

const addQuestion = (type) => {
  render(<TopBarQuestionInserter surveyID={1} />)
  mockCallBack({
    type: type.type,
    questionThemeName: type.theme,
  })
}

describe('TopBarQuestionInserter and code prefixes', () => {
  beforeEach(() => {
    jest.clearAllMocks()
    mockPrefixes.isLoading = false
  })

  test('does not add a question while prefixes are loading', () => {
    mockPrefixes.isLoading = true
    addQuestion(getQuestionTypeInfo().MULTIPLE_CHOICE)
    expect(mockUpdate).not.toHaveBeenCalled()
    expect(mockAddToBuffer).not.toHaveBeenCalled()
  })

  test('still adds a question group while prefixes are loading', () => {
    mockPrefixes.isLoading = true
    addQuestion(getQuestionTypeInfo().QUESTION_GROUP)
    expect(mockUpdate).toHaveBeenCalled()
  })

  test('uses the configured prefixes once loaded', () => {
    addQuestion(getQuestionTypeInfo().ARRAY)
    const operation = mockAddToBuffer.mock.calls[0][0]
    expect(operation.props.answers[0].code).toBe('AB001')
    expect(operation.props.subquestions[0].title).toBe('CD001')
  })
})
