import { useEffect, useMemo, useState } from 'react'
import { format } from 'util'

import {
  HighlightedText,
  LSTable,
  SearchInput,
  useSearchTerms,
} from 'components'
import { htmlToPlainText } from 'helpers'
import { useQuestionResponses } from 'hooks'
import { useIsInViewport } from 'hooks/useInViewport'

import { formatAnswerDate } from './ChartsUtils.js'

// Two-tone subquestion header: "<Y subquestion> - <X subquestion>" with the X
// part styled as secondary, matching the responses grid.
const ColumnHeader = ({ primary, secondary }) => (
  <>
    <span className="responses-statistics-array-text-col-primary">
      {htmlToPlainText(primary)}
    </span>
    {secondary && (
      <span className="responses-statistics-array-text-col-secondary">
        {' - '}
        {htmlToPlainText(secondary)}
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
  scaleHeaders,
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

  // Dual-scale columns end in #<scale id>; label them with the question's
  // scale headers when provided.
  const secondaryFor = (column) => {
    const scaleId = /#(\d+)$/.exec(column.key ?? '')?.[1]
    return (scaleId != null && scaleHeaders?.[scaleId]) || column.secondary
  }

  const tableColumns = useMemo(
    () => [
      {
        id: 'date',
        accessorFn: (row) => row.date,
        header: <ColumnHeader primary={t('Date')} />,
        enableSorting: true,
        cell: ({ row }) => formatAnswerDate(row.original.date),
      },
      ...columns.map((column) => ({
        id: column.key,
        header: column.primary ? (
          <ColumnHeader
            primary={column.primary}
            secondary={secondaryFor(column)}
          />
        ) : (
          t('Answer')
        ),
        cell: ({ row }) => (
          <div className="responses-statistics-array-text-cell">
            <HighlightedText
              text={htmlToPlainText(row.original[column.key])}
              terms={highlightTerms}
            />
          </div>
        ),
      })),
    ],
    [columns, highlightTerms, scaleHeaders]
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
