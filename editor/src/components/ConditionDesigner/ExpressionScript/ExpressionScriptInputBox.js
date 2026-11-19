import { useParams } from 'react-router-dom'

import { useBuffer, useExpressionScriptValidation, useSurvey } from 'hooks'
import { QUESTION_RELEVANCE_DEFAULT_VALUE } from 'helpers'
import { Button } from 'components/UIComponents'
import { CloseCircleIcon, ExpandIcon } from 'components/icons'

import { ExpressionScriptEditorOverlay } from '../Overlays'
import { handleConditionScriptUpdate } from './handleConditionScriptUpdate'
import {
  showConditionPopup,
  showResetAllConditionsOverlay,
  showWarningMessage,
} from '../utils'

export const ExpressionScriptInputBox = ({
  question,
  onShowPanel,
  onNavigateBack,
}) => {
  const { surveyId } = useParams()
  const { update } = useSurvey(surveyId)
  const { addToBuffer } = useBuffer()

  const validateExpression = useExpressionScriptValidation(
    surveyId,
    question?.qid
  )
  const hasConditions = (question?.scenarios ?? []).length > 0

  const maybeShowConditionsOverwriteWarning = () => {
    if (hasConditions) {
      showWarningMessage(
        t(
          `Conditions written in ExpressionScript mode can't be displayed inside the visual condition builder.`
        ),
        'bottom-center'
      )
    }
  }

  const handleApply = (newExpressionScript) => {
    handleConditionScriptUpdate(
      newExpressionScript,
      question.qid,
      addToBuffer,
      update,
      onNavigateBack
    )
  }

  if (!question) {
    return null
  }

  return (
    <div className="condition-script-trigger">
      <span
        className="text-muted text-truncate"
        style={{ cursor: 'pointer' }}
        onClick={() => {
          onShowPanel(null, false, true)
          maybeShowConditionsOverwriteWarning()
        }}
      >
        {question?.relevance === QUESTION_RELEVANCE_DEFAULT_VALUE
          ? t('Enter expression...')
          : question?.relevance || ''}
      </span>
      <div className="d-flex gap-2">
        <Button
          className="d-flex align-items-center p-0 text-secondary"
          variant="link"
          onClick={() => {
            showConditionPopup({
              html: (
                <ExpressionScriptEditorOverlay
                  question={question}
                  onApply={handleApply}
                  validateExpression={validateExpression}
                />
              ),
              showCloseButton: false,
              width: '1156px',
            })
            maybeShowConditionsOverwriteWarning()
          }}
        >
          <ExpandIcon />
        </Button>
        {question?.relevance &&
          question.relevance !== QUESTION_RELEVANCE_DEFAULT_VALUE && (
            <Button
              className="d-flex align-items-center p-0"
              variant="link"
              onClick={() => {
                showResetAllConditionsOverlay({
                  onConfirmDelete: () => handleApply('1'),
                })
              }}
            >
              <CloseCircleIcon />
            </Button>
          )}
      </div>
    </div>
  )
}
