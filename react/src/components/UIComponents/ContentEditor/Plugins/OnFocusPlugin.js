import { useEffect } from 'react'
import { useLexicalComposerContext } from '@lexical/react/LexicalComposerContext'
import { COMMAND_PRIORITY_EDITOR, FOCUS_COMMAND } from 'lexical'

export const OnFocusPlugin = ({ editorValue, setIsFocusChange }) => {
  const [editor] = useLexicalComposerContext()

  useEffect(() => {
    const unsubscribe = editor.registerCommand(
      FOCUS_COMMAND,
      () => {
        setIsFocusChange(true)
        editor.setEditorState(editor.parseEditorState(editorValue))
      },
      COMMAND_PRIORITY_EDITOR
    )

    return () => {
      unsubscribe()
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [editorValue])

  return null
}
