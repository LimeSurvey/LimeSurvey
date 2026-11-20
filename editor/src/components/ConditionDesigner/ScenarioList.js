import { format } from 'util'
import React, { useCallback, useEffect, useState } from 'react'
import { useParams } from 'react-router-dom'
import classNames from 'classnames'

import { useBuffer, useFocused, useSurvey } from 'hooks'
import {
  createBufferOperation,
  QUESTION_RELEVANCE_DEFAULT_VALUE,
} from 'helpers'
import { Button } from 'components'
import { CloseCircleIcon } from 'components/icons'

import {
  getApiOperationActions,
  getCurrentQuestionInfo,
  replaceQuestionInSurvey,
  showDeleteScenarioOverlay,
} from './utils'

export const ScenarioList = ({
  question,
  setScenarioToPatch,
  pendingScenarioName,
  setPendingScenarioName,
}) => {
  const { surveyId } = useParams()
  const { update } = useSurvey(surveyId)
  const { focused } = useFocused()
  const { addToBuffer } = useBuffer()

  const scenarios = question.scenarios || []

  const [hasTempId, setHasTempId] = useState(!!pendingScenarioName)

  useEffect(() => {
    if (!pendingScenarioName) return

    const processedScenario = scenarios.find(
      (scenario) => scenario.scid === pendingScenarioName
    )

    if (processedScenario) {
      const tempIdExists = processedScenario.conditions.some((condition) =>
        condition.cid.toString().includes('temp')
      )
      setHasTempId(tempIdExists)

      if (pendingScenarioName && !tempIdExists) {
        setPendingScenarioName(null)
      }
    }
  }, [question, pendingScenarioName, scenarios])

  const handleDeleteScenario = (scenarioId) => {
    // This runs from the delete confirmation popup, which can outlive this
    // render: find the question in the current survey. Do nothing if the
    // question or the scenario is gone meanwhile, e.g. the group was deleted,
    // or a refresh replaced the scenarios with an ExpressionScript condition.
    const { question: currentQuestion } = getCurrentQuestionInfo(question.qid)
    const scenarioExists = (currentQuestion?.scenarios ?? []).some(
      (scenario) => scenario.scid === scenarioId
    )
    if (!scenarioExists) return

    const properties = {
      qid: currentQuestion.qid,
      scenarios: [
        {
          scid: scenarioId,
          action: getApiOperationActions().SCENARIO.DELETE,
        },
      ],
    }

    const operation = createBufferOperation(currentQuestion.qid)
      .questionCondition(currentQuestion.qid)
      .delete()
    operation.qid = currentQuestion.qid
    operation.props = properties
    addToBuffer(operation)

    const remainingScenarios = currentQuestion.scenarios.filter(
      (scenario) => scenario.scid !== scenarioId
    )
    // A copy: the cached survey must only change through `update`
    const updatedQuestion = {
      ...currentQuestion,
      scenarios: remainingScenarios,
      // The last scenario is removed: the question has no condition anymore
      ...(remainingScenarios.length === 0 && {
        relevance: QUESTION_RELEVANCE_DEFAULT_VALUE,
      }),
    }
    replaceQuestionInSurvey(updatedQuestion, update)
  }

  const isPendingAndHasTempId = useCallback(
    (scenario) => scenario.scid === pendingScenarioName && hasTempId,
    [pendingScenarioName, hasTempId]
  )

  return (
    <div className="d-grid gap-2">
      {scenarios.map((scenario) => {
        return (
          <div
            key={scenario.scid}
            id={`scenario-${scenario.scid}`}
            className={classNames('scenario-list-item', {
              disabled: isPendingAndHasTempId(scenario),
            })}
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
