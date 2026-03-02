import { useState } from 'react'
import { EditorView } from '@codemirror/view'
import { foldGutter, foldService } from '@codemirror/language'
import { autocompletion } from '@codemirror/autocomplete'

import { CodeEditor } from 'components/UIComponents/ContentEditor/CodeMirror/CodeMirror'
import { QUESTION_RELEVANCE_DEFAULT_VALUE, SwalAlert } from 'helpers'
import { Button } from 'components'

import {
  isValidRelevanceValue,
  makeExpressionReadable,
  clean,
  showConditionOverwriteConfirmationOverlay,
} from '../utils'

export const ExpressionScriptEditorOverlay = ({ question, onApply }) => {
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
    if (question?.scenarios.length > 0) {
      showConditionOverwriteConfirmationOverlay({
        onConfirm: () => {
          SwalAlert.close()
          onApply(cleanContent)
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
        <CodeEditor
          id="expression-script-editor"
          value={content}
          height="50vh"
          width="100%"
          className="text-start expression-script-codemirror"
          onChange={handleChange}
          extensions={[
            EditorView.lineWrapping,
            foldService.of(() => null),
            autocompletion({ override: [] }),
            foldGutter({ openText: '', closedText: '' }),
          ]}
        />
      </div>

      <div className="condition-designer-overlay-actions">
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
