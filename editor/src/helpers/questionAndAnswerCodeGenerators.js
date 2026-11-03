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

export const getNextQuestionCode = (codeToQuestion) => {
  const maxCode = findMaxCode(Object.keys(codeToQuestion), 'Q')
  const newNumeric = maxCode.numeric + 1
  return `${maxCode.prefix}${newNumeric.toString().padStart(3, '0')}`
}

export const getNextSubQuestionCode = (question, initialCode = null) => {
  let subquestionTitles = []
  if (
    (initialCode === null || initialCode === undefined) &&
    question.qid !== null &&
    question.qid !== undefined
  ) {
    const subquestions = question?.subquestions ?? []
    subquestionTitles = subquestions.map((sq) => sq.title).filter(Boolean)
  } else {
    subquestionTitles.push(initialCode.toString())
  }
  const maxCode = findMaxCode(subquestionTitles, 'SQ')
  const newNumeric = maxCode.numeric + 1
  return `${maxCode.prefix}${newNumeric.toString().padStart(3, '0')}`
}

export const getNextAnswerCode = (question, initialCode = null) => {
  let answerTitles = []
  if (
    (initialCode === null || initialCode === undefined) &&
    question.qid !== null &&
    question.qid !== undefined
  ) {
    const answers = question?.answers ?? []
    answerTitles = answers.map((a) => a.code).filter(Boolean)
  } else {
    answerTitles.push(initialCode.toString())
  }
  const maxCode = findMaxCode(answerTitles, 'A')
  const newNumeric = maxCode.numeric + 1
  return `${maxCode.prefix}${newNumeric.toString().padStart(3, '0')}`
}
