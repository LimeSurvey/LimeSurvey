import {
  Entities,
  EntitiesType,
  NEW_OBJECT_ID_PREFIX,
  Operations,
  OperationsBuffer,
  replaceTempIdsInSurveyAndBuffer,
  resolveTempIds,
  removeSurveyConditions,
  setRecordingTempIds,
  DuplicateQuestion,
} from 'helpers'
import {
  isConditionWaitingForTempId,
  isReplacedByNewerScript,
  referencesDeletedTempId,
  removeConditionsWithDeletedTempIds,
  settleTempConditionIds,
} from 'helpers/Buffer/operationsHandlers'
import { buildScenarioPayload } from 'components/ConditionDesigner/utils/scenarioPayloadBuilder'

const tempQid = `${NEW_OBJECT_ID_PREFIX}12`
const otherTempQid = `${NEW_OBJECT_ID_PREFIX}123`
const tempSqid = `${NEW_OBJECT_ID_PREFIX}7`

// A condition on saved question 10, with an unsaved question as source.
const conditionOperation = (cqid, cfieldname) => ({
  id: 10,
  op: Operations.update,
  entity: Entities.questionCondition,
  qid: 10,
  props: {
    qid: 10,
    scenarios: [
      {
        scid: 1,
        conditions: [
          {
            qid: 10,
            cqid,
            cfieldname,
            cquestions: cfieldname,
            method: '==',
            value: 'A1',
            scenario: 1,
            action: 'insertCondition',
            editSourceTab: '#SRCPREVQUEST',
            editTargetTab: '#CANSWERSTAB',
            canswers: ['A1'],
            tempId: `${NEW_OBJECT_ID_PREFIX}99_1`,
            cid: `${NEW_OBJECT_ID_PREFIX}99_1`,
            tempcids: [`${NEW_OBJECT_ID_PREFIX}99_1`],
          },
        ],
      },
    ],
  },
})

describe('Conditions with temp IDs', () => {
  test('a condition waits while its source question has a temp ID', () => {
    const buffer = new OperationsBuffer([
      conditionOperation(tempQid, `Q${tempQid}_S${tempSqid}`),
    ])

    expect(buffer.getOperations({ ready: true })).toHaveLength(0)
  })

  test('a condition with only its own temp condition ID is ready', () => {
    const buffer = new OperationsBuffer([conditionOperation(5, 'Q5_S6')])

    expect(buffer.getOperations({ ready: true })).toHaveLength(1)
  })

  test('saving the source question replaces its temp ID in conditions and fieldnames', () => {
    const survey = {
      questionGroups: [
        {
          gid: 1,
          questions: [
            { qid: tempQid, subquestions: [{ qid: tempSqid }] },
            { qid: otherTempQid },
            {
              qid: 10,
              scenarios: [
                {
                  scid: 1,
                  conditions: [
                    {
                      cid: `${NEW_OBJECT_ID_PREFIX}99_1`,
                      cqid: tempQid,
                      cfieldname: `Q${tempQid}_S${tempSqid}`,
                      value: `@Q${otherTempQid}@`,
                    },
                  ],
                },
              ],
            },
          ],
        },
      ],
    }
    const buffer = new OperationsBuffer([
      conditionOperation(tempQid, `Q${tempQid}_S${tempSqid}`),
    ])

    let result = replaceTempIdsInSurveyAndBuffer(
      [{ tempId: tempQid, id: 55 }],
      EntitiesType.question,
      survey,
      buffer
    )
    result = replaceTempIdsInSurveyAndBuffer(
      [{ tempId: tempSqid, id: 77 }],
      EntitiesType.subquestion,
      result.survey,
      result.operationsBuffer
    )

    const condition =
      result.survey.questionGroups[0].questions[2].scenarios[0].conditions[0]
    expect(condition.cqid).toBe(55)
    expect(condition.cfieldname).toBe('Q55_S77')
    // temp__12 must not match inside temp__123.
    expect(condition.value).toBe(`@Q${otherTempQid}@`)

    const [operation] = result.operationsBuffer.getOperations({ ready: true })
    const bufferedCondition = operation.props.scenarios[0].conditions[0]
    expect(bufferedCondition.cqid).toBe(55)
    expect(bufferedCondition.cfieldname).toBe('Q55_S77')
    expect(bufferedCondition.cquestions).toBe('Q55_S77')
    expect(bufferedCondition.cid).toBe(`${NEW_OBJECT_ID_PREFIX}99_1`)
  })

  test('conditions on a temp question or subquestion deleted before saving are dropped', () => {
    const onDeletedQuestion = conditionOperation(tempQid, `Q${tempQid}`)
    const onDeletedSubquestion = conditionOperation(5, `Q5_S${tempSqid}`)
    onDeletedSubquestion.id = 11
    onDeletedSubquestion.qid = 11
    onDeletedSubquestion.props.qid = 11
    const onSavedQuestion = conditionOperation(6, 'Q6')
    onSavedQuestion.id = 12
    onSavedQuestion.qid = 12
    onSavedQuestion.props.qid = 12

    const operations = [
      onDeletedQuestion,
      onDeletedSubquestion,
      onSavedQuestion,
    ]

    // Nothing is deleted while both still exist.
    expect(
      removeConditionsWithDeletedTempIds(
        operations,
        new Set([tempQid, tempSqid])
      )
    ).toBe(operations)

    expect(removeConditionsWithDeletedTempIds(operations, new Set())).toEqual([
      onSavedQuestion,
    ])
  })

  test('a user value containing temp__ is not taken for an ID', () => {
    const operation = conditionOperation(5, 'Q5')
    Object.assign(operation.props.scenarios[0].conditions[0], {
      editTargetTab: '#CONST',
      value: 'temp__1',
      ConditionConst: 'temp__1',
    })
    delete operation.props.scenarios[0].conditions[0].canswers

    expect(isConditionWaitingForTempId(operation)).toBe(false)
    expect(
      isConditionWaitingForTempId({
        id: 10,
        op: Operations.update,
        entity: Entities.questionCondition,
        qid: 10,
        props: { qid: 10, action: 'conditionScript', script: 'temp__1 == 1' },
      })
    ).toBe(false)
  })

  test('temp IDs of two questions in the same group are both replaced', () => {
    const condition = (cid, cqid) => ({
      cid,
      cqid,
      cfieldname: `Q${cqid}`,
    })
    const survey = {
      questionGroups: [
        {
          gid: 1,
          questions: [
            { qid: tempQid },
            {
              qid: 20,
              scenarios: [{ scid: 1, conditions: [condition(1, tempQid)] }],
            },
            {
              qid: 21,
              scenarios: [{ scid: 1, conditions: [condition(2, tempQid)] }],
            },
          ],
        },
      ],
    }

    const result = replaceTempIdsInSurveyAndBuffer(
      [{ tempId: tempQid, id: 55 }],
      EntitiesType.question,
      survey,
      new OperationsBuffer()
    )

    const [, first, second] = result.survey.questionGroups[0].questions
    expect(first.scenarios[0].conditions[0]).toEqual(condition(1, 55))
    expect(second.scenarios[0].conditions[0]).toEqual(condition(2, 55))
  })

  test('an open form can translate temp IDs that were saved meanwhile', () => {
    // A condition designer panel is open.
    setRecordingTempIds(true)
    const tempQuestion = `${NEW_OBJECT_ID_PREFIX}31`
    const tempCondition = `${NEW_OBJECT_ID_PREFIX}31_1`
    replaceTempIdsInSurveyAndBuffer(
      [{ tempId: tempQuestion, id: 310 }],
      EntitiesType.question,
      { questionGroups: [{ gid: 1, questions: [{ qid: tempQuestion }] }] },
      new OperationsBuffer()
    )
    replaceTempIdsInSurveyAndBuffer(
      [{ tempId: tempCondition, id: 900 }],
      EntitiesType.condition,
      {
        questionGroups: [
          {
            gid: 1,
            questions: [
              {
                qid: 310,
                scenarios: [{ scid: 1, conditions: [{ cid: tempCondition }] }],
              },
            ],
          },
        ],
      },
      new OperationsBuffer()
    )

    expect(resolveTempIds(tempQuestion)).toBe(310)
    expect(resolveTempIds(5)).toBe(5)
    expect(
      resolveTempIds([
        {
          cid: tempCondition,
          qid: 10,
          cqid: tempQuestion,
          cfieldname: `Q${tempQuestion}`,
        },
      ])
    ).toEqual([{ cid: 900, qid: 10, cqid: 310, cfieldname: 'Q310' }])
  })

  describe('editing and deleting conditions with temp IDs', () => {
    const cid1 = `${NEW_OBJECT_ID_PREFIX}99_1`
    const cid2 = `${NEW_OBJECT_ID_PREFIX}99_2`

    const apiCondition = (cid, scid, value, action = 'insertCondition') => ({
      qid: 10,
      cqid: 5,
      cfieldname: 'Q5',
      cquestions: 'Q5',
      method: '==',
      value,
      scenario: scid,
      action,
      editSourceTab: '#SRCPREVQUEST',
      editTargetTab: '#CANSWERSTAB',
      canswers: [value],
      cid,
      ...(action === 'insertCondition' ? { tempId: cid, tempcids: [cid] } : {}),
    })

    const saveOperation = (scid, conditions) => ({
      id: 10,
      op: Operations.update,
      entity: Entities.questionCondition,
      qid: 10,
      props: { qid: 10, scenarios: [{ scid, conditions }] },
    })

    const deleteConditionOperation = (cid, scid) => ({
      id: cid,
      op: Operations.delete,
      entity: Entities.questionCondition,
      qid: 10,
      props: {
        qid: 10,
        scenarios: [{ scid, conditions: [{ cid, action: 'deleteCondition' }] }],
      },
    })

    const bufferedConditions = (buffer) =>
      buffer
        .getOperations()
        .flatMap((operation) => operation.props.scenarios)
        .flatMap((scenario) =>
          (scenario.conditions ?? []).map((condition) => ({
            scid: scenario.scid,
            ...condition,
          }))
        )

    test('new conditions in different scenarios are all kept', () => {
      const buffer = new OperationsBuffer()
      buffer.addOperation(saveOperation(1, [apiCondition(cid1, 1, 'A1')]))
      buffer.addOperation(saveOperation(2, [apiCondition(cid2, 2, 'A2')]))

      expect(
        bufferedConditions(buffer).map(({ scid, cid }) => [scid, cid])
      ).toEqual([
        [1, cid1],
        [2, cid2],
      ])
    })

    test('editing a condition that waits to be inserted updates the insert', () => {
      const buffer = new OperationsBuffer()
      buffer.addOperation(saveOperation(1, [apiCondition(cid1, 1, 'A1')]))
      buffer.addOperation(
        saveOperation(3, [apiCondition(cid1, 3, 'A2', 'updateCondition')])
      )

      const conditions = bufferedConditions(buffer)
      expect(conditions).toHaveLength(1)
      expect(conditions[0]).toMatchObject({
        scid: 3,
        cid: cid1,
        value: 'A2',
        action: 'insertCondition',
        tempcids: [cid1],
      })
      expect(buffer.getOperations({ ready: true })).toHaveLength(1)
    })

    test('deleting a condition that waits to be inserted sends nothing', () => {
      const buffer = new OperationsBuffer()
      buffer.addOperation(
        saveOperation(1, [
          apiCondition(cid1, 1, 'A1'),
          apiCondition(cid2, 1, 'A2'),
        ])
      )
      buffer.addOperation(deleteConditionOperation(cid1, 1))

      expect(buffer.getOperations()).toHaveLength(1)
      expect(bufferedConditions(buffer).map(({ cid }) => cid)).toEqual([cid2])
    })

    test('deleting a scenario only drops the pending conditions of that scenario', () => {
      const buffer = new OperationsBuffer()
      buffer.addOperation(saveOperation(1, [apiCondition(cid1, 1, 'A1')]))
      buffer.addOperation(saveOperation(2, [apiCondition(cid2, 2, 'A2')]))
      buffer.addOperation({
        id: 10,
        op: Operations.delete,
        entity: Entities.questionCondition,
        qid: 10,
        props: { qid: 10, scenarios: [{ scid: 1, action: 'deleteScenario' }] },
      })

      expect(bufferedConditions(buffer).map(({ cid }) => cid)).toEqual([cid2])
      // The question is saved, so the scenario delete is still sent.
      expect(
        buffer.getOperations().filter((op) => op.op === Operations.delete)
      ).toHaveLength(1)
    })

    test('editing or deleting a condition whose insert is being saved waits for its real ID', () => {
      // The insert was already sent, so it isn't in the buffer anymore.
      const buffer = new OperationsBuffer()
      buffer.addOperation(
        saveOperation(1, [apiCondition(cid1, 1, 'A2', 'updateCondition')])
      )
      buffer.addOperation(deleteConditionOperation(cid2, 1))

      expect(buffer.getOperations({ ready: true })).toHaveLength(0)

      const survey = {
        questionGroups: [
          {
            gid: 1,
            questions: [
              {
                qid: 10,
                scenarios: [
                  {
                    scid: 1,
                    conditions: [{ cid: cid1 }, { cid: cid2 }],
                  },
                ],
              },
            ],
          },
        ],
      }
      const result = replaceTempIdsInSurveyAndBuffer(
        [
          { tempId: cid1, id: 501 },
          { tempId: cid2, id: 502 },
        ],
        EntitiesType.condition,
        survey,
        buffer
      )

      const ready = result.operationsBuffer.getOperations({ ready: true })
      expect(ready).toHaveLength(1)
      expect(ready[0].op).toBe(Operations.delete)
      expect(ready[0].props.scenarios[0].conditions[0].cid).toBe(502)
      expect(
        result.operationsBuffer.getOperations()[0].props.scenarios[0]
          .conditions[0].cid
      ).toBe(501)
    })
  })

  describe('ExpressionScript and conditions of the same question', () => {
    const script = (text) => ({
      id: 10,
      op: Operations.update,
      entity: Entities.questionCondition,
      qid: 10,
      props: { qid: 10, action: 'conditionScript', script: text },
    })

    test('a script replaces the pending condition saves of the question', () => {
      const buffer = new OperationsBuffer()
      buffer.addOperation(conditionOperation(5, 'Q5'))
      buffer.addOperation(script('Q5 == "A1"'))

      expect(buffer.getOperations()).toEqual([
        expect.objectContaining({
          props: { qid: 10, action: 'conditionScript', script: 'Q5 == "A1"' },
        }),
      ])
    })

    test('conditions saved after a pending script are sent after it, not merged into it', () => {
      const buffer = new OperationsBuffer()
      buffer.addOperation(script('Q5 == "A1"'))
      buffer.addOperation(conditionOperation(5, 'Q5'))

      const operations = buffer.getOperations()
      expect(operations).toHaveLength(2)
      expect(operations[0].props).toEqual({
        qid: 10,
        action: 'conditionScript',
        script: 'Q5 == "A1"',
      })
      expect(operations[1].props.action).toBeUndefined()
      expect(operations[1].props.scenarios[0].conditions).toHaveLength(1)

      // A later edit of that condition still goes into its own save.
      const edited = conditionOperation(5, 'Q5')
      const condition = edited.props.scenarios[0].conditions[0]
      Object.assign(condition, {
        cid: operations[1].props.scenarios[0].conditions[0].cid,
        action: 'updateCondition',
        value: 'A2',
        canswers: ['A2'],
      })
      delete condition.tempId
      delete condition.tempcids
      buffer.addOperation(edited)

      const [first, second] = buffer.getOperations()
      expect(first.props.action).toBe('conditionScript')
      expect(second.props.scenarios[0].conditions).toEqual([
        expect.objectContaining({ action: 'insertCondition', value: 'A2' }),
      ])
    })
  })

  describe('a failed condition insert', () => {
    const cid = `${NEW_OBJECT_ID_PREFIX}55_1`

    const insert = (value) => {
      const operation = conditionOperation(5, 'Q5')
      Object.assign(operation.props.scenarios[0].conditions[0], {
        cid,
        tempId: cid,
        tempcids: [cid],
        value,
        canswers: [value],
      })
      return operation
    }

    const edit = (value) => {
      const operation = insert(value)
      const condition = operation.props.scenarios[0].conditions[0]
      condition.action = 'updateCondition'
      delete condition.tempId
      delete condition.tempcids
      return operation
    }

    const deleteOperation = () => ({
      id: cid,
      op: Operations.delete,
      entity: Entities.questionCondition,
      qid: 10,
      props: {
        qid: 10,
        scenarios: [
          { scid: 1, conditions: [{ cid, action: 'deleteCondition' }] },
        ],
      },
    })

    const conditionsOf = (operations) =>
      operations
        .flatMap((operation) => operation.props?.scenarios ?? [])
        .flatMap((scenario) => scenario.conditions ?? [])

    // The insert was sent, then edited or deleted while being saved.
    const bufferWhileSaving = (...operations) => {
      const buffer = new OperationsBuffer()
      operations.forEach((operation) => buffer.addOperation(operation))
      return buffer
    }

    test('rejected and put back, with a waiting delete: both are dropped', () => {
      const buffer = bufferWhileSaving(deleteOperation())
      buffer.addOperation({ ...insert('A1'), error: true }, false, true)

      expect(conditionsOf(buffer.getOperations())).toEqual([])
    })

    test('rejected and put back, with a waiting edit: the edit is sent as the insert', () => {
      const buffer = bufferWhileSaving(edit('A2'))
      buffer.addOperation({ ...insert('A1'), error: true }, false, true)

      const ready = buffer.getOperations({ ready: true })
      expect(conditionsOf(ready)).toEqual([
        expect.objectContaining({
          cid,
          value: 'A2',
          action: 'insertCondition',
          tempcids: [cid],
        }),
      ])
      expect(conditionsOf(buffer.getOperations())).toHaveLength(1)
    })

    test('rejected and put back without an edit or delete: kept with its error', () => {
      const buffer = new OperationsBuffer()
      buffer.addOperation({ ...insert('A1'), error: true }, false, true)

      expect(buffer.getOperations()).toEqual([
        expect.objectContaining({ error: true }),
      ])
      expect(buffer.getOperations({ ready: true })).toEqual([])
    })

    test('rejected and not put back, with a waiting delete: the delete is dropped', () => {
      const other = conditionOperation(6, 'Q6')
      other.id = 11
      other.qid = 11
      other.props.qid = 11
      const operations = settleTempConditionIds(
        [deleteOperation(), other],
        [cid]
      )

      expect(operations).toEqual([other])
      // Nothing holds up the other save anymore.
      expect(
        new OperationsBuffer(operations).getOperations({ ready: true })
      ).toHaveLength(1)
    })

    test('rejected and not put back, with a waiting edit: the edit becomes the insert', () => {
      const [operation] = settleTempConditionIds([edit('A2')], [cid])

      expect(conditionsOf([operation])).toEqual([
        expect.objectContaining({
          cid,
          value: 'A2',
          action: 'insertCondition',
          tempId: cid,
          tempcids: [cid],
        }),
      ])
    })

    test('network failure, with a waiting delete: both are dropped, no deadlock', () => {
      const buffer = bufferWhileSaving(deleteOperation())
      buffer.addOperation({ ...insert('A1'), error: true }, false)

      expect(conditionsOf(buffer.getOperations())).toEqual([])
    })

    test('a normal save is left as it is', () => {
      const pending = insert('A1')
      const saved = conditionOperation(5, 'Q5')
      Object.assign(saved.props.scenarios[0].conditions[0], {
        cid: 300,
        action: 'updateCondition',
      })
      saved.id = 11
      const operations = [pending, saved]

      expect(settleTempConditionIds(operations)).toBe(operations)
    })
  })

  describe('review fixes', () => {
    const deleteScenario = (scid) => ({
      id: 10,
      op: Operations.delete,
      entity: Entities.questionCondition,
      qid: 10,
      props: { qid: 10, scenarios: [{ scid, action: 'deleteScenario' }] },
    })

    test('deleting two scenarios before a save deletes both', () => {
      const buffer = new OperationsBuffer()
      buffer.addOperation(deleteScenario(1))
      buffer.addOperation(deleteScenario(2))

      const deletes = buffer.getOperations()
      expect(deletes).toHaveLength(1)
      expect(deletes[0].props.scenarios).toEqual([
        { scid: 1, action: 'deleteScenario' },
        { scid: 2, action: 'deleteScenario' },
      ])
    })

    const script = (text, extra = {}) => ({
      id: 10,
      op: Operations.update,
      entity: Entities.questionCondition,
      qid: 10,
      props: { qid: 10, action: 'conditionScript', script: text },
      ...extra,
    })

    test('a retried script keeps the conditions saved after it, and goes first', () => {
      const buffer = new OperationsBuffer()
      buffer.addOperation(conditionOperation(5, 'Q5'))
      buffer.addOperation(script('Q5 == "A1"', { error: true }), false)

      const [first, second] = buffer.getOperations()
      expect(first.props.action).toBe('conditionScript')
      expect(first.error).toBe(false)
      expect(second.props.scenarios[0].conditions).toHaveLength(1)
    })

    test('a refused script is put back with its error', () => {
      const buffer = new OperationsBuffer()
      buffer.addOperation(conditionOperation(5, 'Q5'))
      buffer.addOperation(script('Q5 == "A1"', { error: true }), false, true)

      expect(buffer.getOperations()[0].error).toBe(true)
    })

    test('a retried script is dropped when a newer script is pending', () => {
      const buffer = new OperationsBuffer()
      buffer.addOperation(script('Q5 == "A2"'))
      buffer.addOperation(script('Q5 == "A1"', { error: true }), false)

      expect(buffer.getOperations()).toEqual([
        expect.objectContaining({
          props: expect.objectContaining({ script: 'Q5 == "A2"' }),
        }),
      ])
    })

    test('an edit that moved the condition keeps its scenario when the insert comes back', () => {
      const cid = `${NEW_OBJECT_ID_PREFIX}66_1`
      const insert = conditionOperation(5, 'Q5')
      Object.assign(insert.props.scenarios[0].conditions[0], {
        cid,
        tempId: cid,
        tempcids: [cid],
      })
      const edit = conditionOperation(5, 'Q5')
      edit.props.scenarios[0].scid = 2
      Object.assign(edit.props.scenarios[0].conditions[0], {
        cid,
        scenario: 2,
        action: 'updateCondition',
      })
      delete edit.props.scenarios[0].conditions[0].tempId
      delete edit.props.scenarios[0].conditions[0].tempcids

      const buffer = new OperationsBuffer()
      buffer.addOperation(edit)
      buffer.addOperation({ ...insert, error: true }, false, true)

      const [operation] = buffer.getOperations()
      expect(operation.props.scenarios).toEqual([
        {
          scid: 2,
          conditions: [
            expect.objectContaining({
              cid,
              scenario: 2,
              action: 'insertCondition',
            }),
          ],
        },
      ])
    })

    test('a duplicated question does not copy the conditions of the original', () => {
      const duplicate = DuplicateQuestion({
        qid: 10,
        relevance: '((Q5 == "A1"))',
        scenarios: [{ scid: 1, conditions: [{ cid: 300, qid: 10 }] }],
        answers: [],
        subquestions: [],
      })

      expect(duplicate.scenarios).toEqual([])
      expect(duplicate.relevance).toBe('((Q5 == "A1"))')
    })
  })

  describe('second review fixes', () => {
    const script = {
      id: 10,
      op: Operations.update,
      entity: Entities.questionCondition,
      qid: 10,
      props: { qid: 10, action: 'conditionScript', script: 'Q5 == "A1"' },
    }

    test('a resent condition save is obsolete only if a newer script replaced it', () => {
      const save = conditionOperation(5, 'Q5')

      // The script was saved while the request ran.
      expect(isReplacedByNewerScript(save, [script], [save])).toBe(true)
      // The script was part of the failed request: The save is newer.
      expect(isReplacedByNewerScript(save, [script], [script, save])).toBe(
        false
      )
      expect(isReplacedByNewerScript(save, [], [save])).toBe(false)
      expect(isReplacedByNewerScript(script, [script], [script])).toBe(false)
    })

    test('conditions removed from the survey reset the relevance when none are left', () => {
      const survey = {
        questionGroups: [
          {
            gid: 1,
            questions: [
              {
                qid: 10,
                relevance: '((Q5 == "A1"))',
                scenarios: [{ scid: 1, conditions: [{ cid: 'x' }] }],
              },
              {
                qid: 11,
                relevance: '((Q5 == "A1") or (Q5 == "A2"))',
                scenarios: [
                  { scid: 1, conditions: [{ cid: 'y' }, { cid: 1 }] },
                ],
              },
            ],
          },
        ],
      }

      expect(removeSurveyConditions(survey, () => false)).toBe(survey)

      const [first, second] = removeSurveyConditions(survey, (condition) =>
        ['x', 'y'].includes(condition.cid)
      ).questionGroups[0].questions
      expect(first.scenarios).toEqual([])
      expect(first.relevance).toBe('1')
      expect(second.scenarios[0].conditions).toEqual([{ cid: 1 }])
      expect(second.relevance).toBe('((Q5 == "A1") or (Q5 == "A2"))')
    })

    test('a condition on a deleted temp question is recognised', () => {
      const condition = { qid: 10, cqid: 5, cfieldname: `Q5_S${tempSqid}` }

      expect(referencesDeletedTempId(condition, new Set([tempSqid]))).toBe(
        false
      )
      expect(referencesDeletedTempId(condition, new Set())).toBe(true)
      expect(
        referencesDeletedTempId(
          { qid: 10, cqid: 5, cfieldname: 'Q5' },
          new Set()
        )
      ).toBe(false)
    })

    test('replaced temp IDs of groups are not recorded for the condition designer', () => {
      const tempGid = `${NEW_OBJECT_ID_PREFIX}4242`
      replaceTempIdsInSurveyAndBuffer(
        [{ tempId: tempGid, id: 9 }],
        EntitiesType.group,
        { questionGroups: [{ gid: tempGid, questions: [] }] },
        new OperationsBuffer()
      )

      expect(resolveTempIds(tempGid)).toBe(tempGid)
    })
  })

  describe('third review fixes', () => {
    test('a condition deleted while its insert is being saved gets its delete sent', () => {
      const cid = `${NEW_OBJECT_ID_PREFIX}88_1`
      const buffer = new OperationsBuffer()
      buffer.addOperation({
        id: cid,
        op: Operations.delete,
        entity: Entities.questionCondition,
        qid: 10,
        props: {
          qid: 10,
          scenarios: [
            { scid: 1, conditions: [{ cid, action: 'deleteCondition' }] },
          ],
        },
      })
      expect(buffer.getOperations({ ready: true })).toEqual([])

      // The condition is already gone from the question.
      const survey = {
        questionGroups: [{ gid: 1, questions: [{ qid: 10, scenarios: [] }] }],
      }
      const result = replaceTempIdsInSurveyAndBuffer(
        [{ tempId: cid, id: 501 }],
        EntitiesType.condition,
        survey,
        buffer
      )

      const [ready] = result.operationsBuffer.getOperations({ ready: true })
      expect(ready.id).toBe(501)
      expect(ready.props.scenarios[0].conditions[0].cid).toBe(501)
    })

    test('the ID swap leaves user values alone', () => {
      const userValue = `Q${tempQid}`
      const operation = conditionOperation(tempQid, `Q${tempQid}`)
      Object.assign(operation.props.scenarios[0].conditions[0], {
        editTargetTab: '#CONST',
        value: userValue,
        ConditionConst: userValue,
      })
      delete operation.props.scenarios[0].conditions[0].canswers

      const result = replaceTempIdsInSurveyAndBuffer(
        [{ tempId: tempQid, id: 55 }],
        EntitiesType.question,
        { questionGroups: [{ gid: 1, questions: [{ qid: tempQid }] }] },
        new OperationsBuffer([operation])
      )

      const [condition] =
        result.operationsBuffer.getOperations()[0].props.scenarios[0].conditions
      expect(condition.cqid).toBe(55)
      expect(condition.cfieldname).toBe('Q55')
      expect(condition.value).toBe(userValue)
      expect(condition.ConditionConst).toBe(userValue)
    })
  })

  describe('fourth review fixes', () => {
    test('temp condition IDs of two saves never repeat', () => {
      const condition = {
        qid: 10,
        cqid: 5,
        cfieldname: 'Q5',
        cquestions: 'Q5',
        value: 'A1',
        sourceType: 'Question',
        targetType: 'Answer',
      }
      const first = buildScenarioPayload({ qid: 10 }, [condition], 1)
      const second = buildScenarioPayload({ qid: 10 }, [condition], 1)

      const cidOf = (payload) => payload.scenarios[0].conditions[0].cid
      expect(cidOf(first)).toMatch(/^temp__\d+_\d+$/)
      expect(cidOf(first)).not.toBe(cidOf(second))
    })

    test('replaced temp IDs are only recorded while a panel is open', () => {
      const tempId = `${NEW_OBJECT_ID_PREFIX}7070`
      const map = (id) =>
        replaceTempIdsInSurveyAndBuffer(
          [{ tempId, id }],
          EntitiesType.question,
          { questionGroups: [{ gid: 1, questions: [{ qid: tempId }] }] },
          new OperationsBuffer()
        )

      setRecordingTempIds(false)
      map(70)
      expect(resolveTempIds(tempId)).toBe(tempId)

      setRecordingTempIds(true)
      map(71)
      expect(resolveTempIds(tempId)).toBe(71)

      setRecordingTempIds(false)
      expect(resolveTempIds(tempId)).toBe(tempId)
    })

    test('a scenario delete sent again keeps the conditions saved since', () => {
      const buffer = new OperationsBuffer()
      const pendingInsert = conditionOperation(5, 'Q5')
      buffer.addOperation(pendingInsert)
      buffer.addOperation(
        {
          id: 10,
          op: Operations.delete,
          entity: Entities.questionCondition,
          qid: 10,
          error: true,
          props: {
            qid: 10,
            scenarios: [{ scid: 1, action: 'deleteScenario' }],
          },
        },
        false
      )

      const saves = buffer
        .getOperations()
        .filter((operation) => operation.op === Operations.update)
      expect(saves).toHaveLength(1)
      expect(saves[0].props.scenarios[0].conditions).toHaveLength(1)
    })
  })
})
