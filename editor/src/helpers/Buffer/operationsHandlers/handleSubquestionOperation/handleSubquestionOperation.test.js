import { handleSubquestionOperation } from './handleSubquestionOperation'
import { cloneDeep } from 'lodash'
import { Operations } from 'helpers'

describe('handleSubquestionOperation Tests', () => {
  const subquestionOperation = {
    id: 'temp__149259',
    op: Operations.update,
    entity: 'subquestion',
    props: [
      {
        qid: 'temp__149259212',
        parentQid: 'temp__149259',
        sid: 596477,
        type: 'T',
        title: 'SQ001',
        preg: null,
        other: false,
        mandatory: null,
        encrypted: false,
        sortOrder: 0,
        scaleId: 0,
        questionThemeName: 'longfreetext',
        gid: 5,
        relevance: '1',
        l10ns: {
          en: {
            id: 73,
            qid: 71,
            question: 'Subquestion 1',
            language: 'en',
          },
        },
        attributes: [],
        answers: [],
      },
      {
        qid: 'temp__14925912',
        parentQid: 'temp__149259',
        sid: 596477,
        type: 'T',
        title: 'SQ002',
        preg: null,
        other: false,
        mandatory: null,
        encrypted: false,
        sortOrder: 1,
        scaleId: 0,
        questionThemeName: 'longfreetext',
        gid: 5,
        relevance: '1',
        l10ns: {
          en: {
            id: 76,
            qid: 74,
            question: 'Subquestion 2',
            language: 'en',
          },
        },
        attributes: [],
        answers: [],
      },
    ],
  }

  const newQuestionOperation = {
    id: 'temp__149259',
    op: Operations.create,
    entity: 'question',
    props: {
      question: {
        sid: 613724,
        qid: 'temp__149259',
        tempId: 'temp__149259',
        gid: 'temp__568708',
        type: 'L',
        scaleId: 0,
        sortOrder: 1,
        title: 'Q286734',
        relevance: '1',
        l10ns: { en: { question: '', language: 'en' } },
        attributes: [],
        answers: [],
        subquestions: [],
      },
      subquestions: [],
    },
  }

  test('Handles new question operation with subquestions', () => {
    const result = handleSubquestionOperation(
      [newQuestionOperation],
      subquestionOperation,
      null,
      newQuestionOperation
    )

    expect(result.bufferOperations).toEqual([newQuestionOperation])
    expect(result.newOperation).toEqual({
      ...newQuestionOperation,
      props: {
        ...newQuestionOperation.props,
        subquestions: subquestionOperation.props,
      },
    })
    expect(result.addToBuffer).toBe(false)
  })

  test('Handles current operation with subquestions', () => {
    const currentOperation = cloneDeep(newQuestionOperation)
    currentOperation.props.subquestions = [
      {
        qid: 'temp__149259212',
        parentQid: 'temp__149259',
        sid: 596477,
        type: 'T',
        title: 'SQ001',
        sortOrder: 0,
        scaleId: 0,
        relevance: '1',
        l10ns: { en: { question: 'Subquestion 1', language: 'en' } },
        attributes: [],
        answers: [],
      },
    ]

    const result = handleSubquestionOperation(
      [currentOperation],
      subquestionOperation,
      currentOperation,
      null
    )

    expect(result.bufferOperations).toEqual([currentOperation])
    expect(result.newOperation).toEqual({
      ...currentOperation,
      props: [...subquestionOperation.props],
    })
    expect(result.addToBuffer).toBe(false)
  })

  test('Handles operation without current or new question operation', () => {
    const result = handleSubquestionOperation(
      [],
      subquestionOperation,
      null,
      null
    )

    expect(result.bufferOperations).toEqual([])
    expect(result.newOperation).toEqual(subquestionOperation)
    expect(result.addToBuffer).toBe(true)
  })

  test('Returns empty operation when no operation is provided', () => {
    const result = handleSubquestionOperation([], null, null, null)

    expect(result.bufferOperations).toEqual([])
    expect(result.newOperation).toEqual({})
    expect(result.addToBuffer).toBe(false)
  })
})
