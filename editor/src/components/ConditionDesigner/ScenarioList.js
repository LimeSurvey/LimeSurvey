import { format } from 'util'
import React from 'react'
import { useParams } from 'react-router-dom'

import { useBuffer, useFocused, useSurvey } from 'hooks'
import { getQuestionById, QUESTION_RELEVANCE_DEFAULT_VALUE } from 'helpers'
import { Button } from 'components'
import { CloseCircleIcon } from 'components/icons'

import {
  buildDeleteScenarioOperations,
  showDeleteScenarioOverlay,
} from './utils'

export const ScenarioList = ({ onEditScenario }) => {
  const { surveyId } = useParams()
  const { survey, update } = useSurvey(surveyId)
  const { focused, groupIndex, questionIndex } = useFocused()
  const { addToBuffer, operationsBuffer } = useBuffer()

  const question = getQuestionById(focused.qid, survey).question
  const scenarios = question?.scenarios || []

  const handleDeleteScenario = (scenarioId) => {
    const scenario = scenarios.find((item) => item.scid === scenarioId)
    buildDeleteScenarioOperations(
      question.qid,
      scenario,
      operationsBuffer.getOperations()
    ).forEach((operation) => addToBuffer(operation))

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
            <Button
              className="fw-medium p-0 text-reset text-decoration-none"
              variant="link"
              onClick={() =>
                onEditScenario({
                  ...scenario,
                  qid: focused.qid,
                })
              }
            >
              {format(t('Scenario %s'), scenario.scid)}
            </Button>
            <div className="d-flex gap-2">
              <Button
                className="d-flex align-items-center p-0 text-secondary"
                variant="link"
                onClick={() =>
                  showDeleteScenarioOverlay(scenario.scid, handleDeleteScenario)
                }
                ariaLabel={format(t('Delete scenario %s'), scenario.scid)}
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
