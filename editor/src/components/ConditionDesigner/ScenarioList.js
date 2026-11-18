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
    // render: find the question in the current survey, and do nothing if it
    // was removed meanwhile (e.g. its group was deleted).
    const {
      survey,
      question: currentQuestion,
      groupIndex,
      questionIndex,
    } = getCurrentQuestionInfo(question.qid)
    if (!currentQuestion) return

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

    currentQuestion.scenarios = (currentQuestion.scenarios ?? []).filter(
      (scenario) => scenario.scid !== scenarioId
    )

    if (!currentQuestion.scenarios.length)
      currentQuestion.relevance = QUESTION_RELEVANCE_DEFAULT_VALUE

    const updatedQuestionGroups = [...survey.questionGroups]
    updatedQuestionGroups[groupIndex].questions[questionIndex] = currentQuestion

    update({
      questionGroups: updatedQuestionGroups,
    })

    focused.scenarios = currentQuestion.scenarios
    focused.relevance = currentQuestion.relevance
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
