import { DatePickerField } from 'components'
import { htmlToPlainText } from 'helpers'
import { useDateTimeValues } from 'hooks'

import { DateTimeBreakdownTable } from './DateTimeBreakdownTable.js'
import { StatisticsDetailModal } from './StatisticsDetailModal.js'
import { sliceByRange, toDate, toDayKey } from './dateTimeUtils'

/**
 * Time breakdown of one day: how many answers entered each time on it. The
 * day can be changed here, which moves the calendar's selection along.
 */
export const DateTimeDayModal = ({
  surveyId,
  question = {},
  filters,
  day,
  onDayChange,
  onHide,
}) => {
  const { values, isLoading, isError } = useDateTimeValues(
    surveyId,
    question.qid,
    filters,
    { enabled: !!day }
  )
  const rows = day ? sliceByRange(values?.days, day, day) : []

  const renderContent = () => {
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

    if (!rows.length) {
      return (
        <div className="responses-statistics-empty">
          {t('There are no responses for this date.')}
        </div>
      )
    }

    return <DateTimeBreakdownTable rows={rows} hasTime={question.hasTime} />
  }

  return (
    <StatisticsDetailModal
      show={!!day}
      onHide={onHide}
      modalClassname="responses-statistics-date-modal"
    >
      <h2 className="responses-statistics-modal-title">
        {htmlToPlainText(question.title)}
      </h2>
      <div className="responses-statistics-date-modal-picker">
        <span className="responses-statistics-date-modal-label">
          {t('See all responses for:')}
        </span>
        <DatePickerField
          label={t('Date')}
          value={toDate(day)}
          onChange={(date) => onDayChange(toDayKey(date))}
        />
      </div>
      {renderContent()}
    </StatisticsDetailModal>
  )
}
