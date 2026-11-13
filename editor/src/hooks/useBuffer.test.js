import { renderHook } from '@testing-library/react'
import { QueryClientProvider } from '@tanstack/react-query'

import { queryClient } from 'queryClient'
import { Entities, STATES } from 'helpers'

import { useBuffer } from './useBuffer'

const wrapper = ({ children }) => (
  <QueryClientProvider client={queryClient}>{children}</QueryClientProvider>
)

describe('useBuffer removeFromBuffer', () => {
  const answerCreate = { id: 10, entity: Entities.answer, op: 'create' }
  const answerUpdate = { id: 10, entity: Entities.answer, op: 'update' }
  const otherAnswers = { id: 11, entity: Entities.answer, op: 'update' }
  const questionText = { id: 20, entity: Entities.questionL10n, op: 'update' }

  test('removes only the answer operations of the question', () => {
    queryClient.setQueryData(
      [STATES.BUFFER],
      [answerCreate, otherAnswers, answerUpdate, questionText]
    )
    const { result } = renderHook(() => useBuffer(), { wrapper })

    result.current.removeFromBuffer(10, Entities.answer)

    expect(queryClient.getQueryData([STATES.BUFFER])).toEqual([
      otherAnswers,
      questionText,
    ])
  })

  test('keeps the buffer when nothing matches', () => {
    queryClient.setQueryData([STATES.BUFFER], [questionText])
    const { result } = renderHook(() => useBuffer(), { wrapper })

    result.current.removeFromBuffer(10, Entities.answer)

    expect(queryClient.getQueryData([STATES.BUFFER])).toEqual([questionText])
  })
})
