import { useEffect, useMemo, useRef } from 'react'

import { Button } from 'components'
import { useDateTimeValues } from 'hooks'

import { DateTimeCalendar } from './DateTimeCalendar.js'
import { toDayMap } from './dateTimeUtils'

/**
 * Calendar view of an encrypted Date/time question. Its answers are only
 * decrypted once asked for; until the page reloads they then feed the same
 * calendar, and the same loaded values serve its day and range breakdowns.
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
  const decryptedRef = useRef(null)
  const moveFocusRef = useRef(false)
  const { values, isLoading, isError } = useDateTimeValues(
    surveyId,
    question.qid,
    filters,
    { enabled: isDecrypted, filterSet, decrypt: true }
  )
  const stats = useMemo(() => values && toDayMap(values.days), [values])

  useEffect(() => {
    if (isDecrypted && moveFocusRef.current) {
      moveFocusRef.current = false
      decryptedRef.current?.focus()
    }
  }, [isDecrypted])

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
          onClick={() => {
            moveFocusRef.current = true
            onCalendarStateChange?.({ ...calendarState, decrypted: true })
          }}
        >
          {t('Decrypt response data and show statistics')}
        </Button>
      </div>
    )
  }

  const renderContent = () => {
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

  return (
    <div
      ref={decryptedRef}
      tabIndex={-1}
      className="responses-statistics-calendar-decrypted"
    >
      {renderContent()}
    </div>
  )
}
