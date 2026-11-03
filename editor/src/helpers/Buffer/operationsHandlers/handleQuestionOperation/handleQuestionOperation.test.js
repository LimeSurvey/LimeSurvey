import { handleQuestionOperation } from './handleQuestionOperation'
import { NEW_OBJECT_ID_PREFIX, Operations } from 'helpers'
import { cloneDeep, merge } from 'lodash'

describe('handleQuestionOperation Tests', () => {
  const questionCreateOperation = {
    entity: 'question',
    op: Operations.create,
    id: `${NEW_OBJECT_ID_PREFIX}1`,
    tempId: `${NEW_OBJECT_ID_PREFIX}1`,
    props: {
      question: {
        qid: `${NEW_OBJECT_ID_PREFIX}1`,
        tempId: `${NEW_OBJECT_ID_PREFIX}1`,
        title: 'G01Q06',
        type: '1',
        questionThemeName: 'arrays/dualscale',
        gid: '1',
        mandatory: false,
        relevance: '1',
        encrypted: false,
        saveAsDefault: false,
      },
      questionL10n: {
        en: {
          question: 'Array Question',
          help: 'Help text',
        },
        de: {
          question: 'Array ger',
          help: 'help ger',
        },
      },
      attributes: {
        dualscale_headerA: {
          de: 'A ger',
          en: 'A',
        },
        dualscale_headerB: {
          de: 'B ger',
          en: 'B',
        },
        public_statistics: {
          '': '1',
        },
      },
    },
  }

  const questionUpdateOperation = {
    entity: 'question',
    op: Operations.update,
    id: 1,
    props: {
      title: 'Q03',
      mandatory: true,
      encrypted: true,
    },
  }

  const questionUpdateOperationWithTempId = {
    entity: 'question',
    op: Operations.update,
    id: `${NEW_OBJECT_ID_PREFIX}1`,
    tempId: `${NEW_OBJECT_ID_PREFIX}1`,
    props: {
      title: 'Q03',
      mandatory: true,
      encrypted: true,
    },
  }

  const questionL10nOperation = {
    entity: 'questionL10n',
    op: Operations.update,
    id: 12345,
    props: {
      en: {
        question: 'Array Question',
        help: 'Help text',
      },
      de: {
        question: 'Array ger',
        help: 'help ger',
      },
    },
  }

  const questionL10nUpdateOperationWithTempId = {
    entity: 'questionL10n',
    op: Operations.update,
    id: `${NEW_OBJECT_ID_PREFIX}1`,
    tempId: `${NEW_OBJECT_ID_PREFIX}1`,
    props: {
      en: {
        question: 'Array Question',
        help: 'Help text',
      },
      de: {
        question: 'Array ger',
        help: 'help ger',
      },
    },
  }

  const questionDeleteOperationWithTempId = {
    entity: 'question',
    op: Operations.delete,
    id: `${NEW_OBJECT_ID_PREFIX}1`,
    tempId: `${NEW_OBJECT_ID_PREFIX}1`,
  }

  const questionDeleteOperation = {
    entity: 'question',
    op: Operations.delete,
    id: 1,
  }

  test('Handles create operation for a question', () => {
    const bufferOperations = []
    const result = handleQuestionOperation(
      bufferOperations,
      questionCreateOperation,
      null
    )

    expect(result.bufferOperations).toEqual(bufferOperations)
    expect(result.newOperation).toEqual(questionCreateOperation)
    expect(result.addToBuffer).toBe(true)
  })

  test('Handles update operation for a question', () => {
    const bufferOperations = [cloneDeep(questionCreateOperation)]
    const currentOperation = cloneDeep(questionCreateOperation)

    const result = handleQuestionOperation(
      bufferOperations,
      questionUpdateOperation,
      currentOperation
    )

    expect(result.bufferOperations).toEqual(bufferOperations)
    expect(result.newOperation).toEqual({
      ...currentOperation,
      props: merge({}, currentOperation.props, questionUpdateOperation.props),
    })
    expect(result.addToBuffer).toBe(false)
  })

  test('Handles questionL10n update operation', () => {
    const bufferOperations = []
    const result = handleQuestionOperation(
      bufferOperations,
      questionL10nOperation,
      null
    )

    expect(result.bufferOperations).toEqual(bufferOperations)
    expect(result.newOperation).toEqual(questionL10nOperation)
    expect(result.addToBuffer).toBe(true)
  })

  test('Handles delete operation for a question', () => {
    const bufferOperations = [cloneDeep(questionCreateOperation)]

    const result = handleQuestionOperation(
      bufferOperations,
      questionDeleteOperation,
      null
    )

    expect(result.bufferOperations).toEqual(bufferOperations)
    expect(result.newOperation).toEqual(questionDeleteOperation)
    expect(result.addToBuffer).toBe(true)
  })

  test('Returns empty operation if no operation is provided', () => {
    const bufferOperations = []
    const result = handleQuestionOperation(bufferOperations, null, null)

    expect(result.bufferOperations).toEqual(bufferOperations)
    expect(result.newOperation).toEqual({})
    expect(result.addToBuffer).toBe(false)
  })

  test('Handles operation with no current operation but an existing buffer', () => {
    const bufferOperations = [cloneDeep(questionCreateOperation)]
    const result = handleQuestionOperation(
      bufferOperations,
      questionUpdateOperation,
      null
    )

    expect(result.bufferOperations).toEqual(bufferOperations)
    expect(result.newOperation).toEqual(questionUpdateOperation)
    expect(result.addToBuffer).toBe(true)
  })

  test('Merges props of current and new update operations without adding to buffer when a currentUpdateOperation exists', () => {
    const currentUpdateOperation = {
      id: 1,
      op: Operations.update,
      entity: 'question',
      props: {
        title: 'Q03',
        mandatory: true,
      },
    }

    const newUpdateOperation = {
      id: 1,
      op: Operations.update,
      entity: 'question',
      props: {
        encrypted: true,
        mandatory: false,
      },
    }

    const bufferOperations = [currentUpdateOperation]

    const result = handleQuestionOperation(
      bufferOperations,
      newUpdateOperation,
      currentUpdateOperation
    )

    expect(result.newOperation).toEqual({
      ...currentUpdateOperation,
      props: merge({}, currentUpdateOperation.props, newUpdateOperation.props),
    })
    expect(result.addToBuffer).toBe(false)
    expect(result.bufferOperations).toEqual(bufferOperations)
  })

  test('Merges localized question updates for a temporary ID without adding to buffer', () => {
    const bufferOperations = [questionL10nOperation]

    const newOperation = {
      ...questionL10nOperation,
      props: {
        en: {
          question: 'New Question',
          help: 'New help text',
        },
      },
    }

    const result = handleQuestionOperation(
      bufferOperations,
      newOperation,
      questionL10nOperation
    )

    expect(result.bufferOperations).toEqual(bufferOperations)
    expect(result.newOperation).toEqual({
      ...questionL10nOperation,
      props: {
        ...questionL10nOperation.props,
        ...newOperation.props,
      },
    })
    expect(result.addToBuffer).toBe(false)
  })

  test('Should not add a new operation for a delete operation to the buffer when it is a temporary ID', () => {
    const bufferOperations = [questionCreateOperation]

    const result = handleQuestionOperation(
      bufferOperations,
      questionDeleteOperationWithTempId
    )

    expect(result.bufferOperations).toEqual([])
    expect(result.newOperation).toEqual({})
    expect(result.addToBuffer).toBe(false)
  })

  test('Merges questionUpdateOperationWithTempId into questionCreateOperation and sets addToBuffer to false', () => {
    const bufferOperations = [questionCreateOperation]

    const result = handleQuestionOperation(
      bufferOperations,
      questionUpdateOperationWithTempId,
      questionCreateOperation
    )

    expect(result.bufferOperations).toEqual(bufferOperations)
    expect(result.newOperation).toEqual({
      ...questionCreateOperation,
      props: merge(
        {},
        questionCreateOperation.props,
        questionUpdateOperationWithTempId.props
      ),
    })
    expect(result.addToBuffer).toBe(false)
  })

  test('Merges questionL10nUpdateOperationWithTempId into questionCreateOperation and sets addToBuffer to false', () => {
    const bufferOperations = [questionCreateOperation]

    const result = handleQuestionOperation(
      bufferOperations,
      questionL10nUpdateOperationWithTempId,
      questionCreateOperation
    )

    expect(result.addToBuffer).toBe(false)
    expect(result.bufferOperations).toEqual(bufferOperations)
    expect(result.newOperation).toEqual({
      ...questionCreateOperation,
      props: merge(
        {},
        questionCreateOperation.props,
        questionL10nUpdateOperationWithTempId.props
      ),
    })
  })
})
