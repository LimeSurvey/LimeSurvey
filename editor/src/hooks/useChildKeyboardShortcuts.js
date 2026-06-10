import { useCallback } from 'react'

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
  const getKeyDownHandler = useCallback(
    (childId, index) => (event) => {
      if (isSurveyActive) return

      const isCtrlOrCmd = event.ctrlKey || event.metaKey

      // Ctrl+Enter or Cmd+Enter or Shift+Enter: Add new child after current
      if (event.key === 'Enter' && (isCtrlOrCmd || event.shiftKey)) {
        event.preventDefault()
        event.stopPropagation()
        handleChildAdd(children, entityType, { insertAfterIndex: index })
        return
      }

      // Ctrl+Delete / Ctrl+Backspace: Delete current child (only if more than 1)
      if (
        isCtrlOrCmd &&
        (event.key === 'Delete' || event.key === 'Backspace')
      ) {
        event.preventDefault()
        event.stopPropagation()
        if (children.length > 1) {
          handleChildDelete(childId, children, entityType)
        }
      }
    },
    [children, entityType, handleChildAdd, handleChildDelete, isSurveyActive]
  )

  return { getKeyDownHandler }
}
