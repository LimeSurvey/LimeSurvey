import { useMemo } from 'react'

import { useDateTimeValues } from 'hooks'

import { DateTimeCalendar } from './DateTimeCalendar.js'
import { EncryptedDateTimeGate } from './EncryptedDateTimeGate.js'
import { toDayMap } from './dateTimeUtils'

const DecryptedCalendar = ({
  surveyId,
  question,
  filters,
  filterSet,
  calendarState,
  onCalendarStateChange,
}) => {
  const { values, isLoading, isError } = useDateTimeValues(
    surveyId,
    question.qid,
    filters,
    { filterSet, decrypt: true }
  )
  const stats = useMemo(() => values && toDayMap(values.days), [values])

  if (isLoading) {
    return (
      <div
        className="responses-statistics-comments-status"
        role="status"
        aria-label={t('Loading')}
      >
        <span className="loader"></span>
      </div>
    )
  }

  if (isError) {
    return (
      <div className="responses-statistics-empty" role="alert">
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

/**
 * Calendar view of an encrypted Date/time question
 */
export const EncryptedDateTimeCalendar = ({
  calendarState = {},
  onCalendarStateChange,
  question = {},
  ...props
}) => (
  <EncryptedDateTimeGate
    calendarState={calendarState}
    onCalendarStateChange={onCalendarStateChange}
  >
    <DecryptedCalendar
      {...props}
      question={question}
      calendarState={calendarState}
      onCalendarStateChange={onCalendarStateChange}
    />
  </EncryptedDateTimeGate>
)
