import { useEffect } from 'react'
import { useLexicalComposerContext } from '@lexical/react/LexicalComposerContext'

export const AutoFocusPlugin = ({ focus = false }) => {
  const [editor] = useLexicalComposerContext()

  useEffect(() => {
    if (focus) {
      setTimeout(() => {
        editor.focus()
      }, 0)
    }
  }, [editor, focus])

  return null
}
