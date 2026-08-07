import { Entities, NEW_OBJECT_ID_PREFIX } from 'helpers'

import { handleAnswerOperation } from './handleAnswerOperation'

describe('handleAnswerOperation', () => {
  test('Handles operation with newQuestionOperation', () => {
    const operation = {
      id: 'temp__1492592123',
      op: 'update',
      entity: 'answer',
      props: [
        {
          tempId: `${NEW_OBJECT_ID_PREFIX}1`,
          code: 'A001',
          sortOrder: 1,
          assessmentValue: 0,
          scaleId: 0,
          l10ns: {
            en: {
              answer: 'Answer 1',
              language: 'en',
            },
            de: {
              answer: 'Answer 1',
              language: 'de',
            },
          },
        },
      ],
    }

    const newQuestionOperation = {
      id: 'temp__149259',
      op: 'create',
      entity: 'question',
      error: false,
      props: {
        question: {
          sid: 613724,
          qid: 'temp__149259',
          tempId: 'temp__149259',
          gid: 'temp__568708',
          type: 'L',
          scaleId: 0,
          sortOrder: 1,
          questionThemeName: 'listradio',
          parentQid: 0,
          title: 'Q286734',
          preg: null,
          other: false,
          mandatory: false,
          encrypted: false,
          moduleName: null,
          sameDefault: null,
          relevance: '1',
          sameScript: null,
          l10ns: {
            en: {
              qid: 'temp__149259',
              question: '',
              language: 'en',
            },
          },
          attributes: [],
          answers: [
            {
              aid: 'temp__997693',
              tempId: 'temp__997693',
              qid: 'temp__149259',
              code: 'AO850',
              sortOrder: 1,
              assessmentValue: 0,
              scaleId: 0,
              l10ns: {
                en: {
                  aid: 'temp__997693',
                  answer: '',
                  language: 'en',
                },
              },
            },
            {
              aid: 'temp__33260',
              tempId: 'temp__33260',
              qid: 'temp__149259',
              code: 'AO996',
              sortOrder: 2,
              assessmentValue: 0,
              scaleId: 0,
              l10ns: {
                en: {
                  aid: 'temp__33260',
                  answer: '',
                  language: 'en',
                },
              },
            },
          ],
          subquestions: [],
        },
        questionL10n: {
          en: {
            qid: 'temp__149259',
            question: '',
            language: 'en',
          },
        },
        attributes: {},
        answers: {
          0: {
            aid: 'temp__997693',
            tempId: 'temp__997693',
            qid: 'temp__149259',
            code: 'AO850',
            sortOrder: 1,
            assessmentValue: 0,
            scaleId: 0,
            l10ns: {
              en: {
                aid: 'temp__997693',
                answer: '',
                language: 'en',
              },
            },
          },
          1: {
            aid: 'temp__33260',
            tempId: 'temp__33260',
            qid: 'temp__149259',
            code: 'AO996',
            sortOrder: 2,
            assessmentValue: 0,
            scaleId: 0,
            l10ns: {
              en: {
                aid: 'temp__33260',
                answer: '',
                language: 'en',
              },
            },
          },
        },
        subquestions: {},
      },
    }

    const bufferOperations = [newQuestionOperation]
    const {
      bufferOperations: updatedBuffer,
      newOperation,
      addToBuffer,
    } = handleAnswerOperation(
      bufferOperations,
      operation,
      null,
      newQuestionOperation
    )

    const modifiedQuestionOperation = {
      ...newQuestionOperation,
      props: { ...newQuestionOperation.props, answers: operation.props },
    }

    expect(updatedBuffer).toEqual([newQuestionOperation])
    expect(newOperation).toEqual(modifiedQuestionOperation)
    expect(addToBuffer).toBe(false)
  })

  test('Handles operation with currentOperation', () => {
    const bufferOperations = []
    const operation = {
      entity: Entities.answer,
      id: 'temp__1492592123',
      op: 'update',
      props: {
        tempId: `${NEW_OBJECT_ID_PREFIX}2`,
        code: 'A002',
        sortOrder: 2,
        assessmentValue: 5,
        scaleId: 1,
        l10ns: {
          en: {
            answer: 'Answer 2',
            language: 'en',
          },
        },
      },
    }

    const currentOperation = {
      entity: Entities.answer,
      id: 'temp__1492592123',
      op: 'update',
      props: {
        tempId: `${NEW_OBJECT_ID_PREFIX}2`,
        code: 'A002',
        sortOrder: 2,
        assessmentValue: 0,
        scaleId: 1,
        l10ns: {
          en: {
            answer: 'Old Answer',
            language: 'en',
          },
        },
      },
    }

    const {
      bufferOperations: updatedBuffer,
      newOperation,
      addToBuffer,
    } = handleAnswerOperation(
      bufferOperations,
      operation,
      currentOperation,
      null
    )

    expect(updatedBuffer).toEqual([])
    expect(newOperation).toEqual(operation)
    expect(addToBuffer).toBe(false)
  })

  test('Handles operation without currentOperation or newQuestionOperation', () => {
    const bufferOperations = []
    const operation = {
      entity: Entities.answer,
      id: 'temp__1492592123',
      op: 'update',
      props: {
        tempId: `${NEW_OBJECT_ID_PREFIX}3`,
        code: 'A003',
        sortOrder: 3,
        assessmentValue: 10,
        scaleId: 2,
        l10ns: {
          en: {
            answer: 'Answer 3',
            language: 'en',
          },
        },
      },
    }

    const {
      bufferOperations: updatedBuffer,
      newOperation,
      addToBuffer,
    } = handleAnswerOperation(bufferOperations, operation, null, null)

    expect(updatedBuffer).toEqual(bufferOperations)
    expect(newOperation).toEqual(operation)
    expect(addToBuffer).toBe(true)
  })
})
