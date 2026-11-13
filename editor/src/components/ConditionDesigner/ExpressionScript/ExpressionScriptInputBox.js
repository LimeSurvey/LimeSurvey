import { useParams } from 'react-router-dom'

import {
  useBuffer,
  useExpressionScriptValidation,
  useFocused,
  useSurvey,
} from 'hooks'
import {
  getQuestionById,
  htmlPopup,
  isTempId,
  isDefaultRelevance,
} from 'helpers'
import { Button } from 'components/UIComponents'
import { CloseCircleIcon, ExpandIcon } from 'components/icons'

import { ExpressionScriptEditorOverlay } from '../Overlays'
import { handleConditionScriptUpdate } from './handleConditionScriptUpdate'
import { showResetAllConditionsOverlay, showWarningMessage } from '../utils'

export const ExpressionScriptInputBox = ({ onShowPanel, onNavigateBack }) => {
  const { surveyId } = useParams()
  const { survey, update } = useSurvey(surveyId)
  const { focused, groupIndex, questionIndex } = useFocused()
  const { addToBuffer } = useBuffer()

  const question = getQuestionById(focused.qid, survey).question
  // The server can't validate an expression for a question it doesn't know yet.
  const validateExpression = useExpressionScriptValidation(
    surveyId,
    isTempId(question.qid) ? null : question.qid
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

  const openExpressionScriptPanel = () => {
    onShowPanel(null, false, true)
    maybeShowConditionsOverwriteWarning()
  }

  const handleApply = (newExpressionScript) => {
    handleConditionScriptUpdate(
      newExpressionScript,
      survey,
      questionIndex,
      groupIndex,
      addToBuffer,
      update,
      onNavigateBack
    )
  }

  return (
    <div className="condition-script-trigger">
      <span
        className="text-muted text-truncate"
        style={{ cursor: 'pointer' }}
        role="button"
        tabIndex={0}
        aria-label={t('Expression script')}
        onClick={openExpressionScriptPanel}
        onKeyDown={(event) => {
          if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault()
            openExpressionScriptPanel()
          }
        }}
      >
        {isDefaultRelevance(question?.relevance)
          ? t('Enter expression...')
          : question?.relevance || ''}
      </span>
      <div className="d-flex gap-2">
        <Button
          className="d-flex align-items-center p-0 text-secondary"
          variant="link"
          ariaLabel={t('Edit or copy large conditions')}
          onClick={() => {
            htmlPopup({
              html: (
                <ExpressionScriptEditorOverlay
                  question={question}
                  onApply={handleApply}
                  validateExpression={validateExpression}
                />
              ),
              title: '',
              showCloseButton: false,
              width: '1156px',
            })
            maybeShowConditionsOverwriteWarning()
          }}
        >
          <ExpandIcon />
        </Button>
        {!isDefaultRelevance(question?.relevance) && (
          <Button
            className="d-flex align-items-center p-0"
            variant="link"
            ariaLabel={t('Delete all conditions')}
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
