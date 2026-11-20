import { useState } from 'react'
import { foldGutter, foldService } from '@codemirror/language'
import { autocompletion } from '@codemirror/autocomplete'

import { ExpressionScriptEditor } from 'components/UIComponents'
import { QUESTION_RELEVANCE_DEFAULT_VALUE, SwalAlert } from 'helpers'
import { Button } from 'components'

import {
  isValidRelevanceValue,
  makeExpressionReadable,
  clean,
  getConditionState,
  getCurrentQuestionInfo,
  runIfConditionUnchanged,
  showConditionOverwriteConfirmationOverlay,
} from '../utils'

export const ExpressionScriptEditorOverlay = ({
  question,
  onApply,
  validateExpression,
}) => {
  const expressionScript =
    question?.relevance === QUESTION_RELEVANCE_DEFAULT_VALUE
      ? ''
      : question?.relevance || ''

  const [content, setContent] = useState(
    makeExpressionReadable(expressionScript)
  )
  const [cleanContent, setCleanContent] = useState(clean(content))

  const handleChange = (newContent) => {
    setCleanContent(clean(newContent))
    setContent(newContent)
  }

  const handleApplyClick = () => {
    // The popup can stay open while the survey is refreshed: decide on the
    // question's scenarios as they are now, not when the popup opened
    const currentQuestion =
      getCurrentQuestionInfo(question?.qid).question ?? question
    if (currentQuestion?.scenarios?.length > 0) {
      // The confirmation only applies to the scenarios it was shown for
      const shownCondition = getConditionState(currentQuestion)
      showConditionOverwriteConfirmationOverlay({
        onConfirm: () => {
          SwalAlert.close()
          runIfConditionUnchanged(currentQuestion.qid, shownCondition, () =>
            onApply(cleanContent)
          )
        },
      })
    } else {
      SwalAlert.close()
      onApply(cleanContent)
    }
  }

  return (
    <div className="condition-designer-overlay">
      <div className="condition-designer-overlay-title">
        <p>{t('Condition code')}</p>
      </div>
      <p className="condition-designer-overlay-message">
        {t('Edit or copy large conditions')}
      </p>
      <div className="text-start condition-designer-overlay-textarea-container">
        <ExpressionScriptEditor
          id="expression-script-editor"
          value={content}
          height="50vh"
          width="100%"
          className="text-start expression-script-codemirror"
          onChange={handleChange}
          validateExpression={validateExpression}
          extensions={[
            foldService.of(() => null),
            autocompletion({ override: [] }),
            foldGutter({ openText: '', closedText: '' }),
          ]}
        />
      </div>
      <div className="condition-designer-overlay-actions d-flex justify-content-end">
        <Button
          variant="secondary"
          className="condition-designer-overlay-secondary-button"
          onClick={() => SwalAlert.close()}
        >
          {t('Cancel')}
        </Button>
        <Button
          variant="primary"
          className="condition-designer-overlay-primary-button"
          disabled={isValidRelevanceValue(cleanContent, question)}
          onClick={() => handleApplyClick()}
        >
          {t('Apply')}
        </Button>
      </div>
    </div>
  )
}
