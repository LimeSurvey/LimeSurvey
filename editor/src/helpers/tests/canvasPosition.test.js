import {
  captureCanvasPosition,
  getFocusedCanvasElementId,
  restoreCanvasPosition,
} from 'helpers'

const WELCOME_SCREEN = { type: 'welcome' }
const END_SCREEN = { type: 'end' }

// Adds an element to the page whose top on screen is read from `tops`
const tops = {}
const addElement = (id, className = '', height = 100) => {
  const element = document.createElement('div')
  element.id = id
  element.className = className
  element.getBoundingClientRect = () => ({
    top: tops[element.id],
    bottom: tops[element.id] + height,
  })
  document.body.appendChild(element)
  return element
}

describe('getFocusedCanvasElementId', () => {
  test('maps the focused entity to its canvas element', () => {
    const get = (focused) =>
      getFocusedCanvasElementId(focused, WELCOME_SCREEN, END_SCREEN)

    expect(get({ qid: 12, gid: 3 })).toBe('12-question')
    expect(get({ gid: 3 })).toBe('question-group-3')
    expect(get({ info: WELCOME_SCREEN })).toBe('survey-header')
    expect(get({ info: END_SCREEN })).toBe('survey-footer-title')
    expect(get({})).toBeNull()
  })
})

describe('captureCanvasPosition and restoreCanvasPosition', () => {
  beforeEach(() => {
    // jsdom does not know the property, browsers start with ''
    document.documentElement.style.overflowAnchor = ''
    window.scrollBy = jest.fn()
    addElement('1-question', 'question') // Above the screen
    addElement('2-question', 'question') // Partly on screen
    addElement('3-question', 'question') // First one starting on screen
    addElement('4-question', 'question') // Focused
    Object.assign(tops, {
      '1-question': -500,
      '2-question': -50,
      '3-question': 100,
      '4-question': 300,
    })
  })

  afterEach(() => {
    document.body.innerHTML = ''
    document.documentElement.style.overflowAnchor = ''
  })

  test('keeps the focused element in place when it is on screen', () => {
    const position = captureCanvasPosition('4-question')
    expect(position).toEqual(
      expect.objectContaining({ elementId: '4-question', top: 300 })
    )
    expect(document.documentElement.style.overflowAnchor).toBe('none')

    tops['4-question'] = 1300
    restoreCanvasPosition(position)

    expect(window.scrollBy).toHaveBeenCalledWith({
      top: 1000,
      behavior: 'instant',
    })
    expect(document.documentElement.style.overflowAnchor).toBe('')
  })

  test('otherwise keeps the first question starting on screen that is not moved', () => {
    tops['4-question'] = 2000 // The focused question is off screen

    expect(captureCanvasPosition('4-question').elementId).toBe('3-question')
    expect(captureCanvasPosition('4-question', ['3-question']).elementId).toBe(
      '2-question'
    )
  })

  test('looks the element up again, since the reorder may replace it', () => {
    const position = captureCanvasPosition('4-question')
    document.getElementById('4-question').remove()
    addElement('4-question', 'question')
    tops['4-question'] = 250

    restoreCanvasPosition(position)

    expect(window.scrollBy).toHaveBeenCalledWith({
      top: -50,
      behavior: 'instant',
    })
  })

  test('does not scroll when the element did not move', () => {
    restoreCanvasPosition(captureCanvasPosition('4-question'))
    expect(window.scrollBy).not.toHaveBeenCalled()
    expect(document.documentElement.style.overflowAnchor).toBe('')
  })

  test('does nothing when no canvas element is on screen', () => {
    Object.keys(tops).forEach((id) => (tops[id] = 5000))
    expect(captureCanvasPosition('4-question')).toBeNull()
    expect(document.documentElement.style.overflowAnchor).toBe('')
  })
})
