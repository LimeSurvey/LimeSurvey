import { useCallback, useRef } from 'react'

/**
 * Provides keyboard shortcuts for answer/subquestion editing:
 * - Ctrl+Enter / Cmd+Enter: Add new item after current
 * - Shift+Enter: Add new item after current (alternative)
 * - Ctrl+Delete / Cmd+Delete / Ctrl+Backspace / Cmd+Backspace: Delete current item
 */
export const useChildKeyboardShortcuts = ({
  children,
  entityType,
  handleChildAdd,
  handleChildDelete,
  isSurveyActive,
}) => {
  const childrenRef = useRef(children)
  childrenRef.current = children

  const getKeyDownHandler = useCallback(
    (childId, index) => (event) => {
      if (isSurveyActive) return
      if (event.defaultPrevented || event.repeat) return

      const isCtrlOrCmd = event.ctrlKey || event.metaKey
      const isEnterKey =
        event.key === 'Enter' ||
        event.code === 'Enter' ||
        event.code === 'NumpadEnter'
      const currentChildren = childrenRef.current || []

      if (isCtrlOrCmd && event.shiftKey) {
        return
      }

      // Ctrl+Enter or Cmd+Enter or Shift+Enter: Add new child after current
      if (isEnterKey && (isCtrlOrCmd || event.shiftKey)) {
        event.preventDefault()
        event.stopPropagation()
        event.nativeEvent?.stopImmediatePropagation?.()
        const currentIndex = currentChildren.findIndex(
          (child) => child.qid === childId || child.aid === childId
        )
        handleChildAdd(currentChildren, entityType, {
          insertAfterIndex: currentIndex >= 0 ? currentIndex : index,
        })
        return
      }

      // Ctrl+Delete / Ctrl+Backspace: Delete current child (only if more than 1)
      if (
        isCtrlOrCmd &&
        (event.key === 'Delete' || event.key === 'Backspace')
      ) {
        event.preventDefault()
        event.stopPropagation()
        event.nativeEvent?.stopImmediatePropagation?.()
        if (currentChildren.length > 1) {
          handleChildDelete(childId, currentChildren, entityType)
        }
      }
    },
    [entityType, handleChildAdd, handleChildDelete, isSurveyActive]
  )

  return { getKeyDownHandler }
}
