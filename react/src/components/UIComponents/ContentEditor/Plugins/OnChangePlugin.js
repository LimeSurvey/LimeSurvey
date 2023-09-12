import { useCallback, useEffect, useState } from 'react'
import {
  $insertNodes,
  $getSelection,
  $isRangeSelection,
  KEY_ENTER_COMMAND,
  COMMAND_PRIORITY_LOW,
  KEY_ARROW_UP_COMMAND,
} from 'lexical'
import { $setBlocksType } from '@lexical/selection'
import { $createHeadingNode } from '@lexical/rich-text'

import { useLexicalComposerContext } from '@lexical/react/LexicalComposerContext'
import { $generateHtmlFromNodes, $generateNodesFromDOM } from '@lexical/html'
import { OnChangePlugin as LexicalOnChangePlugin } from '@lexical/react/LexicalOnChangePlugin'

export const OnChangePlugin = ({ value, onChange, disabled, isFocused }) => {
  const [editor] = useLexicalComposerContext()
  const [isFirstRender, setIsFirstRender] = useState(true)

  const updateFontSize = useCallback(() => {
    const lastChildIndex =
      editor?.toJSON()?.editorState?.root?.children?.length - 1
    const headingTag =
      editor?.toJSON()?.editorState?.root?.children[lastChildIndex]?.tag

    if (!headingTag || isFirstRender) {
      return
    }

    setTimeout(() => {
      editor.update(() => {
        const selection = $getSelection()
        if ($isRangeSelection(selection)) {
          $setBlocksType(selection, () => $createHeadingNode(headingTag))
        }
      })
    }, 0)
  }, [editor, isFirstRender])

  useEffect(() => {
    if (!value || !isFirstRender) {
      return
    }

    setIsFirstRender(false)

    // disabled while inserting nodes to prevent scrolling.
    editor.setEditable(false)

    editor.update(() => {
      const parser = new DOMParser()
      const dom = parser.parseFromString(value, 'text/html')
      const nodes = $generateNodesFromDOM(editor, dom)

      // inserts and focuses the editor which might cause a scroll to the end of the page.
      // it doesn't focus if the editor.editable is false.
      $insertNodes(nodes)

      if (!disabled) {
        setTimeout(() => {
          editor.setEditable(true)
        }, 1)
      }
    })

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [editor, disabled])

  useEffect(() => {
    // We want the whole text to have the same fontSize/Heading
    // Basically we check if there's new nodes/lines added and apply the current heading if exist to that new node/line
    editor.registerCommand(
      KEY_ENTER_COMMAND,
      () => {
        updateFontSize()
        return false
      },
      COMMAND_PRIORITY_LOW
    )

    // We want the whole text to have the same fontSize/Heading
    // Check if there's new nodes/lines added and apply the current heading if exist to that new node/line
    editor.registerCommand(
      KEY_ARROW_UP_COMMAND,
      () => {
        updateFontSize()
        return false
      },
      COMMAND_PRIORITY_LOW
    )
  }, [editor, updateFontSize])

  return (
    <LexicalOnChangePlugin
      onChange={(editorState) => {
        editorState.read(() => {
          const htmlValue = $generateHtmlFromNodes(editor)
          onChange(htmlValue)
        })
      }}
    />
  )
}
