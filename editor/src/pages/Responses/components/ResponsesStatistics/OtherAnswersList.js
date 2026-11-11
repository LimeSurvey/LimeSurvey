import { useState } from 'react'
import { format } from 'util'

export const VISIBLE_OTHER_ANSWERS = 5
export const OTHER_ANSWERS_PAGE = 50

const formatResponses = (count) =>
  count === 1
    ? format(t('%s response'), count)
    : format(t('%s responses'), count)

/**
 * The free-text answers of the "other" option, most frequent first.
 */
export const OtherAnswersList = ({ item, onViewComments }) => {
  const [visibleCount, setVisibleCount] = useState(VISIBLE_OTHER_ANSWERS)
  const answers = item?.otherAnswers ?? []
  const noTextCount =
    (item?.value ?? 0) - answers.reduce((sum, { count }) => sum + count, 0)
  const rows = [
    ...answers,
    ...(noTextCount > 0 ? [{ text: '', count: noTextCount }] : []),
  ].sort((a, b) => b.count - a.count)
  const visibleRows = rows.slice(0, visibleCount)

  return (
    <div className="responses-statistics-other-answers">
      <div className="responses-statistics-other-answers-header">
        <h3 className="responses-statistics-other-answers-title">
          {t('All responses for "Other"')}
        </h3>
        {onViewComments && (
          <button
            type="button"
            className="responses-statistics-comments-more-btn"
            onClick={onViewComments}
          >
            {t('View comments')}
          </button>
        )}
      </div>
      <ul className="responses-statistics-other-answers-list">
        {visibleRows.map(({ text, count }) => (
          <li key={text} className="responses-statistics-other-answers-row">
            <span className="responses-statistics-other-answers-text">
              {text || <em>{t('No text')}</em>}
            </span>
            <span>{formatResponses(count)}</span>
          </li>
        ))}
      </ul>
      {visibleCount < rows.length && (
        <div className="responses-statistics-comments-more">
          <button
            type="button"
            className="responses-statistics-comments-more-btn"
            onClick={() =>
              setVisibleCount((count) => count + OTHER_ANSWERS_PAGE)
            }
          >
            {t('Show more')}
          </button>
        </div>
      )}
    </div>
  )
}
