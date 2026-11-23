import { typeChangeKeepsAnswerOptions } from './typeChangeKeepsAnswerOptions'

// Same shape as the survey's answerOptionLayouts from the API
const layouts = {
  'L': '1-0',
  '!': '1-0',
  'O': '1-0',
  'F': '1-1',
  'H': '1-1',
  '1': '2-1',
}

describe('typeChangeKeepsAnswerOptions', () => {
  test.each([
    ['L', '!'],
    ['!', 'O'],
    ['F', 'H'],
  ])('keeps answer options from %s to %s', (oldType, newType) => {
    expect(typeChangeKeepsAnswerOptions(layouts, oldType, newType)).toBe(true)
  })

  test.each([
    ['L', 'S'],
    ['L', 'F'],
    ['F', '1'],
    ['S', 'T'],
  ])('deletes answer options from %s to %s', (oldType, newType) => {
    expect(typeChangeKeepsAnswerOptions(layouts, oldType, newType)).toBe(false)
  })

  test('deletes answer options when the layouts are unknown', () => {
    expect(typeChangeKeepsAnswerOptions(undefined, 'L', '!')).toBe(false)
  })
})
