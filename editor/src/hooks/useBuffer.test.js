import { renderHook } from '@testing-library/react'
import { QueryClientProvider } from '@tanstack/react-query'

import { queryClient } from 'queryClient'
import { Entities, STATES } from 'helpers'

import { useBuffer } from './useBuffer'

const wrapper = ({ children }) => (
  <QueryClientProvider client={queryClient}>{children}</QueryClientProvider>
)

describe('useBuffer removeAnswersFromBuffer', () => {
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

    result.current.removeAnswersFromBuffer(10)

    expect(queryClient.getQueryData([STATES.BUFFER])).toEqual([
      otherAnswers,
      questionText,
    ])
  })

  test('clears the answers queued in the create operation of an unsaved question', () => {
    const questionCreate = (qid) => ({
      id: qid,
      entity: Entities.question,
      op: 'create',
      props: {
        question: { qid, type: 'L' },
        answers: [{ tempId: 'temp__456', code: 'A1' }],
      },
    })
    const typeUpdate = {
      id: 'temp__123',
      entity: Entities.question,
      op: 'update',
      props: { type: 'M' },
    }
    queryClient.setQueryData(
      [STATES.BUFFER],
      [questionCreate('temp__123'), questionCreate('temp__789'), typeUpdate]
    )
    const { result } = renderHook(() => useBuffer(), { wrapper })

    result.current.removeAnswersFromBuffer('temp__123')

    const unsavedQuestion = questionCreate('temp__123')
    unsavedQuestion.props.answers = []
    expect(queryClient.getQueryData([STATES.BUFFER])).toEqual([
      unsavedQuestion,
      questionCreate('temp__789'),
      typeUpdate,
    ])
  })

  test('keeps the buffer when nothing matches', () => {
    queryClient.setQueryData([STATES.BUFFER], [questionText])
    const { result } = renderHook(() => useBuffer(), { wrapper })

    result.current.removeAnswersFromBuffer(10)

    expect(queryClient.getQueryData([STATES.BUFFER])).toEqual([questionText])
  })
})
