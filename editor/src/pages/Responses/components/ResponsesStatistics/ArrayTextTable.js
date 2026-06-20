import { useEffect, useMemo, useState } from 'react'

import { LSTable } from 'components'
import { useQuestionResponses } from 'hooks'
import { useIsInViewport } from 'hooks/useInViewport'

// Participants are shown as zero-padded sequence ids (001, 002, ...).
const formatParticipant = (responseId) =>
  String(responseId ?? '').padStart(3, '0')

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

// Array (Texts): a free-text grid shown as raw per-response data — participant
// rows × subquestion columns — fetched from the responses endpoint.
export const ArrayTextTable = ({ surveyId, questionCode }) => {
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

  const {
    columns,
    rows,
    isLoading,
    hasNextPage,
    fetchNextPage,
    isFetchingNextPage,
  } = useQuestionResponses(surveyId, questionCode, { enabled: shouldLoad })

  const tableColumns = useMemo(
    () => [
      {
        key: 'participant',
        title: t('Participant'),
        sortable: true,
        render: (row) => formatParticipant(row.responseId),
      },
      ...columns.map((column) => ({
        key: column.key,
        title: (
          <ColumnHeader
            primary={column.primary}
            secondary={column.secondary}
          />
        ),
      })),
    ],
    [columns]
  )

  // Flatten each row's cells onto the row so LSTable can render columns by key;
  // `participant` mirrors the response id so the first column can be sorted.
  const tableRows = useMemo(
    () =>
      rows.map((row) => ({
        id: row.responseId,
        responseId: row.responseId,
        participant: row.responseId,
        ...row.cells,
      })),
    [rows]
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
      return (
        <div className="responses-statistics-empty">
          {t('There are no responses for this question yet.')}
        </div>
      )
    }

    return (
      <>
        <LSTable columns={tableColumns} data={tableRows} rowId="id" resizable />
        {hasNextPage && (
          <div className="responses-statistics-comments-more">
            <button
              type="button"
              className="responses-statistics-comments-more-btn"
              onClick={() => fetchNextPage()}
              disabled={isFetchingNextPage}
            >
              {t('Load more')}
            </button>
          </div>
        )}
      </>
    )
  }

  // The ref must stay mounted across states so the viewport observer keeps
  // tracking this card.
  return <div ref={containerRef}>{renderContent()}</div>
}
