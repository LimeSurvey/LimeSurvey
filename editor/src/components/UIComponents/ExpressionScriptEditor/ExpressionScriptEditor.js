import { useEffect, useState } from 'react'
import { EditorView } from '@codemirror/view'

import { CodeEditor } from '../ContentEditor/CodeEditor/CodeEditor'
import {
  expressionScriptDiagnostics,
  expressionScriptExtensions,
} from './expressionScriptExtensions'

export const ExpressionScriptEditor = ({
  id = 'expression-script-editor',
  value = '',
  update,
  onChange,
  labelText,
  height = '120px',
  width = '100%',
  className = '',
  readOnly = false,
  activeDisabled = false,
  disabled = false,
  noPermissionDisabled = false,
  noAccessDisabled = false,
  hasSurveyUpdatePermission = true,
  ariaLabel,
  extensions = [],
  validateExpression,
  validationDelay = 400,
}) => {
  const [diagnostics, setDiagnostics] = useState([])
  const isDisabled =
    readOnly ||
    activeDisabled ||
    disabled ||
    noAccessDisabled ||
    (noPermissionDisabled && !hasSurveyUpdatePermission)
  const handleChange = (newValue) => {
    onChange?.(newValue)
    update?.(newValue)
  }

  useEffect(() => {
    setDiagnostics([])
    if (!validateExpression || !value?.trim()) return undefined

    const abortController = new AbortController()
    const timeout = window.setTimeout(async () => {
      try {
        const result = await validateExpression(value, abortController.signal)
        if (!abortController.signal.aborted) {
          setDiagnostics(result?.diagnostics ?? [])
        }
      } catch (error) {
        if (error?.name !== 'CanceledError' && error?.name !== 'AbortError') {
          setDiagnostics([])
        }
      }
    }, validationDelay)

    return () => {
      window.clearTimeout(timeout)
      abortController.abort()
    }
  }, [validateExpression, validationDelay, value])

  return (
    <div className={`expression-script-editor ${className}`}>
      {labelText && <div className="ui-label mb-1">{labelText}</div>}
      <CodeEditor
        id={id}
        value={value ?? ''}
        height={height}
        width={width}
        className="text-start expression-script-codemirror"
        onChange={handleChange}
        extensions={[
          ...expressionScriptExtensions,
          ...expressionScriptDiagnostics(diagnostics, (value ?? '').length),
          EditorView.lineWrapping,
          EditorView.editable.of(!isDisabled),
          EditorView.contentAttributes.of({
            'aria-label': ariaLabel || labelText || t('Expression script'),
            ...(isDisabled
              ? { 'aria-disabled': 'true', 'aria-readonly': 'true' }
              : {}),
          }),
          ...extensions,
        ]}
      />
    </div>
  )
}
