import { countChipsOnOneLine } from './countChipsOnOneLine'

const GAP = 8
const TOGGLE = 80

describe('countChipsOnOneLine', () => {
  test('shows every chip when they all fit, without room for the button', () => {
    expect(countChipsOnOneLine([100, 100, 100], 316, TOGGLE, GAP)).toBe(3)
  })

  test('keeps room for the button when not all chips fit', () => {
    expect(countChipsOnOneLine([100, 100, 100, 100], 300, TOGGLE, GAP)).toBe(2)
  })

  test('drops a chip that only fits without the button', () => {
    expect(countChipsOnOneLine([100, 100, 100, 100], 320, TOGGLE, GAP)).toBe(2)
  })

  test('always shows at least one chip', () => {
    expect(countChipsOnOneLine([240, 240], 200, TOGGLE, GAP)).toBe(1)
  })

  test('shows nothing when there is nothing to show', () => {
    expect(countChipsOnOneLine([], 300, TOGGLE, GAP)).toBe(0)
  })
})
