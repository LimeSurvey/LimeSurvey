import {
  getNextQuestionCode,
  getNextSubQuestionCode,
  getNextAnswerCode,
  resolveCodePrefix,
} from '../questionAndAnswerCodeGenerators'

describe('questionAndAnswerCodeGenerators', () => {
  describe('getNextQuestionCode', () => {
    it('should generate the next question code', () => {
      const codeToQuestion = {
        Q001: {},
        Q002: {},
        Q003: {},
      }
      expect(getNextQuestionCode(codeToQuestion)).toBe('Q004')
    })

    it('should handle empty codeToQuestion object', () => {
      expect(getNextQuestionCode({})).toBe('Q001')
    })
  })

  describe('getNextSubQuestionCode', () => {
    const question = {
      qid: 1,
      subquestions: [{ title: 'SQ001' }, { title: 'SQ002' }],
    }

    it('should generate the next subquestion code', () => {
      expect(getNextSubQuestionCode(question)).toBe('SQ003')
    })

    it('should handle initial code', () => {
      expect(getNextSubQuestionCode(question, 'SQ005')).toBe('SQ006')
    })

    it('should handle empty subquestions', () => {
      const question = {
        qid: 1,
        subquestions: [],
      }

      expect(getNextSubQuestionCode(question)).toBe('1')
    })
  })

  describe('getNextAnswerCode', () => {
    const question = {
      qid: 1,
      answers: [{ code: 'A001' }, { code: 'A002' }],
    }

    it('should generate the next answer code', () => {
      expect(getNextAnswerCode(question)).toBe('A003')
    })

    it('should handle initial code', () => {
      expect(getNextAnswerCode(null, 'A005')).toBe('A006')
    })

    it('should handle empty answers', () => {
      const emptyQuestion = {
        qid: 1,
        answers: [],
      }
      expect(getNextAnswerCode(emptyQuestion)).toBe('1')
    })
  })

  describe('configurable prefixes', () => {
    const answers = (...codes) => ({
      qid: 1,
      answers: codes.map((code) => ({ code })),
    })
    const subquestions = (...titles) => ({
      qid: 1,
      subquestions: titles.map((title) => ({ title })),
    })

    it('generates numeric codes (no letter fallback) when no prefix is configured', () => {
      expect(getNextAnswerCode(answers(), null, undefined)).toBe('1')
      expect(getNextAnswerCode(answers(), null, null)).toBe('1')
      expect(getNextSubQuestionCode(subquestions(), null, undefined)).toBe('1')
      expect(getNextAnswerCode(null, 1)).toBe('2')
    })

    it('treats an empty prefix as a valid value and generates numeric codes', () => {
      expect(getNextAnswerCode(answers(), null, '')).toBe('1')
      expect(getNextSubQuestionCode(subquestions(), null, '')).toBe('1')
    })

    it('uses a custom prefix', () => {
      expect(getNextAnswerCode(answers(), null, 'AB')).toBe('AB001')
      expect(getNextSubQuestionCode(subquestions(), null, 'SUB')).toBe('SUB001')
    })

    it('keeps the 5 character limit for answer codes', () => {
      expect(getNextAnswerCode(answers(), null, 'ANS')).toHaveLength(5)
      expect(getNextAnswerCode(answers(), null, 'ANS')).toBe('ANS01')
      expect(getNextAnswerCode(answers('9999'), null, '')).toBe('10000')
    })

    it('seeds new questions by index', () => {
      expect(getNextAnswerCode(null, 0, '')).toBe('1')
      expect(getNextAnswerCode(null, 1, '')).toBe('2')
      expect(getNextAnswerCode(null, 3, '')).toBe('4')
      expect(getNextSubQuestionCode(null, 1, 'SUB')).toBe('SUB002')
    })

    it('continues existing numeric codes, keeping their width', () => {
      expect(getNextAnswerCode(answers('1', '2'), null, '')).toBe('3')
      expect(getNextAnswerCode(answers('01', '02'), null, 'AO')).toBe('03')
      expect(getNextSubQuestionCode(subquestions('001', '002'), null, '')).toBe(
        '003'
      )
      expect(getNextSubQuestionCode(subquestions('009'), null, '')).toBe('010')
      expect(getNextSubQuestionCode(subquestions('099'), null, '')).toBe('100')
      expect(getNextSubQuestionCode(subquestions('999'), null, '')).toBe('1000')
      expect(getNextAnswerCode(answers('9'), null, '')).toBe('10')
    })

    it('continues the greatest code regardless of gaps, never recoding', () => {
      expect(getNextAnswerCode(answers('1', '7', '3'), null, '')).toBe('8')
    })

    it('existing prefixed codes keep their prefix even if the setting is empty', () => {
      expect(getNextSubQuestionCode(subquestions('SQ001'), null, '')).toBe(
        'SQ002'
      )
      expect(getNextAnswerCode(answers('A001', 'A002'), null, '')).toBe('A003')
    })

    it('uses the configured prefix when existing codes are not numeric', () => {
      expect(getNextAnswerCode(answers('Y', 'N'), null, '')).toBe('1')
      expect(getNextAnswerCode(answers('Y', 'N'), null, 'AB')).toBe('AB001')
    })

    it('ignores invalid characters in the configured prefix', () => {
      expect(getNextSubQuestionCode(subquestions(), null, 'S-Q')).toBe('SQ001')
    })

    it('shortens the prefix instead of exceeding the 5 character limit', () => {
      expect(getNextAnswerCode(answers('ABC99', 'abc100'), null, 'ABC')).toBe(
        'ab101'
      )
    })

    it('does not mutate or depend on non-numeric legacy generation for questions', () => {
      expect(getNextQuestionCode({ 1: {} })).toBe('Q002')
    })
  })

  describe('resolveCodePrefix', () => {
    it('falls back to an empty prefix, never a letter prefix', () => {
      expect(resolveCodePrefix({ found: true, value: '' })).toBe('')
      expect(resolveCodePrefix({ found: true, value: 'AO' })).toBe('AO')
      expect(resolveCodePrefix({ found: false })).toBe('')
      expect(resolveCodePrefix(undefined)).toBe('')
      expect(resolveCodePrefix({ found: true, value: null })).toBe('')
    })
  })
})
