import { isDefaultRelevance } from './isDefaultRelevance'

describe('isDefaultRelevance', () => {
  test.each(['1', ' 1 ', '', '   ', null, undefined])(
    '%p means "always shown"',
    (relevance) => {
      expect(isDefaultRelevance(relevance)).toBe(true)
    }
  )

  test.each(['((Q1.NAOK == "A1"))', '0', 'Q1.NAOK > 1'])(
    '%p is an expression',
    (relevance) => {
      expect(isDefaultRelevance(relevance)).toBe(false)
    }
  )
})
