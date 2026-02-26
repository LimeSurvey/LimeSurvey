import { foldGutter, foldService } from '@codemirror/language'
import { autocompletion } from '@codemirror/autocomplete'
import { EditorView } from '@codemirror/view'

import { Button } from 'components/UIComponents'
import { SwalAlert } from 'helpers'
import { CodeEditor } from 'components/UIComponents/ContentEditor/CodeMirror/CodeMirror'

import { makeExpressionReadable } from '../utils'

export const ConditionScriptOverwriteConfirmation = ({
  script,
  onConfirmChanges,
  onCancelChanges = () => {},
}) => {
  return (
    <div className="condition-designer-overlay">
      <div className="condition-designer-overlay-title">
        <p>{t('Overwriting condition from ExpressionScript')}</p>
      </div>

      <div className="condition-designer-overlay-message">
        {t(
          'The condition created inside the builder will replace your existing condition written in ExpressionScript mode. This action cannot be undone. Do you want to apply your changes?'
        )}
      </div>

      <div className="condition-designer-overlay-message mt-3">
        <p>{t('This script will be overwritten:')}</p>

        <div className="text-start condition-designer-overlay-textarea-container mt-1">
          <CodeEditor
            id="expression-script-editor"
            value={makeExpressionReadable(script)}
            height="150px"
            width="100%"
            className="text-start expression-script-codemirror"
            extensions={[
              EditorView.editable.of(false),
              foldService.of(() => null),
              autocompletion({ override: [] }),
              foldGutter({ openText: '', closedText: '' }),
            ]}
          />
        </div>
      </div>

      <div className="condition-designer-overlay-actions">
        <Button
          variant="secondary"
          className="condition-designer-overlay-secondary-button"
          onClick={() => {
            SwalAlert.close()
            onCancelChanges()
          }}
        >
          {t('Cancel')}
        </Button>
        <Button
          variant="primary"
          className="condition-designer-overlay-primary-button"
          onClick={() => {
            SwalAlert.close()
            onConfirmChanges()
          }}
        >
          {t('Save changes')}
        </Button>
      </div>
    </div>
  )
}
