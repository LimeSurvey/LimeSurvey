import { useId, useMemo, useRef } from 'react'

import { DateRangePickerField } from 'components'
import { htmlToPlainText } from 'helpers'
import { useDateTimeValues } from 'hooks'

import { DateTimeBreakdownTable } from './DateTimeBreakdownTable.js'
import { StatisticsDetailModal } from './StatisticsDetailModal.js'
import {
  formatResponseCount,
  sliceByRange,
  sumCounts,
  toDate,
} from './dateTimeUtils'

/**
 * How many answers entered each date and time in a range of days (`from` and
 * `to` are day keys). One day lists its times; a longer range adds the date.
 * A date or its calendar icon calls `onOpenPicker`. In the range flow, the
 * results also show their total. A null `range` closes the modal.
 */
export const DateTimeRangeModal = ({
  surveyId,
  question = {},
  filters,
  filterSet,
  range,
  isRangeFlow = false,
  onOpenPicker,
  onHide,
  ...modalProps
}) => {
  const titleId = useId()
  const lastRangeRef = useRef(range)
  if (range) {
    lastRangeRef.current = range
  }
  const shownRange = lastRangeRef.current
  const { values, isLoading, isError } = useDateTimeValues(
    surveyId,
    question.qid,
    filters,
    {
      enabled: !!shownRange,
      filterSet,
      decrypt: !!question.encrypted,
      ...(!question.encrypted && {
        from: shownRange?.from,
        to: shownRange?.to,
      }),
    }
  )
  const isSingleDay = shownRange?.from === shownRange?.to
  const isRangeResult = isRangeFlow || !isSingleDay
  const rows = useMemo(
    () =>
      shownRange
        ? sliceByRange(values?.days, shownRange.from, shownRange.to)
        : [],
    [shownRange, values]
  )

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

    if (!rows.length) {
      return (
        <div className="responses-statistics-empty">
          {isRangeResult
            ? t('No responses for this date range.')
            : t('There are no responses for this date.')}
        </div>
      )
    }

    return (
      <>
        {isRangeResult && (
          <p className="responses-statistics-date-modal-total">
            {formatResponseCount(sumCounts(rows))}
          </p>
        )}
        <DateTimeBreakdownTable
          key={`${shownRange.from}|${shownRange.to}`}
          rows={rows}
          hasTime={question.hasTime}
          showDate={!isSingleDay || !question.hasTime}
        />
      </>
    )
  }

  return (
    <StatisticsDetailModal
      show={!!range}
      onHide={onHide}
      modalClassname="responses-statistics-date-modal"
      aria-labelledby={titleId}
      {...modalProps}
    >
      <h2 id={titleId} className="responses-statistics-modal-title">
        {htmlToPlainText(question.title)}
      </h2>
      {shownRange && (
        <div className="responses-statistics-date-modal-picker">
          <span className="responses-statistics-date-modal-label">
            {t('See all responses for:')}
          </span>
          <DateRangePickerField
            value={{
              from: toDate(shownRange.from),
              to: toDate(shownRange.to),
            }}
            onOpenPicker={onOpenPicker}
          />
        </div>
      )}
      {renderContent()}
    </StatisticsDetailModal>
  )
}
