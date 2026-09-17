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

// Placeholders are highlighted with a <badge> tag so researchers can see at a
// glance that a question or answer uses one. See the `badge` rule in
// themes/contenteditor/content-editor.scss for the styling.
const PLACEHOLDER_REGEX = /(\{[^{}]+\})/g
const PLACEHOLDER_BADGE_REGEX = /<badge>(\{[^{}]+\})<\/badge>/g

export const wrapPlaceholdersInBadges = (text = '') => {
  return removePlaceholderBadges(text).replace(
    PLACEHOLDER_REGEX,
    '<badge>$1</badge>'
  )
}

export const removePlaceholderBadges = (text = '') => {
  return text.replace(PLACEHOLDER_BADGE_REGEX, '$1')
}
