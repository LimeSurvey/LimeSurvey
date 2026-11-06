import { format } from 'util'
import React from 'react'
import { useParams } from 'react-router-dom'

import { useBuffer, useFocused, useSurvey } from 'hooks'
import {
  createBufferOperation,
  getQuestionById,
  hasTempId,
  QUESTION_RELEVANCE_DEFAULT_VALUE,
} from 'helpers'
import { isConditionInBuffer } from 'helpers/Buffer/operationsHandlers'
import { Button } from 'components'
import { CloseCircleIcon } from 'components/icons'

import { getApiOperationActions, showDeleteScenarioOverlay } from './utils'

export const ScenarioList = ({ setScenarioToPatch }) => {
  const { surveyId } = useParams()
  const { survey, update } = useSurvey(surveyId)
  const { focused } = useFocused()
  const { addToBuffer, operationsBuffer } = useBuffer()

  // Positions come from the current survey, the persisted focus indexes can be
  // stale (e.g. after a refresh restores focus from an older survey state).
  const { question, groupIndex, questionIndex } = getQuestionById(
    focused.qid,
    survey
  )
  const scenarios = question?.scenarios || []

  // The scenario only exists locally when all its conditions are still waiting
  // in the buffer to be created: none of them was sent to the server yet.
  const isLocalOnlyScenario = (scenario) => {
    const bufferedOperations = operationsBuffer.getOperations()

    return (
      scenario.conditions?.length > 0 &&
      scenario.conditions.every(
        (condition) =>
          hasTempId(String(condition.cid)) &&
          isConditionInBuffer(bufferedOperations, question.qid, condition.cid)
      )
    )
  }

  // Deleting each local-only condition removes it from the buffered creates
  // without sending anything to the server.
  const deleteLocalOnlyConditions = (scenario) => {
    scenario.conditions.forEach((condition) => {
      const operation = createBufferOperation(condition.cid)
        .questionCondition()
        .delete()
      operation.qid = question.qid
      operation.props = {
        qid: question.qid,
        scenarios: [
          {
            scid: scenario.scid,
            conditions: [
              {
                cid: condition.cid,
                action: getApiOperationActions().CONDITION.DELETE,
              },
            ],
          },
        ],
      }
      addToBuffer(operation)
    })
  }

  const queueScenarioDelete = (scenarioId) => {
    const operation = createBufferOperation(question.qid)
      .questionCondition(question.qid)
      .delete()
    operation.qid = question.qid
    operation.props = {
      qid: question.qid,
      scenarios: [
        {
          scid: scenarioId,
          action: getApiOperationActions().SCENARIO.DELETE,
        },
      ],
    }
    addToBuffer(operation)
  }

  const handleDeleteScenario = (scenarioId) => {
    const scenario = scenarios.find((s) => s.scid === scenarioId)
    if (scenario && isLocalOnlyScenario(scenario)) {
      deleteLocalOnlyConditions(scenario)
    } else {
      queueScenarioDelete(scenarioId)
    }

    question.scenarios = scenarios.filter(
      (scenario) => scenario.scid !== scenarioId
    )

    if (!question.scenarios.length)
      question.relevance = QUESTION_RELEVANCE_DEFAULT_VALUE

    const updatedQuestionGroups = [...survey.questionGroups]
    updatedQuestionGroups[groupIndex].questions[questionIndex] = question

    update({
      questionGroups: updatedQuestionGroups,
    })

    focused.scenarios = question.scenarios
    focused.relevance = question.relevance
  }

  return (
    <div className="d-grid gap-2">
      {scenarios.map((scenario) => {
        return (
          <div
            key={scenario.scid}
            id={`scenario-${scenario.scid}`}
            className="scenario-list-item"
          >
            <span
              className="fw-medium"
              style={{ cursor: 'pointer' }}
              onClick={() =>
                setScenarioToPatch({
                  ...scenario,
                  qid: focused.qid,
                })
              }
            >
              {format(t('Scenario %s'), scenario.scid)}
            </span>
            <div className="d-flex gap-2">
              <Button
                className="d-flex align-items-center p-0 text-secondary"
                variant="link"
                onClick={() =>
                  showDeleteScenarioOverlay(scenario.scid, handleDeleteScenario)
                }
              >
                <CloseCircleIcon />
              </Button>
            </div>
          </div>
        )
      })}
    </div>
  )
}
