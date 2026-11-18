import { mapValues, pick } from 'lodash'

import {
  createBufferOperation,
  Entities,
  getAnswerExample,
  getQuestionExample,
  Operations,
  SCALE_1,
  SCALE_2,
} from 'helpers'
import { getOperationScheme } from 'helpers/Buffer/getOperationScheme'
import { OperationsBuffer } from 'helpers/Buffer/OperationsBuffer'

import { act, renderHook } from '@testing-library/react'
import { QueryClientProvider } from '@tanstack/react-query'
import { queryClient } from 'queryClient'

import {
  createChildrenUpdateOperation,
  useQuestionChildren,
} from './useQuestionChildren'

const mockAddToBuffer = jest.fn()
jest.mock('./useBuffer', () => ({
  useBuffer: () => ({ addToBuffer: mockAddToBuffer }),
}))

const QID = 5

// Children as returned by the survey detail endpoint for a question that was
// converted to "Array dual scale" (type '1') from another array type.
const serverAnswer = (aid, code, scaleId) => ({
  aid,
  qid: QID,
  code,
  sortOrder: 0,
  assessmentValue: 0,
  scaleId,
  l10ns: { en: { id: aid * 10, aid, answer: code, language: 'en' } },
})

const serverSubquestion = {
  qid: 7,
  parentQid: QID,
  sid: 1,
  type: 'T',
  title: 'SQ001',
  preg: null,
  other: false,
  mandatory: null,
  encrypted: false,
  sortOrder: 0,
  scaleId: SCALE_1,
  sameDefault: false,
  questionThemeName: null,
  moduleName: null,
  gid: 2,
  relevance: '1',
  sameScript: false,
  l10ns: {
    en: {
      id: 9,
      qid: 7,
      question: 'Row',
      help: null,
      script: null,
      language: 'en',
    },
  },
  attributes: [],
  answers: [],
  scenarios: [],
  conditiontext: '',
}

const addOperationWithoutErrors = (operation) => {
  const consoleError = jest.spyOn(console, 'error').mockImplementation()
  const buffer = new OperationsBuffer()
  buffer.addOperation(operation)
  expect(consoleError).not.toHaveBeenCalled()
  consoleError.mockRestore()
  return buffer.getOperations()
}

describe('converted Array dual scale children operations', () => {
  test('answer update with server answers and new answers on both scales is valid', () => {
    const answers = [
      serverAnswer(11, 'A1', SCALE_1),
      getAnswerExample({ qid: QID, languages: ['en'], scaleId: SCALE_1 }),
      serverAnswer(12, 'A1', SCALE_2),
      getAnswerExample({ qid: QID, languages: ['en'], scaleId: SCALE_2 }),
    ]

    const [operation] = addOperationWithoutErrors(
      createChildrenUpdateOperation(QID, Entities.answer, answers)
    )

    expect(operation.entity).toBe(Entities.answer)
    expect(operation.props.map((a) => a.scaleId)).toEqual([0, 0, 1, 1])
  })

  test('subquestion update strips read-only conditiontext and is valid', () => {
    const subquestions = [
      serverSubquestion,
      getQuestionExample({ gid: 2, parentQid: QID, languages: ['en'] }),
    ]

    const [operation] = addOperationWithoutErrors(
      createChildrenUpdateOperation(QID, Entities.subquestion, subquestions)
    )

    expect(operation.entity).toBe(Entities.subquestion)
    expect(operation.props[0]).not.toHaveProperty('conditiontext')
    expect(subquestions[0]).toHaveProperty('conditiontext')
  })
})

describe('other operations reported while editing a converted question', () => {
  test('a new question create operation only sends question/help l10ns', () => {
    const question = getQuestionExample({
      gid: 2,
      type: '1',
      questionThemeName: 'arrays/dualscale',
      languages: ['en'],
      answers: [
        getAnswerExample({ qid: 'temp__1', languages: ['en'] }),
        getAnswerExample({
          qid: 'temp__1',
          languages: ['en'],
          scaleId: SCALE_2,
        }),
      ],
      subquestions: [
        getQuestionExample({ gid: 2, parentQid: 'temp__1', languages: ['en'] }),
      ],
    })

    addOperationWithoutErrors(
      createBufferOperation(question.qid)
        .question()
        .create({
          question: { ...question, tempId: question.qid },
          questionL10n: mapValues(question.l10ns, (l10n) =>
            pick(l10n, ['question', 'help'])
          ),
          attributes: {},
          answers: [...question.answers],
          subquestions: [...question.subquestions],
        })
    )
  })

  test('language setting update must use a null id', () => {
    addOperationWithoutErrors(
      createBufferOperation(null)
        .languageSetting()
        .update({ en: { title: 'Survey' } })
    )

    const { error } = getOperationScheme(
      Operations.update,
      Entities.languageSetting
    ).validate(
      createBufferOperation(1)
        .languageSetting()
        .update({ en: { title: 'Survey' } })
    )
    expect(error.message).toBe('"id" must be [null]')
  })
})

describe('handleLabelSetLoad', () => {
  const wrapper = ({ children }) => (
    <QueryClientProvider client={queryClient}>{children}</QueryClientProvider>
  )

  const renderChildrenHook = (question) => {
    const handleUpdate = jest.fn()
    const { result } = renderHook(
      () =>
        useQuestionChildren({
          question,
          handleUpdate,
          surveySettings: { languages: ['en', 'de'] },
          language: 'en',
        }),
      { wrapper }
    )
    return { result, handleUpdate }
  }

  beforeEach(() => mockAddToBuffer.mockReset())

  test('appends the labels as answers in all survey languages', () => {
    const existingAnswers = [serverAnswer(11, 'A1', SCALE_1)]
    const { result, handleUpdate } = renderChildrenHook({
      qid: QID,
      gid: 2,
      questionThemeName: 'listradio',
      answers: existingAnswers,
    })

    act(() =>
      result.current.handleLabelSetLoad(existingAnswers, Entities.answer, [
        { code: 'A1', assessmentValue: 2, l10ns: { en: 'Good', de: 'Gut' } },
        { code: 'A2', assessmentValue: 0, l10ns: { en: 'Bad' } },
      ])
    )

    const { answers } = handleUpdate.mock.calls[0][0]
    expect(answers.map((answer) => answer.code)).toEqual(['A1', 'A003', 'A2'])
    expect(answers.map((answer) => answer.sortOrder)).toEqual([0, 1, 2])
    expect(answers[1]).toMatchObject({ qid: QID, assessmentValue: 2 })
    expect(answers[1].l10ns.en.answer).toBe('Good')
    expect(answers[1].l10ns.de.answer).toBe('Gut')
    expect(answers[2].l10ns.de.answer).toBe('')
    expect(answers[1].appKey).toBeTruthy()
    expect(mockAddToBuffer).toHaveBeenCalledTimes(1)
  })

  test('appends the labels as subquestions with the code as title', () => {
    const { result, handleUpdate } = renderChildrenHook({
      qid: QID,
      gid: 2,
      questionThemeName: 'multiplechoice',
      subquestions: [],
    })

    act(() =>
      result.current.handleLabelSetLoad([], Entities.subquestion, [
        { code: 'L1', assessmentValue: 3, l10ns: { en: 'First' } },
      ])
    )

    const [subquestion] = handleUpdate.mock.calls[0][0].subquestions
    expect(subquestion).toMatchObject({
      title: 'L1',
      parentQid: QID,
      gid: 2,
      sortOrder: 0,
    })
    expect(subquestion.l10ns.en.question).toBe('First')
    expect(subquestion).not.toHaveProperty('assessmentValue')
  })

  test('does nothing without labels', () => {
    const { result, handleUpdate } = renderChildrenHook({
      qid: QID,
      questionThemeName: 'listradio',
      answers: [],
    })

    act(() => result.current.handleLabelSetLoad([], Entities.answer, []))

    expect(handleUpdate).not.toHaveBeenCalled()
    expect(mockAddToBuffer).not.toHaveBeenCalled()
  })
})
