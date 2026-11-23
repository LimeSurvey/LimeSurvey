// Keeps the survey canvas where it is on screen while the structure sidebar
// reorders it.
//
// Without this, the browser's scroll anchoring decides: It keeps the element
// at the top edge of the page in place, which is usually a question that is
// only partly visible. When a drop moves a question from above it to below
// it, the page scrolls along and the dropped question appears where the user
// was looking.

const CANVAS_QUESTIONS_SELECTOR = '.question[id$="-question"]'

const getTop = (element) => element.getBoundingClientRect().top

const isOnScreen = (element) => {
  const { top, bottom } = element.getBoundingClientRect()
  return bottom > 0 && top < window.innerHeight
}

/**
 * Returns the id of the canvas element that shows the focused entity.
 *
 * @param {object} focused The focused entity from useFocused().
 * @param {object} welcomeScreenInfo Question type info of the welcome screen.
 * @param {object} endScreenInfo Question type info of the end screen.
 * @returns {string|null} The element id, or null when nothing is focused.
 */
export const getFocusedCanvasElementId = (
  focused,
  welcomeScreenInfo,
  endScreenInfo
) => {
  if (focused?.qid != null) {
    return `${focused.qid}-question`
  }
  if (focused?.gid != null) {
    return `question-group-${focused.gid}`
  }
  if (focused?.info?.type === welcomeScreenInfo?.type) {
    return 'survey-header'
  }
  if (focused?.info?.type === endScreenInfo?.type) {
    return 'survey-footer-title'
  }
  return null
}

/**
 * Remembers where a canvas element is on screen, before a reorder, and
 * pauses the browser's scroll anchoring until restoreCanvasPosition().
 *
 * The element is the focused one if it is on screen. Otherwise it is the
 * first question that starts on screen (or, failing that, the first one that
 * is partly on screen) and is not being moved, so the page does not follow
 * the dragged item.
 *
 * @param {string|null} focusedElementId Id of the focused canvas element.
 * @param {string[]} movedElementIds Ids of the canvas elements being moved.
 * @returns {{elementId: string, top: number, overflowAnchor: string}|null}
 *   What restoreCanvasPosition() needs, or null when no element is on screen.
 */
export const captureCanvasPosition = (
  focusedElementId,
  movedElementIds = []
) => {
  const focusedElement =
    focusedElementId && document.getElementById(focusedElementId)
  let element =
    focusedElement && isOnScreen(focusedElement) ? focusedElement : null

  if (!element) {
    const questions = [
      ...document.querySelectorAll(CANVAS_QUESTIONS_SELECTOR),
    ].filter((question) => !movedElementIds.includes(question.id))
    element =
      questions.find((question) => {
        const top = getTop(question)
        return top >= 0 && top < window.innerHeight
      }) ??
      questions.find(isOnScreen) ??
      null
  }

  if (!element) {
    return null
  }

  const root = document.documentElement
  const position = {
    elementId: element.id,
    top: getTop(element),
    overflowAnchor: root.style.overflowAnchor,
  }
  root.style.overflowAnchor = 'none'
  return position
}

/**
 * Scrolls the page so the element remembered by captureCanvasPosition() is
 * back at its old place on screen, and resumes the browser's scroll
 * anchoring. Call it after the reorder is in the DOM and before it is painted.
 *
 * @param {{elementId: string, top: number, overflowAnchor: string}} position
 *   The result of captureCanvasPosition().
 * @returns {void}
 */
export const restoreCanvasPosition = ({ elementId, top, overflowAnchor }) => {
  // Looked up again: The element may have been replaced by the reorder
  const element = document.getElementById(elementId)
  if (element) {
    const shift = getTop(element) - top
    if (shift !== 0) {
      // Instant: The editor's smooth scrolling would show the page sliding
      window.scrollBy({ top: shift, behavior: 'instant' })
    }
  }
  document.documentElement.style.overflowAnchor = overflowAnchor
}
