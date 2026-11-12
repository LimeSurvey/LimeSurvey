import { OperationsBuffer } from 'helpers'

import {
  buildConditionScriptOperation,
  buildDeleteScenarioOperation,
  buildDeleteScenarioOperations,
  buildSaveConditionsOperation,
} from './conditionOperations'
import { buildScenarioPayload } from './scenarioPayloadBuilder'

describe('condition designer operations', () => {
  test('deleting a scenario', () => {
    expect(buildDeleteScenarioOperation(20, 2)).toEqual({
      id: 20,
      qid: 20,
      op: 'delete',
      entity: 'questionCondition',
      error: false,
      props: { qid: 20, scenarios: [{ scid: 2, action: 'deleteScenario' }] },
    })
  })

  test('saving an ExpressionScript', () => {
    expect(buildConditionScriptOperation(20, 'Q5 == "A1"')).toEqual({
      id: 20,
      qid: 20,
      op: 'update',
      entity: 'questionCondition',
      error: false,
      props: { qid: 20, action: 'conditionScript', script: 'Q5 == "A1"' },
    })
  })

  test.each([
    ['Question', 'Constant', 'abc', { ConditionConst: 'abc' }],
    ['Question', 'RegExp', '^a', { ConditionRegexp: '^a' }],
    ['Question', 'Question', '@Q6@', { prevQuestionSGQA: '@Q6@' }],
    [
      'Token',
      'Token',
      '{TOKEN:LASTNAME}',
      {
        csrctoken: '{TOKEN:FIRSTNAME}',
        tokenAttr: '{TOKEN:LASTNAME}',
        prevQuestionSGQA: '{TOKEN:FIRSTNAME}',
      },
    ],
  ])(
    'the payload of a %s source and %s target holds the value',
    (sourceType, targetType, value, fields) => {
      const cfieldname = sourceType === 'Token' ? '{TOKEN:FIRSTNAME}' : 'Q5'
      const { scenarios } = buildScenarioPayload(
        { qid: 20 },
        [
          {
            cid: 11,
            qid: 20,
            cqid: 5,
            cfieldname,
            cquestions: cfieldname,
            method: '==',
            value,
            sourceType,
            targetType,
          },
        ],
        1
      )

      expect(scenarios).toEqual([
        {
          scid: 1,
          conditions: [
            {
              cid: 11,
              qid: 20,
              cqid: 5,
              cfieldname,
              cquestions: cfieldname,
              method: '==',
              value,
              scenario: 1,
              action: 'updateCondition',
              editSourceTab:
                sourceType === 'Token' ? '#SRCTOKENATTRS' : '#SRCPREVQUEST',
              editTargetTab: {
                Constant: '#CONST',
                RegExp: '#REGEXP',
                Question: '#PREVQUESTIONS',
                Token: '#TOKENATTRS',
              }[targetType],
              ...fields,
            },
          ],
        },
      ])
    }
  )

  describe('deleting a scenario while saved conditions are being moved', () => {
    const QID = 20
    const savedCondition = (cid, scid, action) => ({
      cid,
      qid: QID,
      cqid: 5,
      cfieldname: 'Q5',
      cquestions: 'Q5',
      method: '==',
      value: 'A1',
      scenario: scid,
      action,
      editSourceTab: '#SRCPREVQUEST',
      editTargetTab: '#CANSWERSTAB',
      canswers: ['A1'],
    })
    // The pending move of a saved condition into scenario `scid`.
    const pendingMove = (cid, scid) =>
      buildSaveConditionsOperation(QID, [
        { scid, conditions: [savedCondition(cid, scid, 'updateCondition')] },
      ])

    const deleteScenario = (buffer, scenario) =>
      buildDeleteScenarioOperations(
        QID,
        scenario,
        buffer.getOperations()
      ).forEach((operation) => buffer.addOperation(operation))

    test('without pending updates, the scenario is deleted by its number', () => {
      expect(
        buildDeleteScenarioOperations(
          QID,
          { scid: 1, conditions: [{ cid: 7 }] },
          []
        )
      ).toEqual([buildDeleteScenarioOperation(QID, 1)])
    })

    test('a condition moved into the deleted scenario is deleted on the server', () => {
      // Condition 7 moved from scenario 2 into scenario 1.
      const buffer = new OperationsBuffer([pendingMove(7, 1)])
      deleteScenario(buffer, { scid: 1, conditions: [{ cid: 7 }] })

      const operations = buffer.getOperations()
      expect(operations).toHaveLength(1)
      expect(operations[0]).toMatchObject({
        id: 7,
        op: 'delete',
        props: {
          scenarios: [
            { scid: 1, conditions: [{ cid: 7, action: 'deleteCondition' }] },
          ],
        },
      })
    })

    test('a condition moved out of the deleted scenario is kept', () => {
      // Condition 8 moved from scenario 1 into scenario 2, condition 9 stays.
      const buffer = new OperationsBuffer([pendingMove(8, 2)])
      deleteScenario(buffer, { scid: 1, conditions: [{ cid: 9 }] })

      const operations = buffer.getOperations()
      const deletes = operations.filter((op) => op.op === 'delete')
      expect(deletes).toHaveLength(1)
      expect(deletes[0].props.scenarios).toEqual([
        { scid: 1, conditions: [{ cid: 9, action: 'deleteCondition' }] },
      ])
      // No scenario-wide delete, and the move of 8 is still sent.
      expect(
        operations.find((op) => op.op === 'update').props.scenarios[0]
          .conditions[0]
      ).toMatchObject({ cid: 8, scenario: 2 })
    })

    test('an unsaved condition in the deleted scenario is just dropped', () => {
      const tempCid = 'temp__1_1'
      const buffer = new OperationsBuffer([
        pendingMove(8, 2),
        buildSaveConditionsOperation(QID, [
          {
            scid: 1,
            conditions: [
              {
                ...savedCondition(tempCid, 1, 'insertCondition'),
                tempId: tempCid,
                tempcids: [tempCid],
              },
            ],
          },
        ]),
      ])
      deleteScenario(buffer, { scid: 1, conditions: [{ cid: tempCid }] })

      const operations = buffer.getOperations()
      expect(operations.filter((op) => op.op === 'delete')).toEqual([])
      expect(JSON.stringify(operations)).not.toContain(tempCid)
    })
  })
})
