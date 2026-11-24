import { useId, useMemo, useState } from 'react'
import { format as sprintf } from 'util'

import { DateTimePickerComponent } from 'components/UIComponents/DateTimePicker/DateTimePicker'
import { useAppState } from 'hooks'
import {
  STATES,
  dayJsHelper,
  getDateQuestionSettings,
  isDateInLimits,
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

  /**
   * Formats a limit for the "between" message, in the format the question shows.
   *
   * @param {string} limit The limit from the settings
   * @returns {string} The formatted limit
   */
  const formatLimit = (limit) =>
    dayJsHelper(limit).format(settings.format || DATE_QUESTION_STORAGE_FORMAT)
  const rangeMessage = sprintf(
    t('Answer must be between %s and %s'),
    formatLimit(settings.minDate),
    formatLimit(settings.maxDate)
  )
  const formatLabel = sprintf(
    t('Date in the format: %s'),
    settings.displayFormat || settings.format || DATE_QUESTION_STORAGE_FORMAT
  )
  const describedBy =
    [settings.showTip && !settings.dropdown ? tipId : '', error ? errorId : '']
      .filter(Boolean)
      .join(' ') || undefined

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
    setError(RANGE_ERRORS.includes(reason) ? rangeMessage : '')
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
          ariaDescribedBy={describedBy}
        />
      ) : (
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
      )}
      {settings.showTip && !settings.dropdown && settings.displayFormat && (
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
