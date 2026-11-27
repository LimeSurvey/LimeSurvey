import React from 'react'
import { renderHook, waitFor } from '@testing-library/react'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'

import questionTypesData from 'helpers/data/question-types.json'
import { useQuestionTypes } from './useQuestionTypes'

const mockGet = jest.fn()
let mockLanguage = 'en'

jest.mock('axios', () => ({
  __esModule: true,
  default: { create: () => ({ get: (...args) => mockGet(...args) }) },
}))

jest.mock('./useAuth', () => ({
  __esModule: true,
  default: () => ({ restHeaders: {} }),
}))

jest.mock('react-i18next', () => ({
  useTranslation: () => ({ i18n: { language: mockLanguage } }),
}))

const serverData = {
  success: true,
  categories: [
    { name: 'text', title: 'Texte', icon: 'QuestionInserterTextIcon' },
  ],
  questionTypes: {
    shortfreetext: {
      name: 'shortfreetext',
      type: 'S',
      title: 'Texte court',
      category: 'text',
      sortOrder: 0,
      devOnly: false,
      visible: true,
    },
  },
}

const render = () => {
  const queryClient = new QueryClient({
    defaultOptions: { queries: { retry: false } },
  })
  const wrapper = ({ children }) => (
    <QueryClientProvider client={queryClient}>{children}</QueryClientProvider>
  )
  return renderHook(() => useQuestionTypes(), { wrapper })
}

describe('useQuestionTypes', () => {
  const demoMode = process.env.REACT_APP_DEMO_MODE
  const originalT = global.t

  beforeEach(() => {
    // The tests run in demo mode, which doesn't call the API
    process.env.REACT_APP_DEMO_MODE = 'false'
    mockLanguage = 'en'
    global.t = (text, { lng } = {}) =>
      (lng === 'de' && { 'Short text': 'Kurzer Text' }[text]) || text
    mockGet.mockReset()
    jest.spyOn(console, 'error').mockImplementation(() => {})
  })

  afterEach(() => {
    process.env.REACT_APP_DEMO_MODE = demoMode
    global.t = originalT
    jest.restoreAllMocks()
  })

  it('returns the question types from the API', async () => {
    mockGet.mockResolvedValue({ data: serverData })

    const { result } = render()

    await waitFor(() =>
      expect(result.current.categories).toEqual(serverData.categories)
    )
    expect(result.current.getTitle('shortfreetext')).toBe('Texte court')
    expect(result.current.getTitle('unknown', 'Fallback')).toBe('Fallback')
    expect(result.current.isLoaded).toBe(true)
    expect(mockGet).toHaveBeenCalledWith('questionTypes', expect.anything())
  })

  it('returns the default question types until the API responds', () => {
    mockGet.mockReturnValue(new Promise(() => {}))

    const { result } = render()

    expect(result.current.categories).toEqual(questionTypesData.categories)
    expect(result.current.getTitle('shortfreetext')).toBe('Short text')
    expect(result.current.isLoaded).toBe(false)
  })

  it('returns the default question types as loaded in demo mode', () => {
    process.env.REACT_APP_DEMO_MODE = 'true'

    const { result } = render()

    expect(result.current.categories).toEqual(questionTypesData.categories)
    expect(result.current.isLoaded).toBe(true)
    expect(mockGet).not.toHaveBeenCalled()
  })

  it('translates the default question types into the user language', () => {
    mockGet.mockReturnValue(new Promise(() => {}))
    mockLanguage = 'de'

    const { result } = render()

    expect(result.current.getTitle('shortfreetext')).toBe('Kurzer Text')
    expect(result.current.getTitle('listradio')).toBe('List (Radio)')
  })

  it('returns the default question types when the request fails', async () => {
    mockGet.mockRejectedValue(new Error('Network Error'))

    const { result } = render()

    await waitFor(() => expect(mockGet).toHaveBeenCalled())
    await waitFor(() => expect(result.current.isFetching).toBe(false))
    expect(result.current.categories).toEqual(questionTypesData.categories)
    expect(result.current.questionTypes).toEqual(
      questionTypesData.questionTypes
    )
    expect(result.current.isLoaded).toBe(false)
  })

  it('keeps the previous titles while the ones of a new language load', async () => {
    mockGet.mockResolvedValueOnce({ data: serverData })
    const { result, rerender } = render()
    await waitFor(() =>
      expect(result.current.getTitle('shortfreetext')).toBe('Texte court')
    )

    mockGet.mockReturnValueOnce(new Promise(() => {}))
    mockLanguage = 'de'
    rerender()

    await waitFor(() => expect(mockGet).toHaveBeenCalledTimes(2))
    expect(result.current.getTitle('shortfreetext')).toBe('Texte court')
    expect(result.current.isLoaded).toBe(true)
  })
})
