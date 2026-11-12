/**
 * Turns condition designer form conditions into the API format of
 * OpHandlerQuestionCondition: New conditions get a temp ID, and conditions
 * are grouped by scenario. Each condition holds one value: The condition
 * designer splits comma separated answers before (splitConditionValues).
 */
import { NEW_OBJECT_ID_PREFIX } from 'helpers/constants'

import { getApiOperationActions } from './getApiOperationActions'

const actions = getApiOperationActions()

const sourceTabs = {
  Question: '#SRCPREVQUEST',
  Token: '#SRCTOKENATTRS',
}

const targetTabs = {
  Answer: '#CANSWERSTAB',
  Constant: '#CONST',
  Question: '#PREVQUESTIONS',
  Token: '#TOKENATTRS',
  RegExp: '#REGEXP',
}

// The fields the API reads the condition value from, per target tab.
const targetValueFields = {
  [targetTabs.Answer]: ({ value }) => ({
    canswers: value.startsWith('[') ? JSON.parse(value) : [value],
  }),
  [targetTabs.Constant]: ({ value }) => ({ ConditionConst: value }),
  [targetTabs.Question]: ({ value }) => ({ prevQuestionSGQA: value }),
  [targetTabs.RegExp]: ({ value }) => ({ ConditionRegexp: value }),
  [targetTabs.Token]: ({ value, cfieldname }) => ({
    tokenAttr: value,
    prevQuestionSGQA: cfieldname,
  }),
}

const buildApiCondition = (condition, value, scenarioName, newTempId) => {
  const isNew = !condition.cid
  const editSourceTab = sourceTabs[condition.sourceType]
  const editTargetTab = targetTabs[condition.targetType]
  const tempId = isNew ? newTempId() : undefined

  return {
    qid: condition.qid,
    cqid: condition.cqid,
    cfieldname: condition.cfieldname,
    cquestions: condition.cquestions,
    method: condition.method ?? '==', // Ensure a valid default method just before sending
    value,
    scenario: scenarioName,
    action: isNew ? actions.CONDITION.CREATE : actions.CONDITION.UPDATE,
    editSourceTab,
    editTargetTab,
    cid: isNew ? tempId : condition.cid,
    ...(editSourceTab === sourceTabs.Token
      ? { csrctoken: condition.cfieldname }
      : {}),
    ...(isNew ? { tempId, tempcids: [tempId] } : {}),
    ...(targetValueFields[editTargetTab]?.({
      value,
      cfieldname: condition.cfieldname,
    }) ?? {}),
  }
}

// Temp condition IDs are unique in the session, not random: Buffered saves
// are merged by condition ID, two equal IDs would drop a condition.
let payloadCount = 0

export const buildScenarioPayload = (question, conditions, scenarioName) => {
  payloadCount += 1
  const tempIdPrefix = `${NEW_OBJECT_ID_PREFIX}${Date.now()}${payloadCount}`
  let tempIdIndex = 1
  const newTempId = () => `${tempIdPrefix}_${tempIdIndex++}`

  const scenarios = []

  conditions.forEach((condition) => {
    const apiCondition = buildApiCondition(
      condition,
      condition.value.toString(),
      scenarioName,
      newTempId
    )
    let scenario = scenarios.find((s) => s.scid == apiCondition.scenario)

    if (!scenario) {
      scenario = { scid: apiCondition.scenario, conditions: [] }
      scenarios.push(scenario)
    }
    scenario.conditions.push(apiCondition)
  })

  return scenarios.length ? { qid: question.qid, scenarios } : null
}
