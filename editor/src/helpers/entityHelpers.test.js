import {
  Entities,
  Operations,
  OperationsBuffer,
  processValidationErrorsAndUpdateOperations,
} from 'helpers'

const conditionOperation = (qid, cid) => ({
  id: qid,
  qid,
  entity: Entities.questionCondition,
  op: Operations.update,
  props: {
    qid,
    scenarios: [
      {
        scid: 1,
        conditions: [
          {
            qid,
            cqid: 4,
            cfieldname: 'Q4',
            cquestions: 'Q4',
            method: '==',
            value: 'A1',
            scenario: 1,
            action: 'insertCondition',
            editSourceTab: '#SRCPREVQUEST',
            editTargetTab: '#CONST',
            ConditionConst: 'A1',
            tempId: cid,
            cid,
            tempcids: [cid],
          },
        ],
      },
    ],
  },
})

const refusal = (qid) => ({
  id: qid,
  op: Operations.update,
  entity: Entities.questionCondition,
  error: 'Cannot create condition',
})

describe('processValidationErrorsAndUpdateOperations', () => {
  test('keeps the error of a refused condition operation when another condition of the question is buffered', () => {
    const sent = conditionOperation(108, 'temp__1_1')
    const newer = conditionOperation(108, 'temp__2_1')
    const buffer = new OperationsBuffer([newer])

    const errors = processValidationErrorsAndUpdateOperations(
      [refusal(108)],
      new OperationsBuffer([sent]),
      buffer
    )

    expect(errors).toEqual([refusal(108)])
    // The newer change is still sent, without the refused one.
    expect(buffer.getOperations()).toEqual([newer])
  })

  test('does not put a refused condition operation back in the buffer', () => {
    const buffer = new OperationsBuffer([])

    const errors = processValidationErrorsAndUpdateOperations(
      [refusal(108)],
      new OperationsBuffer([conditionOperation(108, 'temp__1_1')]),
      buffer
    )

    expect(errors).toHaveLength(1)
    expect(buffer.getOperations()).toEqual([])
  })

  test('still drops the error of another entity changed again since', () => {
    const questionUpdate = {
      id: 5,
      entity: Entities.question,
      op: Operations.update,
      props: { question: { qid: 5, title: 'Q5' } },
    }
    const buffer = new OperationsBuffer([questionUpdate])

    const errors = processValidationErrorsAndUpdateOperations(
      [{ id: 5, op: Operations.update, entity: Entities.question }],
      new OperationsBuffer([questionUpdate]),
      buffer
    )

    expect(errors).toEqual([])
  })
})
