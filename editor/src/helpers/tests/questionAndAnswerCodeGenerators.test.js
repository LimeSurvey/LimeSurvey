import {
  getNextQuestionCode,
  getNextSubQuestionCode,
  getNextAnswerCode,
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

      expect(getNextSubQuestionCode(question)).toBe('SQ001')
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
      expect(getNextAnswerCode(emptyQuestion)).toBe('A001')
    })
  })
})
