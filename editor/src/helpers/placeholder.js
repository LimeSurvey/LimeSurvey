// placeholder.js
// Utility functions for managing and formatting question placeholders
// within the context of a survey.

export const extractPlaceholders = (text) => {
  return (text.match(/{([^}]+)}/g) || []).map((match) =>
    match.slice(1, -1).replace(/TOKEN:/g, '')
  )
}

export const getQuestionPlaceholders = (language, question) => {
  const questionText = question?.l10ns?.[language]?.question
  return questionText ? extractPlaceholders(questionText) : []
}

export const isPlaceholderInUse = (language, question, placeholderKey) => {
  return getQuestionPlaceholders(language, question).includes(placeholderKey)
}
