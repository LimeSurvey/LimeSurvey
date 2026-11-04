import { useMemo, useRef, useState } from 'react'
import { useTranslation } from 'react-i18next'

import { LSTable } from 'components'

import { formatDay, formatTime, getLocaleCode } from './dateTimeUtils'

const ROWS_PER_LOAD = 50

/**
 * Responses per entered date/time: Time | Responses for one day, with the
 * Date column added for a range. The rows are all loaded already; "Load more"
 * only draws more of them.
 */
export const DateTimeBreakdownTable = ({
  rows,
  hasTime = false,
  showDate = false,
}) => {
  const { i18n } = useTranslation()
  const localeCode = getLocaleCode(i18n?.language)
  const [visibleCount, setVisibleCount] = useState(ROWS_PER_LOAD)
  const tableRef = useRef(null)
  const visibleRows = useMemo(
    () => rows.slice(0, visibleCount),
    [rows, visibleCount]
  )

  const loadMore = () => {
    const nextCount = visibleCount + ROWS_PER_LOAD
    setVisibleCount(nextCount)
    if (nextCount >= rows.length) {
      tableRef.current?.focus()
    }
  }

  const columns = useMemo(
    () => [
      ...(showDate
        ? [
            {
              id: 'date',
              header: t('Date'),
              cell: ({ row }) => formatDay(row.original.date, localeCode),
            },
          ]
        : []),
      ...(hasTime
        ? [
            {
              id: 'time',
              header: t('Time'),
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
      {
        id: 'count',
        header: t('Responses'),
        meta: showDate ? undefined : { align: 'right' },
        cell: ({ row }) => row.original.count,
      },
    ],
    [hasTime, showDate, localeCode]
  )

  return (
    <div
      ref={tableRef}
      tabIndex={-1}
      className="responses-statistics-date-table"
    >
      <LSTable
        columns={columns}
        data={visibleRows}
        getRowId={(row) => `${row.date} ${row.time ?? ''}`}
      />
      {visibleCount < rows.length && (
        <div className="responses-statistics-comments-more">
          <button
            type="button"
            className="responses-statistics-comments-more-btn"
            onClick={loadMore}
          >
            {t('Load more')}
          </button>
        </div>
      )}
    </div>
  )
}
