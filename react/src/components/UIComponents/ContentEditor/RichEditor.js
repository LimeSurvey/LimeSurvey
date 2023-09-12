import { useEffect, useState } from 'react'
import { HeadingNode } from '@lexical/rich-text'
import { LexicalComposer } from '@lexical/react/LexicalComposer'
import { HistoryPlugin } from '@lexical/react/LexicalHistoryPlugin'
import { ClearEditorPlugin } from '@lexical/react/LexicalClearEditorPlugin'

import {
  AutoFocusPlugin,
  OnChangePlugin,
  RichTextPlugin,
  ToolbarPlugin,
} from './Plugins'

export const RichEditor = ({
  onFocus,
  onBlur,
  showToolbar,
  disabled,
  handleOnChange,
  focus,
  value,
  placeholder,
}) => {
  const [questionTitle, setQuestionTitle] = useState(value)
  const [isFocused, setIsFocused] = useState(false)

  const handleFocus = () => {
    onFocus()
    setIsFocused(true)
  }

  const handleBlur = () => {
    onBlur()
    setIsFocused(false)
  }

  const onError = (error) => {
    console.error(error)
  }

  const initConfig = {
    nodes: [HeadingNode],
    theme: {
      text: {
        bold: 'textBold',
        italic: 'textItalic',
      },
    },
    onError,
  }

  useEffect(() => {
    setQuestionTitle(value)
  }, [value])

  return (
    <div onFocus={handleFocus} onBlur={handleBlur}>
      <LexicalComposer initialConfig={initConfig}>
        <OnChangePlugin
          disabled={disabled}
          onChange={handleOnChange}
          value={questionTitle}
          isFocused={isFocused}
        />
        <HistoryPlugin />
        <ClearEditorPlugin />
        <AutoFocusPlugin focus={focus} />
        <RichTextPlugin value={value} placeholder={placeholder} />
        <ToolbarPlugin value={questionTitle} showToolbar={showToolbar} />
      </LexicalComposer>
    </div>
  )
}
