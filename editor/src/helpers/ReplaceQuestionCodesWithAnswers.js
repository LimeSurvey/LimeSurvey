import Mexp from 'math-expression-evaluator'

import { RemoveHTMLTagsInString } from './RemoveHTMLTagsInString'

export const ReplaceQuestionCodesWithAnswers = (value, codeToQuestion = {}) => {
  const questionValue = value
  const usedVariables = {}
  const mexp = new Mexp()

  const result = questionValue.replace(
    /\{([^}]+)\}/g,
    (match, variableString) => {
      let hasUndefined = false
      const variableStringWithoutHTML = RemoveHTMLTagsInString(variableString)
      const variables = variableStringWithoutHTML
        .split(/([+\-*/])/)
        .map((variable) => variable.trim())

      for (const variable of variables) {
        if (codeToQuestion.hasOwnProperty(variable)) {
          usedVariables[variable] = codeToQuestion[variable]
          const answerExample = codeToQuestion[variable].question.answerExample
          if (!answerExample) {
            hasUndefined = true
          }

          variableString = variableString.replace(
            variable,
            answerExample ? answerExample : ''
          )
        }
      }

      if (hasUndefined) {
        return ''
      }

      try {
        return mexp.eval(RemoveHTMLTagsInString(variableString))
      } catch {
        return variableString
      }
    }
  )

  return result
}
