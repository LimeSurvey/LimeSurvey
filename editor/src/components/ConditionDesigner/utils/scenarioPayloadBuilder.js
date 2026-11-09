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
import { createBufferOperation } from 'helpers'

import { getApiOperationActions } from './getApiOperationActions'

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
    condition.value
      .toString()
      .split(',')
      .forEach((val) => {
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

/**
 * Builds the buffer operation deleting one condition of a scenario.
 * @param {number|string} qid question owning the condition
 * @param {number|string} scid scenario of the condition
 * @param {number|string} cid
 */
export const buildConditionDeleteOperation = (qid, scid, cid) => {
  const operation = createBufferOperation(cid).questionCondition().delete()
  operation.qid = qid
  operation.props = {
    qid,
    scenarios: [
      {
        scid,
        conditions: [
          { cid, action: getApiOperationActions().CONDITION.DELETE },
        ],
      },
    ],
  }
  return operation
}

/**
 * Builds the buffer operations deleting a scenario: one delete per condition
 * shown in it, not a scenario delete by number. The buffer sends deletes
 * before older updates, so a scenario delete could reach the server before a
 * buffered move of a condition between scenarios: it would then delete a
 * condition moved out of the scenario, and keep one moved into it. Deleting
 * the conditions by cid doesn't depend on that order.
 * @param {number|string} qid question owning the scenario
 * @param {{scid: number, conditions: Array}} scenario as shown in the editor
 * @returns {Array}
 */
export const buildScenarioConditionDeleteOperations = (qid, scenario) =>
  (scenario?.conditions ?? []).map((condition) =>
    buildConditionDeleteOperation(qid, scenario.scid, condition.cid)
  )
