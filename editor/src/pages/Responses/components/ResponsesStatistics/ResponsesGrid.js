import { useEffect, useMemo, useState } from 'react'
import { format } from 'util'
import classNames from 'classnames'

import { HighlightedText, SearchInput, useSearchTerms } from 'components'
import { useQuestionResponses } from 'hooks'
import { useIsInViewport } from 'hooks/useInViewport'

import { StatisticsDetailModal } from './StatisticsDetailModal.js'
import { formatAnswerDate } from './ChartsUtils.js'

// The card shows a short preview; the full list lives in the modal.
const INLINE_LIMIT = 5

/**
 * Per-response answers of a single-field question (short/long/huge text,
 * numerical) as a list of "answer — when" rows; numerical uses two columns.
 */
export const ResponsesGrid = ({
  surveyId,
  questionCode,
  title = '',
  fields,
  filters,
  twoColumns = false,
}) => {
  const [containerRef, isInView] = useIsInViewport(null, {
    initialInView: false,
  })
  const [shouldLoad, setShouldLoad] = useState(false)
  const [showModal, setShowModal] = useState(false)
  useEffect(() => {
    if (isInView) {
      setShouldLoad(true)
    }
  }, [isInView])

  const { terms, setTerms, setTyped, search } = useSearchTerms()

  // The backend matches the global panel terms too, so highlight both.
  const highlightTerms = useMemo(
    () => [...new Set([...(filters?.search ?? []), ...search])],
    [filters, search]
  )

  const {
    columns,
    rows,
    totalResults,
    isLoading,
    hasNextPage,
    fetchNextPage,
    isFetchingNextPage,
  } = useQuestionResponses(surveyId, questionCode, {
    enabled: shouldLoad,
    fields,
    filters,
    search,
  })

  const answerKey = columns[0]?.key
  const answers = useMemo(
    () =>
      rows
        .map((row) => ({
          id: row.responseId,
          value: row.cells?.[answerKey] ?? '',
          date: row.date,
        }))
        .filter((row) => row.value !== '' && row.value != null),
    [rows, answerKey]
  )

  const searchBlock = (
    <div className="responses-statistics-array-text-search">
      <SearchInput
        terms={terms}
        onChange={setTerms}
        onTyping={setTyped}
        placeholder={t('Search responses')}
      />
      {search.length > 0 && totalResults != null && (
        <span className="responses-statistics-search-results">
          {totalResults === 1
            ? t('1 result found')
            : format(t('%s results found'), totalResults)}
        </span>
      )}
    </div>
  )

  const emptyState = (
    <div className="responses-statistics-empty">
      {search.length
        ? t('No responses match your search.')
        : t('There are no responses for this question yet.')}
    </div>
  )

  const answersGrid = (items) => (
    <div
      className={classNames('responses-statistics-grid', {
        'responses-statistics-grid--two-col': twoColumns,
      })}
    >
      {items.map((answer) => (
        <div className="responses-statistics-grid-row" key={answer.id}>
          <span className="responses-statistics-grid-answer">
            <HighlightedText text={answer.value} terms={highlightTerms} />
          </span>
          <span className="responses-statistics-grid-date">
            {formatAnswerDate(answer.date)}
          </span>
        </div>
      ))}
    </div>
  )

  const openModal = () => {
    setShowModal(true)
    if (hasNextPage && !isFetchingNextPage) {
      fetchNextPage()
    }
  }

  const renderContent = () => {
    if (!shouldLoad || isLoading) {
      return (
        <div className="responses-statistics-comments-status">
          <span className="loader"></span>
        </div>
      )
    }

    if (!answers.length) {
      return emptyState
    }

    return (
      <>
        {answersGrid(answers.slice(0, INLINE_LIMIT))}
        {(hasNextPage || answers.length > INLINE_LIMIT) && (
          <div className="responses-statistics-comments-more">
            <button
              type="button"
              className="responses-statistics-comments-more-btn"
              onClick={openModal}
            >
              {t('Load more')}
            </button>
          </div>
        )}
      </>
    )
  }

  return (
    <div ref={containerRef}>
      {shouldLoad && searchBlock}
      {renderContent()}
      <StatisticsDetailModal
        show={showModal}
        onHide={() => setShowModal(false)}
        modalClassname="responses-statistics-responses-modal"
      >
        <div className="responses-statistics-comments">
          <h2 className="responses-statistics-modal-title">{title}</h2>
          {searchBlock}
          {answers.length ? answersGrid(answers) : emptyState}
          {hasNextPage && (
            <div className="responses-statistics-comments-more">
              <button
                type="button"
                className="responses-statistics-comments-more-btn"
                onClick={() => fetchNextPage()}
                disabled={isFetchingNextPage}
              >
                {isFetchingNextPage ? t('Loading...') : t('Load more')}
              </button>
            </div>
          )}
        </div>
      </StatisticsDetailModal>
    </div>
  )
}
