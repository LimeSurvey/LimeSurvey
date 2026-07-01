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
    const codeToQuestion = {
      Q001: {
        question: {
          qid: 1,
          subquestions: [{ title: 'SQ001' }, { title: 'SQ002' }],
        },
      },
    }

    it('should generate the next subquestion code', () => {
      expect(getNextSubQuestionCode(codeToQuestion, 1)).toBe('SQ003')
    })

    it('should handle initial code', () => {
      expect(getNextSubQuestionCode(codeToQuestion, null, 'SQ005')).toBe(
        'SQ006'
      )
    })

    it('should handle empty subquestions', () => {
      const emptyCodeToQuestion = {
        Q001: {
          question: {
            qid: 1,
            subquestions: [],
          },
        },
      }
      expect(getNextSubQuestionCode(emptyCodeToQuestion, 1)).toBe('SQ001')
    })
  })

  describe('getNextAnswerCode', () => {
    const codeToQuestion = {
      Q001: {
        question: {
          qid: 1,
          answers: [{ code: 'A001' }, { code: 'A002' }],
        },
      },
    }

    it('should generate the next answer code', () => {
      expect(getNextAnswerCode(codeToQuestion, 1)).toBe('A003')
    })

    it('should handle initial code', () => {
      expect(getNextAnswerCode(codeToQuestion, null, 'A005')).toBe('A006')
    })

    it('should handle empty answers', () => {
      const emptyCodeToQuestion = {
        Q001: {
          question: {
            qid: 1,
            answers: [],
          },
        },
      }
      expect(getNextAnswerCode(emptyCodeToQuestion, 1)).toBe('A001')
    })
  })
})
