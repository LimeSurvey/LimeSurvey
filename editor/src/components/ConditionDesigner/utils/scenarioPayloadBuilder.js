/**
 * scenarioPayloadBuilder.js
 *
 * This module is responsible for constructing the scenario payload
 * to be sent to the API. It transforms UI condition objects into
 * API-ready format by:
 * - Determining the correct operation (CREATE or UPDATE)
 * - Mapping values to appropriate tab formats (e.g., answers, constants, regex)
 * - Assigning temporary IDs for new conditions
 * - Structuring conditions into grouped scenarios by scenario name
 *
 * Depends on:
 * - getApiOperationActions from utils.js
 */
import { getApiOperationActions } from './getApiOperationActions'
import { getConditionTypeInfo } from './getConditionTypeInfo'

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

/**
 * Returns the values of a condition: The answer options selected together for a new
 * condition are joined with commas and become one condition each. Constants and
 * regular expressions may contain commas themselves, so they stay one value.
 *
 * @param {object} condition The condition of the Condition Designer.
 * @returns {string[]} The values, one per condition to save.
 */
export const splitConditionValues = (condition) => {
  const value = condition.value.toString()
  return condition.targetType === getConditionTypeInfo().TARGET.ANSWER_OPTIONS
    ? value.split(',')
    : [value]
}

export const buildScenarioPayload = (question, conditions, scenarioName) => {
  const apiActions = getApiOperationActions()
  const qid = question.qid
  const tempIdSeed = Math.floor(Math.random() * 1000000)
  const tempIdPrefix = `temp__${tempIdSeed}`
  let tempIdIndex = 1
  const scenarios = []

  const createApiCondition = (condition, value) => {
    const apiCondition = {
      qid: condition.qid,
      cqid: condition.cqid,
      cfieldname: condition.cfieldname,
      cquestions: condition.cquestions,
      method: condition.method ?? '==', // Ensure a valid default method just before sending
      value: value,
      scenario: scenarioName,
      action: condition.cid
        ? apiActions.CONDITION.UPDATE
        : apiActions.CONDITION.CREATE,
      editSourceTab: sourceTabs[condition.sourceType],
      editTargetTab: targetTabs[condition.targetType],
      ...(condition.cid ? { cid: condition.cid } : {}),
    }

    if (apiCondition.editSourceTab === sourceTabs['Token']) {
      apiCondition.csrctoken = apiCondition.cfieldname
    }

    if (apiCondition.action === apiActions.CONDITION.CREATE) {
      apiCondition.tempId = `${tempIdPrefix}_${tempIdIndex++}`
      apiCondition.cid = apiCondition.tempId
      apiCondition.tempcids = [apiCondition.tempId]
    }

    switch (apiCondition.editTargetTab) {
      case targetTabs['Answer']:
        apiCondition.canswers = apiCondition.value.startsWith('[')
          ? JSON.parse(apiCondition.value)
          : [apiCondition.value]
        break
      case targetTabs['Constant']:
        apiCondition.ConditionConst = apiCondition.value
        break
      case targetTabs['Question']:
        apiCondition.prevQuestionSGQA = apiCondition.value
        break
      case targetTabs['RegExp']:
        apiCondition.ConditionRegexp = apiCondition.value
        break
      case targetTabs['Token']:
        apiCondition.tokenAttr = apiCondition.value
        apiCondition.prevQuestionSGQA = apiCondition.cfieldname
        break
    }
    return apiCondition
  }

  conditions.forEach((condition) => {
    splitConditionValues(condition).forEach((val) => {
      const apiCondition = createApiCondition(condition, val)
      let scenario = scenarios.find((s) => s.scid == apiCondition.scenario)

      if (!scenario) {
        scenario = { scid: apiCondition.scenario, conditions: [] }
        scenarios.push(scenario)
      }
      scenario.conditions.push(apiCondition)
    })
  })

  return scenarios.length ? { qid, scenarios } : null
}
