import { useId, useMemo } from 'react'

import { DateRangePickerField } from 'components'
import { htmlToPlainText } from 'helpers'
import { useDateTimeValues } from 'hooks'

import { DateTimeBreakdownTable } from './DateTimeBreakdownTable.js'
import { StatisticsDetailModal } from './StatisticsDetailModal.js'
import { sliceByRange, toDate, toDayKey } from './dateTimeUtils'

/**
 * How many answers entered each date and time in a range of days (`from` and
 * `to` are day keys). One day lists its times; a longer range adds the date.
 * Dates can be typed here; the calendar icon calls `onOpenPicker`.
 */
export const DateTimeRangeModal = ({
  surveyId,
  question = {},
  filters,
  filterSet,
  range,
  onRangeChange,
  onOpenPicker,
  onHide,
  ...modalProps
}) => {
  const titleId = useId()
  const { values, isLoading, isError } = useDateTimeValues(
    surveyId,
    question.qid,
    filters,
    { enabled: !!range, filterSet, decrypt: !!question.encrypted }
  )
  const isSingleDay = range?.from === range?.to
  const rows = useMemo(
    () => (range ? sliceByRange(values?.days, range.from, range.to) : []),
    [range, values]
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
          {isSingleDay
            ? t('There are no responses for this date.')
            : t('There are no responses for this date range.')}
        </div>
      )
    }

    return (
      <DateTimeBreakdownTable
        key={`${range.from}|${range.to}`}
        rows={rows}
        hasTime={question.hasTime}
        showDate={!isSingleDay || !question.hasTime}
      />
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
      {range && (
        <div className="responses-statistics-date-modal-picker">
          <span className="responses-statistics-date-modal-label">
            {t('See all responses for:')}
          </span>
          <DateRangePickerField
            value={{ from: toDate(range.from), to: toDate(range.to) }}
            onOpenPicker={onOpenPicker}
            onChange={({ from, to }) =>
              onRangeChange({ from: toDayKey(from), to: toDayKey(to) })
            }
          />
        </div>
      )}
      {renderContent()}
    </StatisticsDetailModal>
  )
}
