import CodeMirror from '@uiw/react-codemirror'
import { html } from '@codemirror/lang-html'
import { EditorView } from '@codemirror/view'
import { useEffect } from 'react'

// basicSetup (included by @uiw/react-codemirror by default) already provides
// autocompletion, closeBrackets, bracketMatching, and indentOnInput.
// We only need to add the HTML language extension on top.
export const htmlExtensions = [html({ autoCloseTags: true })]

const codeEditorTheme = EditorView.theme({
  '&': {
    backgroundColor: '#ffffff',
    border: '2px solid #6e748c',
    borderRadius: '2px',
    padding: '12px 4px',
  },
  '&.cm-focused': {
    outline: 'none',
  },
  '.cm-gutters': {
    backgroundColor: '#f8f9fa',
    color: '#1E1E1E',
    border: 'none',
    maxWidth: '24px',
  },
  '.cm-lineNumbers': {
    fontSize: '10px',
    color: '#1E1E1E',
    textAlign: 'center',
  },
  '.cm-gutterElement': {
    display: 'flex',
    justifyContent: 'center',
    alignItems: 'center',
    padding: '0 2px !important',
  },
  '.cm-lineWrapping': {
    padding: '0',
  },
  '.cm-activeLineGutter': {
    backgroundColor: '#dddee8',
    padding: '0 2px !important',
  },
  '.cm-content': {
    fontSize: '14px',
    color: '#1E1E1E',
  },
  '.cm-selectionBackground': {
    backgroundColor: '#b3d4ff',
  },
  '&.cm-focused > .cm-scroller > .cm-selectionLayer .cm-selectionBackground': {
    backgroundColor: '#b3d4ff',
  },
  '.cm-activeLine': {
    backgroundColor: '#f0f0f090',
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
  extensions = htmlExtensions,
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
