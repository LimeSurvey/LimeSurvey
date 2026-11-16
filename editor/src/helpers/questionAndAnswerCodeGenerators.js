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
    // Answers numbered 1, 2, 3 (e.g. by getNumericAnswerCodes) continue that scheme
    if (
      answerTitles.length > 0 &&
      answerTitles.every((code) => /^[1-9]\d*$/.test(code))
    ) {
      return String(Math.max(...answerTitles.map(Number)) + 1)
    }
  } else {
    answerTitles.push(initialCode.toString())
  }
  const maxCode = findMaxCode(answerTitles, 'A')
  const newNumeric = maxCode.numeric + 1
  return `${maxCode.prefix}${newNumeric.toString().padStart(3, '0')}`
}

/**
 * Renumbers answer options 1..n (no prefix) per scale, following sortOrder.
 * Every code is replaced, numeric ones included (10, 20, 30 become 1, 2, 3);
 * running it again on the result changes nothing.
 */
export const getNumericAnswerCodes = (answers = []) => {
  const counters = {}
  const sorted = [...answers].sort((a, b) => a.sortOrder - b.sortOrder)
  const codes = new Map()
  sorted.forEach((answer) => {
    const scale = answer.scaleId ?? 0
    counters[scale] = (counters[scale] || 0) + 1
    codes.set(answer, String(counters[scale]))
  })
  return answers.map((answer) => ({ ...answer, code: codes.get(answer) }))
}

export const hasNumericAnswerCodes = (answers = []) =>
  getNumericAnswerCodes(answers).every(
    (answer, index) => answer.code === answers[index].code
  )
