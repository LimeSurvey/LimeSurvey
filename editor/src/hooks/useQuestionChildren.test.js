import { mapValues, pick } from 'lodash'
import { renderHook } from '@testing-library/react'
import { QueryClientProvider } from '@tanstack/react-query'

import {
  createBufferOperation,
  Entities,
  getAnswerExample,
  getQuestionExample,
  Operations,
  SCALE_1,
  SCALE_2,
  STATES,
} from 'helpers'
import { queryClient } from 'queryClient'
import {
  IMAGE_OPTION_ALT_ATTRIBUTE,
  IMAGE_OPTION_SETTINGS_ATTRIBUTE,
} from 'components/QuestionTypes/imageOptionSettings'
import { getOperationScheme } from 'helpers/Buffer/getOperationScheme'
import { OperationsBuffer } from 'helpers/Buffer/OperationsBuffer'

import {
  createChildrenUpdateOperation,
  useQuestionChildren,
} from './useQuestionChildren'

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

describe('image settings of image select options', () => {
  const wrapper = ({ children }) => (
    <QueryClientProvider client={queryClient}>{children}</QueryClientProvider>
  )

  const imageAttributes = {
    [IMAGE_OPTION_SETTINGS_ATTRIBUTE]: {
      '': JSON.stringify({ A1: { width: 400 }, A2: { width: 200 } }),
    },
    [IMAGE_OPTION_ALT_ATTRIBUTE]: { en: JSON.stringify({ A1: 'Lime' }) },
  }

  afterAll(() => {
    queryClient.removeQueries({ queryKey: [STATES.SURVEY] })
    queryClient.removeQueries({ queryKey: [STATES.BUFFER] })
  })

  const renderChildren = (questionThemeName) => {
    // addToBuffer() only buffers operations while a survey is loaded.
    queryClient.setQueryData([STATES.SURVEY], { survey: { sid: 1 } })
    queryClient.setQueryData([STATES.BUFFER], [])
    const handleUpdate = jest.fn()
    const answers = [serverAnswer(11, 'A1', 0), serverAnswer(12, 'A2', 0)]
    const { result } = renderHook(
      () =>
        useQuestionChildren({
          question: {
            qid: QID,
            questionThemeName,
            attributes: imageAttributes,
            answers,
          },
          handleUpdate,
          surveySettings: { languages: ['en'] },
          language: 'en',
        }),
      { wrapper }
    )
    return { result, handleUpdate, answers }
  }

  const getAttributeOperation = () =>
    queryClient
      .getQueryData([STATES.BUFFER])
      .find((operation) => operation.entity === Entities.questionAttribute)

  test('renaming an option code moves its image settings', () => {
    const { result, handleUpdate, answers } = renderChildren(
      'image_select-listradio'
    )

    result.current.handleChildCodeUpdate({
      newCode: 'B1',
      childIndex: 0,
      childArray: answers,
      entityType: Entities.answer,
      entityTitleKey: 'answer',
    })

    const { attributes } = handleUpdate.mock.calls[0][0]
    expect(JSON.parse(attributes[IMAGE_OPTION_SETTINGS_ATTRIBUTE][''])).toEqual(
      { B1: { width: 400 }, A2: { width: 200 } }
    )
    expect(JSON.parse(attributes[IMAGE_OPTION_ALT_ATTRIBUTE].en)).toEqual({
      B1: 'Lime',
    })
    const operation = getAttributeOperation()
    expect(operation.props).toEqual({
      [IMAGE_OPTION_SETTINGS_ATTRIBUTE]: {
        '': attributes[IMAGE_OPTION_SETTINGS_ATTRIBUTE][''],
      },
      [IMAGE_OPTION_ALT_ATTRIBUTE]: {
        en: attributes[IMAGE_OPTION_ALT_ATTRIBUTE].en,
      },
    })
    expect(
      getOperationScheme(
        Operations.update,
        Entities.questionAttribute
      ).validate(operation).error
    ).toBeUndefined()
  })

  test('renaming moves the settings from the code the question has, also when the children lag behind', () => {
    // Sets up the buffer and gives the answers as the children show them.
    const { handleUpdate, answers } = renderChildren('image_select-listradio')
    // The question already has A1 → A10, the children passed in still show A1.
    const questionAnswers = [{ ...answers[0], code: 'A10' }, answers[1]]
    const attributes = {
      [IMAGE_OPTION_SETTINGS_ATTRIBUTE]: {
        '': JSON.stringify({ A10: { width: 400 } }),
      },
    }
    const { result: laggingResult } = renderHook(
      () =>
        useQuestionChildren({
          question: {
            qid: QID,
            questionThemeName: 'image_select-listradio',
            attributes,
            answers: questionAnswers,
          },
          handleUpdate,
          surveySettings: { languages: ['en'] },
          language: 'en',
        }),
      { wrapper }
    )
    laggingResult.current.handleChildCodeUpdate({
      newCode: 'A105',
      childIndex: 0,
      childArray: answers,
      entityType: Entities.answer,
      entityTitleKey: 'answer',
    })

    const { attributes: updated } = handleUpdate.mock.calls[0][0]
    expect(JSON.parse(updated[IMAGE_OPTION_SETTINGS_ATTRIBUTE][''])).toEqual({
      A105: { width: 400 },
    })
  })

  test('deleting an option removes its image settings', () => {
    const { result, handleUpdate, answers } = renderChildren(
      'image_select-listradio'
    )

    result.current.handleChildDelete(11, answers, Entities.answer)

    const { answers: updatedAnswers, attributes } =
      handleUpdate.mock.calls[0][0]
    expect(updatedAnswers.map((answer) => answer.code)).toEqual(['A2'])
    expect(JSON.parse(attributes[IMAGE_OPTION_SETTINGS_ATTRIBUTE][''])).toEqual(
      { A2: { width: 200 } }
    )
    expect(attributes[IMAGE_OPTION_ALT_ATTRIBUTE].en).toBe('')
    expect(getAttributeOperation()).toBeDefined()
  })

  test('a new image resets the image settings of the option', () => {
    const { result, handleUpdate, answers } = renderChildren(
      'image_select-listradio'
    )

    result.current.handleChildLUpdate(
      '/upload/surveys/1/images/new.jpg',
      0,
      answers,
      Entities.answer
    )

    const { answers: updatedAnswers, attributes } =
      handleUpdate.mock.calls[0][0]
    expect(updatedAnswers[0].l10ns.en.answer).toBe(
      '/upload/surveys/1/images/new.jpg'
    )
    expect(JSON.parse(attributes[IMAGE_OPTION_SETTINGS_ATTRIBUTE][''])).toEqual(
      { A2: { width: 200 } }
    )
    expect(attributes[IMAGE_OPTION_ALT_ATTRIBUTE].en).toBe('')
  })

  test('the same image keeps the image settings', () => {
    const { result, handleUpdate, answers } = renderChildren(
      'image_select-listradio'
    )

    result.current.handleChildLUpdate(
      answers[0].l10ns.en.answer,
      0,
      answers,
      Entities.answer
    )

    expect(handleUpdate.mock.calls[0][0]).not.toHaveProperty('attributes')
  })

  test('other themes leave the attributes alone', () => {
    const { result, handleUpdate, answers } = renderChildren('listradio')

    result.current.handleChildCodeUpdate({
      newCode: 'B1',
      childIndex: 0,
      childArray: answers,
      entityType: Entities.answer,
      entityTitleKey: 'answer',
    })
    result.current.handleChildDelete(12, answers, Entities.answer)

    expect(handleUpdate.mock.calls[0][0]).not.toHaveProperty('attributes')
    expect(handleUpdate.mock.calls[1][0]).not.toHaveProperty('attributes')
    expect(getAttributeOperation()).toBeUndefined()
  })
})
