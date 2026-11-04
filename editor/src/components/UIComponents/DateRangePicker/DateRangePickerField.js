import { isSameDay } from 'date-fns'
import classNames from 'classnames'

import { DATE_FORMAT } from 'helpers/dateRangeUtils'

import { DateInput } from './DateInput'
import { useDayPickerSetup } from './DateRangeCalendar'

/**
 * Read-only dates of a confirmed range: one for a single day, two with "until"
 * for a longer range. Activating a date or its calendar icon calls
 * `onOpenPicker`, where the dates are changed.
 */
export const DateRangePickerField = ({
  value,
  onOpenPicker,
  dateFormat = DATE_FORMAT,
  className,
}) => {
  const { locale } = useDayPickerSetup()
  const isRange = !!value.to && !isSameDay(value.from, value.to)

  const inputProps = {
    dateFormat,
    locale,
    readOnly: true,
    onIconClick: onOpenPicker,
  }

  return (
    <div
      className={classNames(
        'date-range-picker date-range-picker-field',
        className
      )}
    >
      <div className="date-range-picker-field-inputs">
        <DateInput
          {...inputProps}
          label={isRange ? t('Start date') : t('Date')}
          value={value.from}
        />
        {isRange && (
          <>
            <span className="date-range-picker-field-until">{t('until')}</span>
            <DateInput {...inputProps} label={t('End date')} value={value.to} />
          </>
        )}
      </div>
    </div>
  )
}
