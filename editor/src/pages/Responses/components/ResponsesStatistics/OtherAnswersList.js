import { useEffect, useRef, useState } from 'react'
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
export const OtherAnswersList = ({ id, item, onViewComments }) => {
  const [visibleCount, setVisibleCount] = useState(VISIBLE_OTHER_ANSWERS)
  // Row to focus after "Show more", so keyboard users continue at the new rows.
  const focusRowRef = useRef(null)
  const listRef = useRef(null)
  const answers = item?.otherAnswers ?? []
  const noTextCount =
    (item?.value ?? 0) - answers.reduce((sum, { count }) => sum + count, 0)
  const rows = [
    ...answers,
    ...(noTextCount > 0 ? [{ text: '', count: noTextCount }] : []),
  ].sort((a, b) => b.count - a.count)
  const visibleRows = rows.slice(0, visibleCount)

  useEffect(() => {
    if (focusRowRef.current !== null) {
      listRef.current?.children[focusRowRef.current]?.focus()
      focusRowRef.current = null
    }
  }, [visibleCount])

  const showMore = () => {
    focusRowRef.current = visibleCount
    setVisibleCount((count) => count + OTHER_ANSWERS_PAGE)
  }

  return (
    <div id={id} className="responses-statistics-other-answers">
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
      <ul ref={listRef} className="responses-statistics-other-answers-list">
        {visibleRows.map(({ text, count }) => (
          <li
            key={text}
            tabIndex={-1}
            className="responses-statistics-other-answers-row"
          >
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
            onClick={showMore}
          >
            {t('Show more')}
          </button>
        </div>
      )}
    </div>
  )
}
