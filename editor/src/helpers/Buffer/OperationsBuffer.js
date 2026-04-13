import { cloneDeep, merge } from 'lodash'

import {
  arrayDeleteItem,
  filterArray,
  filterCollection,
  hasTempId,
} from 'helpers'

import { Entities, getOperationScheme, Operations } from './'
import {
  handleAnswerOperation,
  handleQuestionGroupOperation,
  handleQuestionOperation,
  handleSubquestionOperation,
  handleSurveyOperation,
  handleQuestionConditionOperation,
} from './operationsHandlers'
import { addBreadcrumb, reportExtras } from 'appInstrumentation'

export class OperationsBuffer {
  /**
   * Information on how to handle the operations can be found in the backend in the following directory:
   * ls-ce\public\application\libraries\Api\Command\V1\SurveyPatch
   * A operation should be constructed according to the info in the directory mentioned above.
   */
  operations = []

  // Buffer hash is used to check if the buffer has changed since the last save
  bufferHash = ''

  constructor(operations = [], hash = '') {
    this.operations = cloneDeep(operations)
    this.bufferHash = hash
  }

  getOperations = ({ ready } = {}) => {
    const includeReady = ready === undefined || ready === true
    const includeNotReady = ready === undefined || ready === false
    const includeAll = includeReady && includeNotReady

    const allOperations = cloneDeep(this.operations)

    const hasCreateOp = allOperations.some(
      (operation) => operation.op === Operations.create
    )
    const hasDeletOp = allOperations.some(
      (operation) => operation.op === Operations.delete
    )

    return includeAll
      ? allOperations
      : filterArray(allOperations, (operation) => {
          const result = this.isOperationReadyForPatch(
            operation,
            hasCreateOp,
            hasDeletOp
          )

          return includeReady === true ? result : !result
        }).validArray
  }

  setBufferHash = (hash) => {
    this.bufferHash = hash
  }

  /**
   * Adds a new operation to the buffer or updates an existing one.
   *
   * @param {Object} operation - The operation to be added or updated.
   * @param {boolean} [updateCurrentOperation=true] - Whether to update an existing operation if found.
   * @param {boolean} [restore=false] - Whether this operation is being restored.
   *
   * This method handles various types of operations (survey, question group, question, answer, subquestion)
   * and manages the buffer accordingly. It includes logic for validating operations, handling deletions,
   * and merging properties for existing operations.
   *
   *  todo: if the user is trying to delete a question group with a tempId
   * - then loop over the questions with that id and remove the operations
   */
  addOperation = (
    operation,
    updateCurrentOperation = true,
    restore = false
  ) => {
    const operationScheme = getOperationScheme(operation.op, operation.entity)

    if (!operationScheme) {
      addBreadcrumb({
        category: 'OperationsBuffer',
        message: `Invalid operation: ${operation.op} , entity: ${operation.entity}`,
        data: {
          operation: operation,
        },
        level: 'warning',
      })
      // eslint-disable-next-line no-console
      console.error('Invalid operation', { operation })
    } else {
      const validateResult = operationScheme.validate(operation)
      if (validateResult.error) {
        addBreadcrumb({
          category: 'OperationsBuffer',
          message: `Invalid operation: ${operation.op} , entity: ${operation.entity}`,
          data: {
            operation: operation,
            error: validateResult.error,
          },
          level: 'warning',
        })
        // eslint-disable-next-line no-console
        console.error(validateResult.error)
        operation = operationScheme.validate(operation, {
          stripUnknown: true,
        }).value
      }
    }

    let { id, op, entity, props, error = false } = operation

    // Look if current operation exists in the buffer.
    let operationIndex = this.findIndex(id, op, entity)
    const currentOperation = this.operations[operationIndex]

    delete currentOperation?.error

    const isSurveyOperation = [
      Entities.languageSetting,
      Entities.survey,
      Entities.surveyStatus,
    ].includes(entity)
    const isQuestionGroupOperation = [
      Entities.questionGroup,
      Entities.questionGroupL10n,
    ].includes(entity)
    const isQuestionOperation = [
      Entities.question,
      Entities.questionL10n,
      Entities.questionAttribute,
    ].includes(entity)
    const isAnswerOperation = entity === Entities.answer
    const isSubquestionOperation = entity === Entities.subquestion
    const isQuestionConditionOperation = entity === Entities.questionCondition

    let result
    if (isSurveyOperation) {
      result = handleSurveyOperation(
        this.operations,
        operation,
        currentOperation
      )
    } else if (isQuestionGroupOperation) {
      result = handleQuestionGroupOperation(
        this.operations,
        operation,
        currentOperation
      )
    } else if (isQuestionOperation) {
      result = handleQuestionOperation(
        this.operations,
        operation,
        currentOperation
      )
    } else if (isAnswerOperation) {
      const newQuestionIndex = this.findIndex(
        id,
        Operations.create,
        Entities.question
      )
      const newQuestion = this.operations[newQuestionIndex]

      if (newQuestionIndex !== -1) {
        operationIndex = newQuestionIndex
      }

      result = handleAnswerOperation(
        this.operations,
        operation,
        currentOperation,
        newQuestion
      )
    } else if (isSubquestionOperation) {
      const newQuestionIndex = this.findIndex(
        id,
        Operations.create,
        Entities.question
      )
      const newQuestion = this.operations[newQuestionIndex]

      if (newQuestionIndex !== -1) {
        operationIndex = newQuestionIndex
      }

      result = handleSubquestionOperation(
        this.operations,
        operation,
        currentOperation,
        newQuestion
      )
    } else if (isQuestionConditionOperation) {
      result = handleQuestionConditionOperation(
        this.operations,
        operation,
        currentOperation
      )
    }

    if (result) {
      if (!restore) delete result.newOperation.error
      this.operations = result.bufferOperations

      if (result.addToBuffer) {
        this.operations.push(result.newOperation)
      } else if (
        (result.newOperation.id || isSurveyOperation) &&
        operationIndex !== -1
      ) {
        this.operations[operationIndex] = result.newOperation
      }

      this.setOperations(this.operations)
      return
    }
    reportExtras({
      extraData: {
        operation,
        currentOperation,
      },
      message: 'Reverting to default addOperation handling',
      level: 'debug',
    })

    const isNewEntity = hasTempId(operation.id?.toString())
    const entityisAnswerOrSubquestion =
      entity === Entities.answer || entity === Entities.subquestion

    // if (entityisAnswerOrSubquestion) {
    //   this.handleAnswersAndSubquestionOperations(
    //     operation,
    //     updateCurrentOperation
    //   )
    //   return
    // }

    // If the user is updating an entity which is waiting to be created,
    // - we update the "create" operation and discard the "update" operation
    if (
      isNewEntity &&
      operation.op !== Operations.create &&
      entityisAnswerOrSubquestion
    ) {
      const newOperationIndex = this.findIndex(id, Operations.create, entity)
      const newOperation = this.operations[newOperationIndex]

      if (newOperation && op === Operations.update) {
        merge(newOperation.props, props)
        return
      }
    }

    if (op === Operations.delete) {
      // remove all the operations that related to that entity.
      // since the entity will be deleted anyways.
      this.operations = this.operations.filter(
        (operation) => operation.entity != entity || operation.id != id
      )

      if (entity === Entities.questionGroup) {
        this.operations = this.operations.filter(
          (operation) => operation.props?.question?.gid !== id
        )
      }

      // if the user is trying to delete an answer or a subquestion,
      // - then we also remove all the operations that depend on that entity.
      if (entity === Entities.answer) {
        this.operations.forEach((operation) => {
          if (operation.entity === Entities.answer && operation.props) {
            operation.props = filterCollection(
              operation?.props,
              (answer) => answer?.aid !== id
            )
          }
        })
      } else if (entity === Entities.subquestion) {
        this.operations.forEach((operation) => {
          if (operation.entity === Entities.subquestion && operation.props) {
            operation.props = filterCollection(
              operation?.props,
              (subquestion) => subquestion?.qid !== id
            )
          }
        })
      }

      // remove the operations with no props if they are not a delete operation.
      this.operations = this.operations.filter(
        (operation) =>
          operation.op === Operations.delete ||
          operation?.props?.length ||
          Object.keys(operation?.props || {})?.length
      )

      // if we are trying to delete a newly created entity (has a tempId),
      // then we basically just return and we don't add any operations.
      if (isNewEntity) {
        return
      }
    }

    if (operationIndex === -1 || !updateCurrentOperation) {
      this.operations.push({ id, op, entity, error, props })
    } else if (
      updateCurrentOperation &&
      this.operations[operationIndex] !== undefined &&
      this.operations[operationIndex]?.props
    ) {
      if (entityisAnswerOrSubquestion) {
        this.operations[operationIndex].props = props
      } else {
        merge(this.operations[operationIndex].props, props)
      }
      if (!restore) delete this.operations[operationIndex].error
    }
  }

  setOperations = (operations) => {
    this.operations = operations
  }

  clearBuffer = ({ ready } = {}) => {
    this.setOperations(
      ready === undefined
        ? []
        : this.getOperations({
            ready: !ready,
          })
    )
  }

  removeOperation = (id, op, entity) => {
    const operationIndex = this.findIndex(id, op, entity)
    this.operations = arrayDeleteItem(this.operations, operationIndex)[0]

    return operationIndex !== -1
  }

  findIndex = (id, op, entity) => {
    return this.operations.findIndex((item) => {
      return (
        item.id == id &&
        (op === undefined || item.op === op) && // Check item.op === op only if op is defined
        (entity === undefined || item.entity === entity)
      ) // Check item.entity === entity only if entity is defined
    })
  }

  getOperation = (id, op, entity) => {
    const index = this.findIndex(id, op, entity)
    return index !== -1 ? this.operations[index] : undefined
  }

  isEmpty = () => {
    return this.operations.length === 0
  }

  isCreateOp(data) {
    return data?.entity && data?.op === Operations.create
  }

  isOperationReadyForPatch(operation, hasCreateOp, hasDeletOp) {
    // Perform create and delete operations before other operations
    if (operation.op !== Operations.create && hasCreateOp) {
      return false
    } else if (operation.op !== Operations.delete && hasDeletOp) {
      return false
    }

    const isQuestionGroupHasATempIdAndOpIsNotGroupCreate =
      hasTempId(operation.props, 'gid') &&
      operation.op !== Operations.create &&
      operation.entity === Entities.questionGroup

    // When duplicating a group questions are added to the buffer
    // - with temporary group id. These operations shoulbe be delayed
    // - until the group temp id has been replaced with the actual group id
    const isAddingQuestionAndQuestionHasAGroupTempId =
      operation.entity === Entities.question &&
      operation.op === Operations.create &&
      operation.props &&
      hasTempId(operation.props.question, 'gid')

    const isAddingSubquestionAndQuestionHasATempId =
      operation.entity === Entities.subquestion &&
      operation.props &&
      hasTempId(operation.props[0], 'parentQid')

    const isAddingAnswerAndQuestionHasATempid =
      operation.entity === Entities.answer &&
      operation.props &&
      hasTempId(operation.props[0], 'qid')

    if (
      isQuestionGroupHasATempIdAndOpIsNotGroupCreate ||
      isAddingQuestionAndQuestionHasAGroupTempId ||
      isAddingSubquestionAndQuestionHasATempId ||
      isAddingAnswerAndQuestionHasATempid ||
      operation.error
    ) {
      return false
    }

    return true
  }
}
