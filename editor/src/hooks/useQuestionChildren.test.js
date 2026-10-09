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

import { createChildrenUpdateOperation } from './useQuestionChildren'

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
