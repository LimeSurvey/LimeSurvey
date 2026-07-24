import { useEffect, useMemo, useState } from 'react'
import { format } from 'util'

import {
  HighlightedText,
  LSTable,
  SearchInput,
  useSearchTerms,
} from 'components'
import { useQuestionResponses } from 'hooks'
import { useIsInViewport } from 'hooks/useInViewport'

import { formatAnswerDate } from './ChartsUtils.js'

// Two-tone subquestion header: "<Y subquestion> - <X subquestion>" with the X
// part styled as secondary, matching the responses grid.
const ColumnHeader = ({ primary, secondary }) => (
  <>
    <span className="responses-statistics-array-text-col-primary">
      {primary}
    </span>
    {secondary && (
      <span className="responses-statistics-array-text-col-secondary">
        {' - '}
        {secondary}
      </span>
    )}
  </>
)

export const ArrayTextTable = ({
  surveyId,
  questionCode,
  fields,
  filters,
  searchable = false,
}) => {
  // Defer the fetch until the card scrolls into view, then keep it loaded.
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

  const tableColumns = useMemo(
    () => [
      {
        key: 'date',
        title: <ColumnHeader primary={t('Date')} />,
        sortable: true,
        render: (row) => formatAnswerDate(row.date),
      },
      ...columns.map((column) => ({
        key: column.key,
        title: column.primary ? (
          <ColumnHeader primary={column.primary} secondary={column.secondary} />
        ) : (
          t('Answer')
        ),
        render: (row) => (
          <div className="responses-statistics-array-text-cell">
            <HighlightedText text={row[column.key]} terms={highlightTerms} />
          </div>
        ),
      })),
    ],
    [columns, highlightTerms]
  )

  const tableRows = useMemo(
    () =>
      rows.map((row) => ({
        id: row.responseId,
        responseId: row.responseId,
        date: row.date,
        ...row.cells,
      })),
    [rows]
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
    // Loader while waiting to come into view or while the first page loads.
    if (!shouldLoad || isLoading) {
      return (
        <div className="responses-statistics-comments-status">
          <span className="loader"></span>
        </div>
      )
    }

    if (!tableRows.length) {
      return emptyState
    }

    return (
      <>
        <LSTable
          columns={tableColumns}
          data={tableRows}
          rowId="id"
          resizable
          maxHeight="400px"
        />
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
      {searchable && shouldLoad && searchBlock}
      {renderContent()}
    </div>
  )
}
