import React, { useEffect, useMemo, useState } from 'react'
import { format } from 'util'
import classNames from 'classnames'

import { HighlightedText, SearchInput, useSearchTerms } from 'components'
import { htmlToPlainText } from 'helpers'
import { useQuestionResponses } from 'hooks'
import { useIsInViewport } from 'hooks/useInViewport'

import { StatisticsDetailModal } from './StatisticsDetailModal.js'
import { formatAnswerDate } from './ChartsUtils.js'

const INLINE_LIMIT = 5
const INLINE_FIELD_LIMIT = 4

/**
 * Per-response answers for multi-field questions, shown as a short preview
 * with the complete loaded response list in a modal.
 *
 * `twoColumns` supports the denser numeric-input layout.
 */
export const MultiFieldResponsesGrid = ({
  surveyId,
  questionCode,
  title = '',
  fields,
  filters,
  twoColumns = true,
  filterSet = [],
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
    filterSet,
    search,
  })

  // One card per response, containing only its answered fields.
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
              value: htmlToPlainText(String(row.cells?.[column.key] ?? '')),
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
        ? hasNextPage
          ? t('No matching responses on this page yet.')
          : t('No responses match your search.')
        : hasNextPage
          ? t('No answers on this page yet.')
          : t('There are no responses for this question yet.')}
    </div>
  )

  const cardsGrid = (items, preview = false) => (
    <div
      className={classNames('responses-statistics-grid', {
        'responses-statistics-grid--two-col': twoColumns,
      })}
    >
      {items.map((card) => (
        <div className="responses-statistics-grid-row" key={card.id}>
          <div className="responses-statistics-multi-field">
            {(preview
              ? card.items.slice(0, INLINE_FIELD_LIMIT)
              : card.items
            ).map((item) => (
              <React.Fragment key={item.key}>
                <span className="responses-statistics-multi-field-label">
                  {item.label}
                </span>
                <span className="responses-statistics-multi-field-value">
                  <HighlightedText text={item.value} terms={highlightTerms} />
                </span>
              </React.Fragment>
            ))}
            {preview && card.items.length > INLINE_FIELD_LIMIT && (
              <span className="responses-statistics-multi-field-more">
                {format(
                  t('%s more answers'),
                  card.items.length - INLINE_FIELD_LIMIT
                )}
              </span>
            )}
          </div>
          <span className="responses-statistics-grid-date">
            {formatAnswerDate(card.date)}
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

  const loadMoreButton = (onClick) => (
    <div className="responses-statistics-comments-more">
      <button
        type="button"
        className="responses-statistics-comments-more-btn"
        onClick={onClick}
        disabled={isFetchingNextPage}
      >
        {isFetchingNextPage
          ? t('Loading...')
          : hasNextPage
            ? t('Load more')
            : t('View all responses')}
      </button>
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

    if (!cards.length && !hasNextPage) {
      return emptyState
    }

    return (
      <>
        {!cards.length && emptyState}
        {cardsGrid(cards.slice(0, INLINE_LIMIT), true)}
        {(hasNextPage ||
          cards.length > INLINE_LIMIT ||
          cards
            .slice(0, INLINE_LIMIT)
            .some((card) => card.items.length > INLINE_FIELD_LIMIT)) &&
          loadMoreButton(openModal)}
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
          <h2 className="responses-statistics-modal-title">
            {htmlToPlainText(title)}
          </h2>
          {searchBlock}
          {cards.length ? cardsGrid(cards) : emptyState}
          {hasNextPage && loadMoreButton(() => fetchNextPage())}
        </div>
      </StatisticsDetailModal>
    </div>
  )
}
