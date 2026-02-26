import { useParams } from 'react-router-dom'
import React, { useEffect, useState } from 'react'
import classNames from 'classnames'

import { useBuffer, useFocused, useSurvey } from 'hooks'
import { getQuestionById, QUESTION_RELEVANCE_DEFAULT_VALUE } from 'helpers'
import { SideBarHeader } from 'components/SideBar'
import { Button } from 'components/UIComponents'
import { ArrowLeftIcon } from 'components/icons'

import { handleConditionScriptUpdate } from './handleConditionScriptUpdate'
import {
  isValidRelevanceValue,
  showConditionOverwriteConfirmationOverlay,
  showUnsavedChangesOverlay,
} from '../utils'

export const ExpressionScript = ({ onNavigateBack = () => {} }) => {
  const { surveyId } = useParams()
  const { survey, update } = useSurvey(surveyId)
  const { focused, groupIndex, questionIndex } = useFocused()
  const { addToBuffer } = useBuffer()

  const question = getQuestionById(focused.qid, survey).question

  const [content, setContent] = useState(
    question.relevance === QUESTION_RELEVANCE_DEFAULT_VALUE
      ? ''
      : question.relevance
  )

  useEffect(() => {
    setContent(
      question.relevance === QUESTION_RELEVANCE_DEFAULT_VALUE
        ? ''
        : question.relevance
    )
  }, [focused])

  const handleChange = (e) => {
    setContent(e.target.value)
  }

  const handleApply = () => {
    handleConditionScriptUpdate(
      content,
      survey,
      questionIndex,
      groupIndex,
      addToBuffer,
      update,
      onNavigateBack
    )
  }

  const shouldShowUnsavedAlert = () => {
    if (
      content.trim() === '' &&
      question.relevance.trim() === QUESTION_RELEVANCE_DEFAULT_VALUE
    )
      return false

    return question.relevance.trim() !== content.trim()
  }

  const handleApplyClick = () => {
    if (question?.scenarios.length > 0) {
      showConditionOverwriteConfirmationOverlay({
        onConfirm: () => handleApply(content),
        onCancel: onNavigateBack,
      })
    } else {
      handleApply(content)
    }
  }

  return (
    <>
      <div
        className={classNames('survey-settings')}
        data-testid="condition-designer-expression-script"
      >
        <SideBarHeader className="condition-designer-sidebar right-side-bar-header primary">
          <div className="d-flex gap-2 fw-bold">
            <Button
              className="d-flex align-items-center p-0"
              variant="btn bg-transparent border-0 shadow-none p-0"
              onClick={() => {
                shouldShowUnsavedAlert()
                  ? showUnsavedChangesOverlay(
                      onNavigateBack,
                      t(
                        'You are about to go back without saving your changes. Do you want to proceed?'
                      )
                    )
                  : onNavigateBack()
              }}
            >
              <ArrowLeftIcon className={`text-black`} />
            </Button>
            <span className="text-start">{t('Expression script')}</span>
          </div>
        </SideBarHeader>
      </div>

      <div className="expression-script-container">
        <div className="expression-script-header">
          <p>{t('All scenarios')}</p>
        </div>
        <div
          className="expression-script-body"
          style={{ maxWidth: '800px', margin: '0 auto' }}
        >
          <span className="expression-script-label mb-1">
            {t('Expression script')}
          </span>
          <div className="d-flex justify-content-center mb-3">
            <textarea
              onChange={handleChange}
              className="expression-script-textarea no-resize"
              value={content}
            />
          </div>
        </div>
      </div>

      <div className="p-2 m-2 text-center">
        <Button
          onClick={handleApplyClick}
          variant={
            isValidRelevanceValue(content, question) ? 'secondary' : 'primary'
          }
          disabled={isValidRelevanceValue(content, question)}
        >
          {t('Apply')}
        </Button>
      </div>
    </>
  )
}
