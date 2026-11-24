import { useId, useMemo, useRef, useState } from 'react'
import { format as sprintf } from 'util'

import { DateTimePickerComponent } from 'components/UIComponents/DateTimePicker/DateTimePicker'
import { useAppState } from 'hooks'
import {
  STATES,
  dayJsHelper,
  getDateQuestionSettings,
  isDateInLimits,
  isFullDateFormat,
  DATE_QUESTION_STORAGE_FORMAT,
} from 'helpers'

import { DateQuestionDropdowns } from './DateQuestionDropdowns'

const RANGE_ERRORS = ['minDate', 'maxDate', 'minTime', 'maxTime']

/**
 * Date question shown like in the survey: A date picker or, with dropdown_dates, dropdowns,
 * limited by the question's minimum and maximum date and in the question's (or the survey
 * language's) date format. Editable in the response editor, a disabled preview in the editor.
 *
 * @param {object} props The component props
 * @param {object} props.question The date question, with its attributes
 * @param {string} props.language The current language code
 * @param {object} [props.surveySettings] Survey settings, with dateFormats (language => date format index)
 * @param {object[]} [props.values] The response values (response editor)
 * @param {Function} [props.onValueChange] Called with the "YYYY-MM-DD HH:mm:ss" value (or null) and the response key, plus true when the input can't be saved
 * @param {boolean} [props.participantMode] True in the response editor; otherwise a disabled preview
 * @returns {JSX.Element} The date question input
 */
export const DateQuestionInput = ({
  question,
  language,
  surveySettings = {},
  values = [],
  onValueChange = () => {},
  participantMode = false,
}) => {
  const tipId = useId()
  const errorId = useId()
  const [siteSettings] = useAppState(STATES.SITE_SETTINGS)
  const [error, setError] = useState('')
  // The picker's last validation error, e.g. "invalidDate" or "minDate"
  const pickerErrorRef = useRef(null)
  const valueInfo = values?.[0]

  const settings = useMemo(
    () =>
      getDateQuestionSettings(
        question,
        language,
        surveySettings?.dateFormats?.[language],
        siteSettings?.dateFormats
      ),
    [
      question?.attributes,
      language,
      surveySettings?.dateFormats,
      siteSettings?.dateFormats,
    ]
  )
  const initialValue = useMemo(() => {
    if (!participantMode || !valueInfo?.value) {
      return null
    }

    const parsed = dayJsHelper(valueInfo.value)

    return parsed.isValid() ? parsed : null
  }, [participantMode, valueInfo?.value])

  // The limits are full dates: Show them in the question's format only when it shows a full
  // date too, otherwise (e.g. mm/yyyy or HH:MM) in the survey's date format
  const limitFormat = [settings.format, settings.surveyFormat].find(
    isFullDateFormat
  )

  /**
   * Formats a limit for the "between" message.
   *
   * @param {string} limit The limit from the settings
   * @returns {string} The formatted limit
   */
  const formatLimit = (limit) =>
    dayJsHelper(limit).format(limitFormat || 'YYYY-MM-DD')
  const rangeMessage = sprintf(
    t('Answer must be between %s and %s'),
    formatLimit(settings.minDate),
    formatLimit(settings.maxDate)
  )
  const formatLabel = sprintf(
    t('Date in the format: %s'),
    settings.displayFormat || settings.format || DATE_QUESTION_STORAGE_FORMAT
  )
  // The format hint is only shown in picker mode and when the format is known
  const showTip =
    settings.showTip && !settings.dropdown && Boolean(settings.displayFormat)
  const describedBy =
    [showTip ? tipId : '', error ? errorId : ''].filter(Boolean).join(' ') ||
    undefined

  const handleDropdownChange = (date) => {
    if (date && !isDateInLimits(date, settings.minDate, settings.maxDate)) {
      setError(rangeMessage)
      onValueChange(null, valueInfo?.key, true)
      return
    }

    setError('')
    onValueChange(
      date ? date.format(DATE_QUESTION_STORAGE_FORMAT) : null,
      valueInfo?.key
    )
  }

  const handleDropdownPartialChange = (reason, dateString) => {
    // An incomplete or impossible date can't be saved: Drop any pending change
    onValueChange(null, valueInfo?.key, true)
    setError(
      reason === 'invalid'
        ? sprintf(
            t('Date %s is invalid, please review your answer.'),
            dateString
          )
        : ''
    )
  }

  const handlePickerError = (reason) => {
    pickerErrorRef.current = reason
    // An invalid date is only reported on leaving the field (see handlePickerBlur), as MUI
    // also reports it while a date is still being typed
    setError(RANGE_ERRORS.includes(reason) ? rangeMessage : '')
  }

  /**
   * Explains an invalid typed date (e.g. 31/02/2001) once the input loses focus.
   *
   * @param {FocusEvent} event The blur event, bubbled up from the picker's input
   * @returns {void}
   */
  const handlePickerBlur = (event) => {
    if (
      event.target?.tagName === 'INPUT' &&
      pickerErrorRef.current === 'invalidDate'
    ) {
      setError(
        sprintf(
          t('Date %s is invalid, please review your answer.'),
          event.target.value
        )
      )
    }
  }

  return (
    <div className="date-question" data-testid="date-question">
      {settings.dropdown ? (
        <DateQuestionDropdowns
          settings={settings}
          value={initialValue}
          onChange={handleDropdownChange}
          onPartialChange={handleDropdownPartialChange}
          disabled={!participantMode}
          ariaLabel={formatLabel}
          invalid={Boolean(error)}
          errorId={errorId}
        />
      ) : (
        <div onBlur={handlePickerBlur}>
          <DateTimePickerComponent
            minDate={settings.minDate}
            maxDate={settings.maxDate}
            format={settings.format}
            views={settings.views}
            minuteStep={settings.minuteStep}
            values={participantMode ? values : []}
            onValueChange={onValueChange}
            onError={handlePickerError}
            participantMode={participantMode}
            previewDisabled={!participantMode}
            ariaLabel={formatLabel}
            ariaDescribedBy={describedBy}
          />
        </div>
      )}
      {showTip && (
        <p id={tipId} className="form-text mb-0">
          {sprintf(t('Format: %s'), settings.displayFormat)}
        </p>
      )}
      <div aria-live="polite">
        {error && (
          <p id={errorId} className="text-danger mb-0 mt-1" role="alert">
            {error}
          </p>
        )}
      </div>
    </div>
  )
}
