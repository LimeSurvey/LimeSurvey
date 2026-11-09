import { format } from 'util'
import React from 'react'
import { useParams } from 'react-router-dom'

import { useBuffer, useFocused, useSurvey } from 'hooks'
import { getQuestionById, QUESTION_RELEVANCE_DEFAULT_VALUE } from 'helpers'
import { Button } from 'components'
import { CloseCircleIcon } from 'components/icons'

import {
  buildScenarioConditionDeleteOperations,
  showDeleteScenarioOverlay,
} from './utils'

export const ScenarioList = ({ setScenarioToPatch }) => {
  const { surveyId } = useParams()
  const { survey, update } = useSurvey(surveyId)
  const { focused } = useFocused()
  const { addToBuffer } = useBuffer()

  // The position comes from the survey: The focus indexes can be stale.
  const { question, groupIndex, questionIndex } = getQuestionById(
    focused.qid,
    survey
  )
  const scenarios = question?.scenarios || []

  const handleDeleteScenario = (scenarioId) => {
    if (groupIndex === undefined) return

    // Condition by condition (see buildScenarioConditionDeleteOperations):
    // Unsaved conditions are only removed from the buffer.
    const scenario = scenarios.find((s) => s.scid === scenarioId)
    buildScenarioConditionDeleteOperations(question.qid, scenario).forEach(
      (operation) => addToBuffer(operation)
    )

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
