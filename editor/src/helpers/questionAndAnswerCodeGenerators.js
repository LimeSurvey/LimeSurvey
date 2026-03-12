const findMaxCode = (codes, defaultPrefix) => {
  return codes.reduce(
    (max, code) => {
      const match = code.match(/^([^\d]*)(\d+)$/)
      if (match) {
        const [, prefix, numericPart] = match
        const numericCode = parseInt(numericPart, 10)
        return numericCode > max.numeric
          ? { numeric: numericCode, prefix: prefix || defaultPrefix }
          : max
      }
      return max
    },
    { numeric: 0, prefix: defaultPrefix }
  )
}

const findQuestionById = (codeToQuestion, questionId) => {
  if (codeToQuestion) {
    for (const questionData of Object.values(codeToQuestion)) {
      if (questionData.question && questionData.question.qid === questionId) {
        return questionData.question
      }
    }
  }
  return null // Return null if no matching question is found
}

export const getNextQuestionCode = (codeToQuestion) => {
  const maxCode = findMaxCode(Object.keys(codeToQuestion), 'Q')
  const newNumeric = maxCode.numeric + 1
  return `${maxCode.prefix}${newNumeric.toString().padStart(3, '0')}`
}

export const getNextSubQuestionCode = (
  codeToQuestion,
  questionId,
  initialCode = null
) => {
  let subquestionTitles = []
  if (
    (initialCode === null || initialCode === undefined) &&
    (questionId !== null || questionId !== undefined) &&
    (codeToQuestion !== null || codeToQuestion !== undefined)
  ) {
    const question = findQuestionById(codeToQuestion, questionId)
    const subquestions = question?.subquestions ?? []
    subquestionTitles = subquestions.map((sq) => sq.title).filter(Boolean)
  } else {
    subquestionTitles.push(initialCode.toString())
  }
  const maxCode = findMaxCode(subquestionTitles, 'SQ')
  const newNumeric = maxCode.numeric + 1
  return `${maxCode.prefix}${newNumeric.toString().padStart(3, '0')}`
}

export const getNextAnswerCode = (
  codeToQuestion,
  questionId = null,
  initialCode = null
) => {
  let answerTitles = []
  let question = null
  if (
    (codeToQuestion !== null || codeToQuestion !== undefined) &&
    (questionId !== null || questionId !== undefined)
  ) {
    // If there are answers for the question check the prefix and the numeric part
    question = findQuestionById(codeToQuestion, questionId)
  }
  if (question) {
    const answers = question.answers ? question.answers : []
    answerTitles = answers.map((a) => a.code).filter(Boolean)
  } else {
    // If no question or questionId provided, just use the initial code
    answerTitles.push(initialCode.toString())
  }
  const maxCode = findMaxCode(answerTitles, 'A')
  const newNumeric = maxCode.numeric + 1
  return `${maxCode.prefix}${newNumeric.toString().padStart(3, '0')}`
}
