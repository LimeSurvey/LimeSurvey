import { useEffect } from 'react'
import { useLexicalComposerContext } from '@lexical/react/LexicalComposerContext'
import { BLUR_COMMAND, COMMAND_PRIORITY_EDITOR } from 'lexical'
import { useAppState } from 'hooks'
import { L10ns } from 'helpers'

export const OnBlurPlugin = ({ setIsBlurEvent }) => {
  const [editor] = useLexicalComposerContext()
  const [codeToQuestion] = useAppState('codeToQuestion', {})

  useEffect(() => {
    editor.registerCommand(
      BLUR_COMMAND,
      () => {
        setIsBlurEvent(true)
        const editorState = editor.getEditorState().toJSON()
        // can be undefined
        for (const children of editorState.root.children) {
          let text = children.children[0].text
          if (typeof text !== 'string') {
            return
          }

          text = text.replace(/\{([^}]+)\}/g, (match, variableString) => {
            const variables = variableString.split(/([+\-*/])/)

            for (const variable of variables) {
              if (codeToQuestion.hasOwnProperty(variable)) {
                variableString = variableString.replace(
                  variable,
                  L10ns({
                    prop: 'question',
                    language: 'en',
                    l10ns: codeToQuestion[variable].question.l10ns,
                  })
                )
              }
            }

            return variableString
          })

          children.children[0].text = text
        }

        setTimeout(() => {
          editor.setEditorState(editor.parseEditorState(editorState))
        }, 1)
      },
      COMMAND_PRIORITY_EDITOR
    )
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  return null
}
