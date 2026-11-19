import { act } from 'react'
import { renderHook } from '@testing-library/react'
import { QueryClientProvider } from '@tanstack/react-query'

import { queryClient } from 'queryClient'
import { Entities, SCALE_1, SCALE_2, STATES } from 'helpers'

import { useQuestionChildren } from './useQuestionChildren'

const QID = 5

const wrapper = ({ children }) => (
  <QueryClientProvider client={queryClient}>{children}</QueryClientProvider>
)

const answer = (aid, code, scaleId) => ({ aid, qid: QID, code, scaleId })
const subquestion = (qid, title) => ({ qid, parentQid: QID, title, scaleId: 0 })

const dualScaleAnswers = [
  answer(11, 'A1', SCALE_1),
  answer(12, 'A2', SCALE_1),
  answer(21, 'B1', SCALE_2),
  answer(22, 'B2', SCALE_2),
]
const subquestions = [subquestion(7, 'SQ001'), subquestion(8, 'SQ002')]

// Renders the hook for a dual scale question with the survey active or not, returning its handlers and the handleUpdate mock
const renderChildren = (isSurveyActive) => {
  queryClient.setQueryData(
    ['appState', STATES.IS_SURVEY_ACTIVE],
    isSurveyActive
  )
  const saved = { answers: dualScaleAnswers, subquestions }
  const handleUpdate = jest.fn((update) => Object.assign(saved, update))
  const { result } = renderHook(
    () =>
      useQuestionChildren({
        question: {
          qid: QID,
          gid: 2,
          questionThemeName: 'arrays/dualscale',
          answers: dualScaleAnswers,
          subquestions,
        },
        handleUpdate,
        surveySettings: { languages: ['en'] },
        language: 'en',
      }),
    { wrapper }
  )
  return { handlers: result.current, handleUpdate, saved }
}

describe('useQuestionChildren on an active survey', () => {
  afterEach(() => {
    queryClient.setQueryData(['appState', STATES.IS_SURVEY_ACTIVE], false)
  })

  test('answer options are recoded, added and removed', () => {
    const { handlers, handleUpdate } = renderChildren(true)

    act(() => {
      handlers.handleChildCodeUpdate({
        newCode: 'A9',
        childIndex: 0,
        childArray: dualScaleAnswers,
        entityType: Entities.answer,
        entityTitleKey: 'answer',
      })
      handlers.handleChildAdd(dualScaleAnswers, Entities.answer, {
        scaleId: SCALE_1,
      })
      handlers.handleChildDelete(12, dualScaleAnswers, Entities.answer)
    })

    expect(handleUpdate).toHaveBeenCalledTimes(3)
    expect(handleUpdate.mock.calls[0][0].answers.map((a) => a.code)).toEqual([
      'A9',
      'A2',
      'B1',
      'B2',
    ])
  })

  test('subquestions keep their codes and can not be added or removed', () => {
    const { handlers, saved } = renderChildren(true)

    act(() => {
      handlers.handleChildCodeUpdate({
        newCode: 'SQ009',
        childIndex: 0,
        childArray: subquestions,
        entityType: Entities.subquestion,
        entityTitleKey: 'question',
      })
      handlers.handleChildAdd(subquestions, Entities.subquestion)
      handlers.handleChildDelete(7, subquestions, Entities.subquestion)
    })

    expect(saved.subquestions).toEqual([
      subquestion(7, 'SQ001'),
      subquestion(8, 'SQ002'),
    ])
  })

  test('subquestions are recoded on an inactive survey', () => {
    const { handlers, handleUpdate } = renderChildren(false)

    act(() => {
      handlers.handleChildCodeUpdate({
        newCode: 'SQ009',
        childIndex: 0,
        childArray: subquestions,
        entityType: Entities.subquestion,
        entityTitleKey: 'question',
      })
    })

    expect(handleUpdate).toHaveBeenCalledWith({
      subquestions: [subquestion(7, 'SQ009'), subquestion(8, 'SQ002')],
    })
  })

  test('a dual scale answer takes a code that the other scale uses', () => {
    const { handlers, handleUpdate } = renderChildren(true)

    act(() => {
      handlers.handleChildCodeUpdate({
        newCode: 'A1',
        childIndex: 2,
        childArray: dualScaleAnswers,
        entityType: Entities.answer,
        entityTitleKey: 'answer',
      })
    })

    expect(handleUpdate).toHaveBeenCalledWith({
      answers: [
        answer(11, 'A1', SCALE_1),
        answer(12, 'A2', SCALE_1),
        answer(21, 'A1', SCALE_2),
        answer(22, 'B2', SCALE_2),
      ],
    })
  })
})
