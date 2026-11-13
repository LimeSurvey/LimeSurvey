import { QUESTION_RELEVANCE_DEFAULT_VALUE } from './constants'

/**
 * Whether a question's relevance means "always shown": The default "1", or
 * empty, e.g. for questions imported from older surveys.
 *
 * @param {string|null|undefined} relevance The relevance of the question.
 * @returns {boolean} True if the question has no condition in its relevance.
 */
export const isDefaultRelevance = (relevance) => {
  const value = (relevance ?? '').trim()
  return value === '' || value === QUESTION_RELEVANCE_DEFAULT_VALUE
}
