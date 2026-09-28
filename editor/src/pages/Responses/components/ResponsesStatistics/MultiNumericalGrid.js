import React, { useEffect, useMemo, useState } from 'react'
import { format } from 'util'

import { HighlightedText, SearchInput, useSearchTerms } from 'components'
import { htmlToPlainText } from 'helpers'
import { useQuestionResponses } from 'hooks'
import { useIsInViewport } from 'hooks/useInViewport'

import { formatAnswerDate } from './ChartsUtils.js'

/**
 * Per-response answers of a multiple numerical input (K): one card per
 * participant, listing each subquestion and the number they entered.
 */
export const MultiNumericalGrid = ({
  surveyId,
  questionCode,
  fields,
  filters,
}) => {
  const [containerRef, isInView] = useIsInViewport(null, {
    initialInView: false,
  })
  const [shouldLoad, setShouldLoad] = useState(false)
  useEffect(() => {
    if (isInView) {
      setShouldLoad(true)
    }
  }, [isInView])

  const { terms, setTerms, setTyped, search } = useSearchTerms()

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

  // One card per response: the subquestion label and the entered number.
  const cards = useMemo(
    () =>
      rows
        .map((row) => ({
          id: row.responseId,
          date: row.date,
          items: columns
            .map((column) => ({
              key: column.key,
              label: htmlToPlainText(column.primary),
              value: row.cells?.[column.key] ?? '',
            }))
            .filter((item) => item.value !== '' && item.value != null),
        }))
        .filter((card) => card.items.length),
    [rows, columns]
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

  const renderContent = () => {
    if (!shouldLoad || isLoading) {
      return (
        <div className="responses-statistics-comments-status">
          <span className="loader"></span>
        </div>
      )
    }

    if (!cards.length) {
      return emptyState
    }

    return (
      <>
        <div className="responses-statistics-grid responses-statistics-grid--two-col">
          {cards.map((card) => (
            <div className="responses-statistics-grid-row" key={card.id}>
              <div className="responses-statistics-multi-numerical">
                {card.items.map((item) => (
                  <React.Fragment key={item.key}>
                    <span className="responses-statistics-multi-numerical-label">
                      {item.label}
                    </span>
                    <span className="responses-statistics-multi-numerical-value">
                      <HighlightedText
                        text={String(item.value)}
                        terms={highlightTerms}
                      />
                    </span>
                  </React.Fragment>
                ))}
              </div>
              <span className="responses-statistics-grid-date">
                {formatAnswerDate(card.date)}
              </span>
            </div>
          ))}
        </div>
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
      </>
    )
  }

  return (
    <div ref={containerRef}>
      {shouldLoad && searchBlock}
      {renderContent()}
    </div>
  )
}
