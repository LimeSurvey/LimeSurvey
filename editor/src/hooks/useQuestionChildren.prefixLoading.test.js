import { act, renderHook } from '@testing-library/react'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'

import { Entities } from 'helpers'

import { useQuestionChildren } from './useQuestionChildren'

const mockPrefixes = {
  answerPrefix: '',
  subquestionPrefix: '',
  isLoading: false,
}
jest.mock('./useCodePrefixes', () => ({
  useCodePrefixes: () => mockPrefixes,
}))

const wrapper = ({ children }) => (
  <QueryClientProvider client={new QueryClient()}>
    {children}
  </QueryClientProvider>
)

const question = { qid: 5, gid: 2, answers: [], subquestions: [] }

const setup = () => {
  const handleUpdate = jest.fn()
  const { result } = renderHook(
    () =>
      useQuestionChildren({
        question,
        handleUpdate,
        surveySettings: { languages: ['en'] },
        language: 'en',
      }),
    { wrapper }
  )
  return { result, handleUpdate }
}

describe('useQuestionChildren handleChildAdd and code prefixes', () => {
  beforeEach(() => {
    Object.assign(mockPrefixes, {
      answerPrefix: '',
      subquestionPrefix: '',
      isLoading: false,
    })
  })

  test('does not add a child while prefixes are loading', () => {
    mockPrefixes.isLoading = true
    const { result, handleUpdate } = setup()
    act(() => result.current.handleChildAdd([], Entities.answer))
    act(() => result.current.handleChildAdd([], Entities.subquestion))
    expect(handleUpdate).not.toHaveBeenCalled()
  })

  test('uses the configured prefixes once loaded', () => {
    mockPrefixes.answerPrefix = 'AB'
    mockPrefixes.subquestionPrefix = 'CD'
    const { result, handleUpdate } = setup()
    act(() => result.current.handleChildAdd([], Entities.answer))
    act(() => result.current.handleChildAdd([], Entities.subquestion))
    expect(handleUpdate.mock.calls[0][0].answers[0].code).toBe('AB001')
    expect(handleUpdate.mock.calls[1][0].subquestions[0].title).toBe('CD001')
  })

  test('falls back to numeric codes when prefixes are empty (e.g. failed load)', () => {
    const { result, handleUpdate } = setup()
    act(() => result.current.handleChildAdd([], Entities.answer))
    act(() => result.current.handleChildAdd([], Entities.subquestion))
    expect(handleUpdate.mock.calls[0][0].answers[0].code).toBe('1')
    expect(handleUpdate.mock.calls[1][0].subquestions[0].title).toBe('1')
  })
})
