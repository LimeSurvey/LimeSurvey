import CodeMirror from '@uiw/react-codemirror'
import { html } from '@codemirror/lang-html'
import { EditorView } from '@codemirror/view'
import { useEffect } from 'react'

const codeEditorTheme = EditorView.theme({
  '&': {
    backgroundColor: '#ffffff',
    border: '2px solid #6e748c',
    borderRadius: '2px',
    padding: '12px 8px',
  },
  '&.cm-focused': {
    outline: 'none',
  },
  '.cm-gutters': {
    backgroundColor: '#f8f9fa',
    color: '#1e1e1e',
  },
  '.cm-lineNumbers': {
    fontSize: '12px',
    color: '#1e1e1e',
    textAlign: 'center',
    minWidth: '30px',
  },
  '.cm-gutterElement': {
    display: 'flex',
    justifyContent: 'center',
    alignItems: 'center',
    minWidth: '40px !important',
    padding: '0 8px !important',
  },
  '.cm-line': {
    backgroundColor: '#ffffff',
  },
  '.cm-lineWrapping': {
    padding: '0',
  },
  '.cm-activeLineGutter': {
    backgroundColor: '#dddee8',
    padding: '0 8px !important',
  },
  '.cm-content': {
    fontSize: '14px',
    backgroundColor: '#ffffff',
    color: '#1e1e1e',
  },
  '.cm-foldGutter': {
    display: 'none',
    width: '0px',
  },
})

export const CodeEditor = ({
  id = 'code-mirror',
  value = '',
  height = '75vh',
  width = '75vw',
  className = 'text-start',
  extensions = [html()],
  onChange = (newValue) => {
    document.getElementById(id)?.setAttribute('data-value', newValue)
  },
}) => {
  useEffect(() => {
    document.getElementById(id)?.setAttribute('data-value', value)
  }, [id, value])

  return (
    <CodeMirror
      id={id}
      value={value}
      height={height}
      width={width}
      className={className}
      theme={codeEditorTheme}
      extensions={extensions}
      onChange={onChange}
    />
  )
}
