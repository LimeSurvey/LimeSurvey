import { useMemo } from 'react'
import { useTranslation } from 'react-i18next'

import { LSTable } from 'components'

import { formatDay, formatTime, getLocaleCode } from './dateTimeUtils'

/**
 * Responses per entered date/time: Time | Responses for one day, with the
 * Date column added for a range.
 */
export const DateTimeBreakdownTable = ({
  rows,
  hasTime = false,
  showDate = false,
}) => {
  const { i18n } = useTranslation()
  const localeCode = getLocaleCode(i18n?.language)

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
    <div className="responses-statistics-date-table">
      <LSTable
        columns={columns}
        data={rows}
        getRowId={(row) => `${row.date} ${row.time ?? ''}`}
      />
    </div>
  )
}
