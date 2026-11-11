import { format } from 'util'
import React from 'react'
import { useParams } from 'react-router-dom'

import { useBuffer, useFocused, useSurvey } from 'hooks'
import { getQuestionById, QUESTION_RELEVANCE_DEFAULT_VALUE } from 'helpers'
import { Button } from 'components'
import { CloseCircleIcon } from 'components/icons'

import {
  buildScenarioConditionDeleteOperations,
  getLiveFocusedQuestion,
  showDeleteScenarioOverlay,
} from './utils'

export const ScenarioList = ({ setScenarioToPatch }) => {
  const { surveyId } = useParams()
  const { survey, update } = useSurvey(surveyId)
  const { focused } = useFocused()
  const { addOperationsToBuffer } = useBuffer()

  // Read from the survey, not from the focus (a copy of the question that a
  // save doesn't refresh), in the order of their numbers.
  const scenarios = [
    ...(getQuestionById(focused.qid, survey).question?.scenarios || []),
  ].sort((a, b) => a.scid - b.scid)

  /**
   * Deletes a scenario of the focused question, once confirmed in a dialog.
   * The question is looked up now (getLiveFocusedQuestion): While the dialog
   * was open, a save may have replaced its temp ID or those of its
   * conditions.
   * @param {number} scenarioId
   * @returns {void}
   */
  const handleDeleteScenario = (scenarioId) => {
    const {
      survey: liveSurvey,
      question,
      groupIndex,
      questionIndex,
    } = getLiveFocusedQuestion()
    const scenario = question?.scenarios?.find((s) => s.scid === scenarioId)
    if (!scenario) return

    // Condition by condition (see buildScenarioConditionDeleteOperations):
    // Unsaved conditions are only removed from the buffer.
    addOperationsToBuffer(
      buildScenarioConditionDeleteOperations(question.qid, scenario)
    )

    question.scenarios = question.scenarios.filter(
      (scenario) => scenario.scid !== scenarioId
    )

    if (!question.scenarios.length)
      question.relevance = QUESTION_RELEVANCE_DEFAULT_VALUE

    const updatedQuestionGroups = [...liveSurvey.questionGroups]
    updatedQuestionGroups[groupIndex].questions[questionIndex] = question

    update({
      questionGroups: updatedQuestionGroups,
    })
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
