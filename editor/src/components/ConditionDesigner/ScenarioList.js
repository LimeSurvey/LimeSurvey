import React from 'react'
import { useParams } from 'react-router-dom'
import classNames from 'classnames'

import { useBuffer, useFocused, useSurvey } from 'hooks'
import {
  createBufferOperation,
  getQuestionById,
  QUESTION_RELEVANCE_DEFAULT_VALUE,
} from 'helpers'
import { Button } from 'components'
import { CloseCircleIcon } from 'components/icons'

import { getApiOperationActions, showDeleteScenarioOverlay } from './utils'

export const ScenarioList = ({ setScenarioToPatch }) => {
  const { surveyId } = useParams()
  const { survey, update } = useSurvey(surveyId)
  const { focused, groupIndex, questionIndex } = useFocused()
  const { addToBuffer } = useBuffer()

  const question = getQuestionById(focused.qid, survey).question
  const scenarios = question?.scenarios || []

  const handleDeleteScenario = (scenarioId) => {
    const properties = {
      qid: question.qid,
      scenarios: [
        {
          scid: scenarioId,
          action: getApiOperationActions().SCENARIO.DELETE,
        },
      ],
    }

    const operation = createBufferOperation(question.qid)
      .questionCondition(question.qid)
      .delete()
    operation.qid = question.qid
    operation.props = properties
    addToBuffer(operation)

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
            className={classNames('scenario-list-item', {})}
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
              {t('Scenario')} {scenario.scid}
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
