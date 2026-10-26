import { useEffect, useMemo, useState } from 'react'
import { useTranslation } from 'react-i18next'

import { LSTable } from 'components'
import { useQuestionResponses } from 'hooks'
import { useIsInViewport } from 'hooks/useInViewport'

import { ChartHeader } from './ChartHeader.js'
import { StatisticsDetailModal } from './StatisticsDetailModal.js'
import {
  formatDay,
  formatTime,
  getLocaleCode,
  parseAnswerDateTime,
} from './dateTimeUtils'

// The card shows a short preview; the full list lives in the modal.
const INLINE_LIMIT = 5

// Stored values sort chronologically as plain strings; a missing time sorts first.
const byRawValue = (rowA, rowB, columnId) =>
  String(rowA.original[columnId] ?? '').localeCompare(
    String(rowB.original[columnId] ?? '')
  )

/**
 * Data table view of a Date/time question: one row per answer with its
 * response ID, entered date and, when the question records one, time.
 */
export const DateTimeTable = ({ surveyId, question = {}, filters }) => {
  const { code: questionCode, fields, hasTime = false } = question
  const { i18n } = useTranslation()
  const localeCode = getLocaleCode(i18n?.language)
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

  const {
    columns,
    rows,
    isLoading,
    hasNextPage,
    fetchNextPage,
    isFetchingNextPage,
  } = useQuestionResponses(surveyId, questionCode, {
    enabled: shouldLoad,
    fields,
    filters,
  })

  const answerKey = columns[0]?.key
  const answers = useMemo(
    () =>
      rows
        .map((row) => ({
          id: row.responseId,
          responseId: Number(row.responseId),
          ...parseAnswerDateTime(row.cells?.[answerKey]),
        }))
        .filter((row) => row.date),
    [rows, answerKey]
  )

  const tableColumns = useMemo(
    () => [
      {
        id: 'responseId',
        accessorKey: 'responseId',
        header: t('Response ID'),
        enableSorting: true,
      },
      {
        id: 'date',
        accessorKey: 'date',
        header: t('Date'),
        enableSorting: true,
        sortingFn: byRawValue,
        cell: ({ row }) => formatDay(row.original.date, localeCode),
      },
      ...(hasTime
        ? [
            {
              id: 'time',
              accessorKey: 'time',
              header: t('Time'),
              enableSorting: true,
              sortingFn: byRawValue,
              cell: ({ row }) =>
                row.original.time ? (
                  formatTime(row.original.time, localeCode)
                ) : (
                  <span className="responses-statistics-date-table-no-time">
                    {t('No time entered.')}
                  </span>
                ),
            },
          ]
        : []),
    ],
    [hasTime, localeCode]
  )

  const table = (items) => (
    <div className="responses-statistics-date-table">
      <LSTable columns={tableColumns} data={items} resizable />
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
      return (
        <div className="responses-statistics-empty">
          {t('There are no responses for this question yet.')}
        </div>
      )
    }

    return (
      <>
        {table(answers.slice(0, INLINE_LIMIT))}
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
      {renderContent()}
      <StatisticsDetailModal
        show={showModal}
        onHide={() => setShowModal(false)}
        modalClassname="responses-statistics-responses-modal responses-statistics-date-table-modal"
      >
        <div className="responses-statistics-comments">
          <ChartHeader {...question} />
          {table(answers)}
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
