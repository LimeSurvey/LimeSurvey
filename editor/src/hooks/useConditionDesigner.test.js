import { renderHook } from '@testing-library/react'

import {
  EntitiesType,
  OperationsBuffer,
  replaceTempIdsInSurveyAndBuffer,
  setRecordingTempIds,
} from 'helpers'
import { errorToast } from 'helpers/Alert'
import { showSuccessMessage } from '../components/ConditionDesigner/utils'

import { useConditionDesigner } from './useConditionDesigner'

jest.mock('helpers/Alert', () => ({
  ...jest.requireActual('helpers/Alert'),
  errorToast: jest.fn(),
}))
jest.mock(
  '../components/ConditionDesigner/utils/conditionAlertHelpers',
  () => ({
    ...jest.requireActual(
      '../components/ConditionDesigner/utils/conditionAlertHelpers'
    ),
    showSuccessMessage: jest.fn(),
  })
)

const QID = 20
const TEMP_CID = expect.stringMatching(/^temp__\d+_\d+$/)

// A condition as the condition designer form holds it.
const formCondition = (value, extra = {}) => ({
  qid: QID,
  cqid: 5,
  cfieldname: 'Q5',
  cquestions: 'Q5',
  method: '==',
  value,
  scenario: 1,
  answers: [],
  sourceType: 'Question',
  targetType: 'Answer',
  isDeleted: false,
  isUpdated: false,
  ...extra,
})

// A condition as saved on the question.
const savedCondition = (cid, value) => ({
  cid,
  qid: QID,
  cqid: 5,
  cfieldname: 'Q5',
  method: '==',
  value,
  scenario: 1,
})

const setup = ({ conditions, scenarioToPatch = null, scenarios = [] }) => {
  const question = { qid: QID, relevance: '1', scenarios }
  const survey = { questionGroups: [{ gid: 1, questions: [question] }] }
  const focused = { qid: QID, relevance: '1', scenarios }
  const addToBuffer = jest.fn()
  const update = jest.fn()
  const onNavigateBack = jest.fn()

  const { result } = renderHook(() =>
    useConditionDesigner({
      survey,
      question,
      focused,
      scid: 1,
      scenarioToPatch,
      groupIndex: 0,
      questionIndex: 0,
      conditions,
      addToBuffer,
      setScid: jest.fn(),
      setConditions: jest.fn(),
      update,
      onNavigateBack,
    })
  )

  result.current.handleSavingScenario()

  return { question, focused, addToBuffer, update, onNavigateBack }
}

describe('useConditionDesigner: saving a scenario', () => {
  beforeEach(() => {
    jest.clearAllMocks()
  })

  test('a new scenario inserts each value as its own condition', () => {
    const { question, addToBuffer, onNavigateBack } = setup({
      conditions: [formCondition('A1,A2'), formCondition('A3')],
    })

    expect(addToBuffer).toHaveBeenCalledTimes(1)
    const [operation] = addToBuffer.mock.calls[0]
    expect(operation).toMatchObject({
      id: QID,
      qid: QID,
      op: 'update',
      entity: 'questionCondition',
      props: {
        qid: QID,
        scenarios: [
          {
            scid: 1,
            conditions: ['A1', 'A2', 'A3'].map((value) => ({
              action: 'insertCondition',
              qid: QID,
              cqid: 5,
              cfieldname: 'Q5',
              method: '==',
              value,
              scenario: 1,
              editSourceTab: '#SRCPREVQUEST',
              editTargetTab: '#CANSWERSTAB',
              canswers: [value],
              cid: TEMP_CID,
              tempId: TEMP_CID,
            })),
          },
        ],
      },
    })
    expect(question.scenarios).toHaveLength(1)
    expect(question.scenarios[0].conditions.map((c) => c.value)).toEqual([
      'A1',
      'A2',
      'A3',
    ])
    expect(showSuccessMessage).toHaveBeenCalledTimes(1)
    expect(onNavigateBack).toHaveBeenCalledTimes(1)
  })

  test('editing a scenario sends deletes, updates and inserts', () => {
    const scenarios = [
      {
        scid: 1,
        conditions: [savedCondition(11, 'A1'), savedCondition(12, 'A2')],
      },
    ]
    const { question, focused, addToBuffer } = setup({
      scenarios,
      scenarioToPatch: { ...scenarios[0], qid: QID },
      conditions: [
        formCondition('B1', { cid: 11, isUpdated: true }),
        formCondition('A2', { cid: 12, isDeleted: true }),
        formCondition('C1'),
      ],
    })

    expect(addToBuffer).toHaveBeenCalledTimes(2)
    expect(addToBuffer.mock.calls[0][0]).toEqual({
      id: 12,
      qid: QID,
      op: 'delete',
      entity: 'questionCondition',
      error: false,
      props: {
        qid: QID,
        scenarios: [
          { scid: 1, conditions: [{ cid: 12, action: 'deleteCondition' }] },
        ],
      },
    })
    expect(addToBuffer.mock.calls[1][0]).toMatchObject({
      id: QID,
      qid: QID,
      op: 'update',
      props: {
        qid: QID,
        scenarios: [
          {
            scid: 1,
            conditions: [{ action: 'updateCondition', cid: 11, value: 'B1' }],
          },
          {
            scid: 1,
            conditions: [
              { action: 'insertCondition', cid: TEMP_CID, value: 'C1' },
            ],
          },
        ],
      },
    })
    expect(
      question.scenarios[0].conditions.map(({ cid, value }) => [cid, value])
    ).toEqual([
      [11, 'B1'],
      [TEMP_CID, 'C1'],
    ])
    expect(focused.relevance).toBe('1')
  })

  test('deleting the last condition resets the relevance', () => {
    const scenarios = [{ scid: 1, conditions: [savedCondition(11, 'A1')] }]
    const { question, focused, addToBuffer } = setup({
      scenarios,
      scenarioToPatch: { ...scenarios[0], qid: QID },
      conditions: [formCondition('A1', { cid: 11, isDeleted: true })],
    })

    expect(addToBuffer).toHaveBeenCalledTimes(1)
    expect(addToBuffer.mock.calls[0][0]).toMatchObject({
      id: 11,
      op: 'delete',
    })
    expect(question.scenarios).toEqual([])
    expect(question.relevance).toBe('1')
    expect(focused.scenarios).toEqual([])
  })

  test('a condition that already exists is not saved again', () => {
    const scenarios = [{ scid: 1, conditions: [savedCondition(11, 'A1')] }]
    const { addToBuffer, onNavigateBack } = setup({
      scenarios,
      conditions: [formCondition('A1')],
    })

    expect(addToBuffer).not.toHaveBeenCalled()
    expect(errorToast).toHaveBeenCalledTimes(1)
    expect(showSuccessMessage).not.toHaveBeenCalled()
    expect(onNavigateBack).toHaveBeenCalledTimes(1)
  })

  test('an edit made after the condition got its real ID is saved', () => {
    // The condition designer panel is open.
    setRecordingTempIds(true)
    const tempCid = 'temp__42_1'
    // The condition was saved while the designer was open: The question has
    // the real ID, the form still the temp ID.
    replaceTempIdsInSurveyAndBuffer(
      [{ tempId: tempCid, id: 77 }],
      EntitiesType.condition,
      {
        questionGroups: [
          {
            gid: 1,
            questions: [
              {
                qid: QID,
                scenarios: [{ scid: 1, conditions: [{ cid: tempCid }] }],
              },
            ],
          },
        ],
      },
      new OperationsBuffer()
    )

    const scenarios = [{ scid: 1, conditions: [savedCondition(77, 'A1')] }]
    const question = { qid: QID, relevance: '1', scenarios }
    const setConditions = jest.fn()
    const { result } = renderHook(() =>
      useConditionDesigner({
        survey: { questionGroups: [{ gid: 1, questions: [question] }] },
        question,
        focused: { qid: QID },
        scid: 1,
        scenarioToPatch: {
          scid: 1,
          qid: QID,
          conditions: [savedCondition(tempCid, 'A1')],
        },
        groupIndex: 0,
        questionIndex: 0,
        conditions: [formCondition('A1', { cid: tempCid })],
        addToBuffer: jest.fn(),
        setScid: jest.fn(),
        setConditions,
        update: jest.fn(),
        onNavigateBack: jest.fn(),
      })
    )

    result.current.updateCondition(0, 'value', 'B1')

    const [updater] = setConditions.mock.calls[0]
    const [edited] = updater([formCondition('A1', { cid: tempCid })])
    expect(edited.isUpdated).toBe(true)
  })

  test('an unsaved condition edited to several answers gets one temp ID per answer', () => {
    const tempCid = 'temp__900_1'
    const scenarios = [{ scid: 1, conditions: [savedCondition(tempCid, 'A1')] }]
    const { addToBuffer } = setup({
      scenarios,
      scenarioToPatch: { ...scenarios[0], qid: QID },
      conditions: [formCondition('A1,A2', { cid: tempCid, isUpdated: true })],
    })

    const [operation] = addToBuffer.mock.calls[0]
    const conditions = operation.props.scenarios.flatMap((s) => s.conditions)
    expect(conditions).toEqual([
      expect.objectContaining({
        cid: tempCid,
        value: 'A1',
        action: 'updateCondition',
      }),
      expect.objectContaining({
        cid: TEMP_CID,
        value: 'A2',
        action: 'insertCondition',
      }),
    ])
    expect(conditions[1].cid).not.toBe(tempCid)
  })

  test('a saved condition edited to several answers gets a new condition per extra answer', () => {
    const scenarios = [{ scid: 1, conditions: [savedCondition(11, 'A1')] }]
    const { question, addToBuffer } = setup({
      scenarios,
      scenarioToPatch: { ...scenarios[0], qid: QID },
      conditions: [formCondition('A1,A2', { cid: 11, isUpdated: true })],
    })

    const [operation] = addToBuffer.mock.calls[0]
    expect(operation.props.scenarios.flatMap((s) => s.conditions)).toEqual([
      expect.objectContaining({
        cid: 11,
        value: 'A1',
        action: 'updateCondition',
      }),
      expect.objectContaining({
        cid: TEMP_CID,
        value: 'A2',
        action: 'insertCondition',
      }),
    ])
    // The editor shows both conditions.
    expect(
      question.scenarios[0].conditions.map(({ cid, value }) => [cid, value])
    ).toEqual([
      [11, 'A1'],
      [TEMP_CID, 'A2'],
    ])
  })

  test('a constant with a comma is saved as one condition', () => {
    const { addToBuffer } = setup({
      conditions: [formCondition('1,5', { targetType: 'Constant' })],
    })

    const [operation] = addToBuffer.mock.calls[0]
    expect(operation.props.scenarios.flatMap((s) => s.conditions)).toEqual([
      expect.objectContaining({
        value: '1,5',
        ConditionConst: '1,5',
        action: 'insertCondition',
      }),
    ])
  })
})
