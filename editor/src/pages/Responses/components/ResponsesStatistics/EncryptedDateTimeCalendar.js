import { useMemo } from 'react'

import { Button } from 'components'
import { useDateTimeValues } from 'hooks'

import { DateTimeCalendar } from './DateTimeCalendar.js'
import { toDayMap } from './dateTimeUtils'

/**
 * Calendar view of an encrypted Date/time question.
 */
export const EncryptedDateTimeCalendar = ({
  surveyId,
  question = {},
  filters,
  filterSet,
  calendarState = {},
  onCalendarStateChange,
}) => {
  const isDecrypted = !!calendarState.decrypted
  const { values, isLoading, isError } = useDateTimeValues(
    surveyId,
    question.qid,
    filters,
    { enabled: isDecrypted, filterSet, decrypt: true }
  )
  const stats = useMemo(() => values && toDayMap(values.days), [values])

  if (!isDecrypted) {
    return (
      <div className="responses-statistics-calendar-locked">
        <p>
          {t(
            'The date/time data for this question was set up to be encrypted.'
          )}
        </p>
        <Button
          variant="primary"
          onClick={() =>
            onCalendarStateChange?.({ ...calendarState, decrypted: true })
          }
        >
          {t('Decrypt response data and show statistics')}
        </Button>
      </div>
    )
  }

  if (isLoading) {
    return (
      <div className="responses-statistics-comments-status">
        <span className="loader"></span>
      </div>
    )
  }

  if (isError) {
    return (
      <div className="responses-statistics-empty">
        {t('The responses could not be loaded.')}
      </div>
    )
  }

  return (
    <DateTimeCalendar
      surveyId={surveyId}
      question={question}
      filters={filters}
      filterSet={filterSet}
      stats={stats}
      calendarState={calendarState}
      onCalendarStateChange={onCalendarStateChange}
    />
  )
}
