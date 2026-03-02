import CodeMirror from '@uiw/react-codemirror'
import { html } from '@codemirror/lang-html'
import { useEffect } from 'react'

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
      extensions={extensions}
      onChange={onChange}
    />
  )
}
