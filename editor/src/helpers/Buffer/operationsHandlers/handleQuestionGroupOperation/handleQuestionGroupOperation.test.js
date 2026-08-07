import { merge } from 'lodash'
import { handleQuestionGroupOperation } from './handleQuestionGroupOperation'
import { Operations } from 'helpers'

describe('handleQuestionGroupOperation Tests', () => {
  const createOperation = {
    id: 'temp__568708',
    op: Operations.create,
    entity: 'questionGroup',
    props: {
      questionGroup: {
        gid: 'temp__568708',
        sid: 613724,
        type: 'QG',
        theme: 'QuestionGroup',
        sortOrder: 1,
        gRelevance: '',
        tempId: 'temp__568708',
      },
      questionGroupL10n: {
        en: {
          groupName: '',
          description: '',
        },
      },
    },
  }
  const createOperation2 = {
    id: 'temp__5682270822',
    op: Operations.create,
    entity: 'questionGroup',
    props: {
      questionGroup: {
        gid: 'temp__5682270822',
        sid: 613724,
        type: 'QG',
        theme: 'QuestionGroup',
        sortOrder: 1,
        gRelevance: '',
        tempId: 'temp__5682270822',
      },
      questionGroupL10n: {
        en: {
          groupName: '',
          description: '',
        },
      },
    },
  }

  const updateOperation = {
    entity: 'questionGroup',
    op: Operations.update,
    id: 7,
    props: {
      questionGroup: {
        randomizationGroup: '',
        gRelevance: '',
      },
      questionGroupL10n: {
        en: {
          groupName: '3rd Group - updated',
          description: 'English',
        },
        fr: {
          groupName: 'Troisième Groupe - updated',
          description: 'French',
        },
      },
    },
  }

  const updateOperation2 = {
    entity: 'questionGroup',
    op: Operations.update,
    id: 7,
    props: {
      questionGroup: {
        randomizationGroup: '',
        gRelevance: '',
      },
      questionGroupL10n: {
        en: {
          groupName: '3rd Group - updated 2',
          description: 'English',
        },
        fr: {
          groupName: 'Troisième Groupe - updated 2',
          description: 'French',
        },
      },
    },
  }

  const updateOperationWithTempId = {
    entity: 'questionGroup',
    op: Operations.update,
    id: 'temp__568708',
    props: {
      questionGroup: {
        randomizationGroup: '',
        gRelevance: '',
      },
      questionGroupL10n: {
        en: {
          groupName: '3rd Group - updated',
          description: 'English',
        },
        fr: {
          groupName: 'Troisième Groupe - updated',
          description: 'French',
        },
      },
    },
  }

  const deleteOperation = {
    entity: 'questionGroup',
    op: Operations.delete,
    id: 7,
  }

  const deleteOperationWithTempId = {
    entity: 'questionGroup',
    op: Operations.delete,
    id: 'temp__568708',
  }

  test('Handles delete operation for a group with a tempId', () => {
    const bufferOperations = [createOperation]
    const result = handleQuestionGroupOperation(
      bufferOperations,
      deleteOperationWithTempId
    )

    expect(result.bufferOperations).toEqual([])
    expect(result.newOperation).toEqual({})
    expect(result.addToBuffer).toBe(false)
  })

  test('Handles create operation when no current operation exists', () => {
    const result = handleQuestionGroupOperation([], createOperation)
    expect(result.bufferOperations).toEqual([])
    expect(result.newOperation).toEqual(createOperation)
    expect(result.addToBuffer).toBe(true)
  })

  test('Handles update operation with an existing current operation', () => {
    const bufferOperations = [updateOperation]
    const result = handleQuestionGroupOperation(
      bufferOperations,
      updateOperation2,
      updateOperation
    )

    expect(result.newOperation).toEqual({
      ...updateOperation,
      props: merge({}, updateOperation.props, updateOperation2.props),
    })
    expect(result.addToBuffer).toBe(false)
  })

  test('Handles update operation with a temporary ID', () => {
    const bufferOperations = [createOperation]

    const result = handleQuestionGroupOperation(
      bufferOperations,
      updateOperationWithTempId,
      createOperation
    )

    expect(result.bufferOperations).toEqual(bufferOperations)
    expect(result.newOperation).toEqual({
      ...createOperation,
      props: merge({}, createOperation.props, updateOperationWithTempId.props),
    })
    expect(result.addToBuffer).toBe(false)
  })

  test('Returns empty buffer and no operation when the operation is null', () => {
    const bufferOperations = []

    const result = handleQuestionGroupOperation(bufferOperations, null)
    expect(result.bufferOperations).toEqual([])
    expect(result.newOperation).toEqual({})
    expect(result.addToBuffer).toBe(false)
  })

  test('Should remove all operations related to a given question group if the operation is delete', () => {
    const bufferOperations = [updateOperation]

    const result = handleQuestionGroupOperation(
      bufferOperations,
      deleteOperation
    )

    expect(result.bufferOperations).toEqual([])
    expect(result.newOperation).toEqual(deleteOperation)
    expect(result.addToBuffer).toBe(true)
  })

  test('Deletes only the specified operation from the bufferOperations', () => {
    const bufferOperations = [createOperation2, createOperation]

    const result = handleQuestionGroupOperation(
      bufferOperations,
      deleteOperationWithTempId
    )

    expect(result.bufferOperations).toEqual([createOperation2])
    expect(result.newOperation).toEqual({})
    expect(result.addToBuffer).toBe(false)
  })
})
