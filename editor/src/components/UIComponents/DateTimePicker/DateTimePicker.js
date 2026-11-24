import { useState } from 'react'
import { Form } from 'react-bootstrap'
import { AdapterDayjs } from '@mui/x-date-pickers/AdapterDayjs'
import { LocalizationProvider } from '@mui/x-date-pickers/LocalizationProvider'
import { DateTimePicker } from '@mui/x-date-pickers/DateTimePicker'
import { DateIcon, CloseIcon } from 'components/icons'

import { TooltipContainer } from 'components/TooltipContainer/TooltipContainer'
import { useAppState } from 'hooks'
import {
  dayJsHelper,
  STATES,
  getTooltipMessages,
  DATE_QUESTION_DEFAULT_MIN,
  DATE_QUESTION_DEFAULT_MAX,
  DATE_QUESTION_STORAGE_FORMAT,
  normalizeDateToViews,
} from 'helpers'

/**
 * Parses a stored date value into a dayjs object.
 *
 * @param {*} rawValue The stored value, e.g. "1985-04-17 00:00:00"
 * @returns {import('dayjs').Dayjs|null} The parsed date, or null when empty or invalid
 */
const parseInitialValue = (rawValue) => {
  if (!rawValue) {
    return null
  }

  const parsed = dayJsHelper(rawValue)

  return parsed.isValid() ? parsed : null
}

/**
 * Date and time picker used in the settings and in the response editor.
 *
 * @param {object} props The component props
 * @param {string} [props.labelText] The label shown above the picker
 * @param {*} [props.defaultValue] The initial value when no response value is given
 * @param {Function} [props.update] Called with the ISO string (or null) on every change
 * @param {*} [props.minDate] The earliest selectable date
 * @param {*} [props.maxDate] The latest selectable date
 * @param {string} [props.format] The dayjs display and input format; MUI's default when not set
 * @param {string[]} [props.views] The picker views; the parts not in it are reset before saving
 * @param {number} [props.minuteStep] The step of the minutes the picker offers; MUI's default (5) when not set
 * @param {Function} [props.onError] Called with MUI's validation error (e.g. "minDate") or null
 * @param {string} [props.ariaLabel] Accessible name of the input
 * @param {string} [props.ariaDescribedBy] Id of the element describing the input
 * @param {boolean} [props.noPermissionDisabled] Disable the picker without survey update permission
 * @param {boolean} [props.noAccessDisabled] Disable the picker
 * @param {boolean} [props.previewDisabled] Disable the picker without the "no permission" tooltip
 * @param {object[]} [props.values] The response values (participant mode)
 * @param {Function} [props.onValueChange] Called with the "YYYY-MM-DD HH:mm:ss" value (or null) and the response key, plus true when the input can't be saved
 * @param {boolean} [props.participantMode] True in the response editor
 * @param {boolean} [props.closeOnSelect] Close the picker once a value is selected
 * @returns {JSX.Element} The picker
 */
export const DateTimePickerComponent = ({
  labelText,
  defaultValue,
  update = () => {},
  minDate = DATE_QUESTION_DEFAULT_MIN,
  maxDate = DATE_QUESTION_DEFAULT_MAX,
  format,
  views,
  minuteStep,
  onError = () => {},
  ariaLabel,
  ariaDescribedBy,
  noPermissionDisabled = false,
  noAccessDisabled = false,
  previewDisabled = false,
  values = [],
  onValueChange: onParticipantValueChange = () => {},
  participantMode = false,
  closeOnSelect = true,
}) => {
  const valueInfo = values?.[0]

  const [hasSurveyUpdatePermission] = useAppState(
    STATES.HAS_SURVEY_UPDATE_PERMISSION
  )
  const [open, setOpen] = useState(false)
  const [value, setValue] = useState(() =>
    parseInitialValue(valueInfo?.value || defaultValue)
  )
  const showNoPermissionTip =
    (noPermissionDisabled && !hasSurveyUpdatePermission) || noAccessDisabled
  const disabled = showNoPermissionTip || previewDisabled
  const minDateTime = dayJsHelper(minDate ?? DATE_QUESTION_DEFAULT_MIN)
  const maxDateTime = dayJsHelper(maxDate ?? DATE_QUESTION_DEFAULT_MAX)
  const [siteSettings] = useAppState(STATES.SITE_SETTINGS)
  const [userDetails] = useAppState(STATES.USER_DETAIL)
  const appTimezone = siteSettings?.timezone ? siteSettings.timezone : null
  const timezone = userDetails?.timezone ? userDetails.timezone : appTimezone

  /**
   * Checks whether a date may be saved: Valid and within the minimum and maximum date.
   *
   * @param {import('dayjs').Dayjs} date The date to check
   * @returns {boolean} True when the date may be saved
   */
  const isSavable = (date) =>
    date.isValid() && !date.isBefore(minDateTime) && !date.isAfter(maxDateTime)

  /**
   * Drops the pending change of a date that can't be saved and reports why, like MUI's own
   * validation does.
   *
   * @param {import('dayjs').Dayjs} date The date that can't be saved
   * @returns {void}
   */
  const reportOutOfLimits = (date) => {
    onParticipantValueChange(null, valueInfo?.key, true)
    if (!date.isValid()) {
      onError('invalidDate')
    } else {
      onError(date.isBefore(minDateTime) ? 'minDate' : 'maxDate')
    }
  }

  const handleOnAccept = (acceptedValue) => {
    if (!acceptedValue) {
      return
    }

    const value = normalizeDateToViews(dayJsHelper(acceptedValue), views)
    if (!isSavable(value)) {
      reportOutOfLimits(value)
      return
    }

    onParticipantValueChange(
      value.format(DATE_QUESTION_STORAGE_FORMAT),
      valueInfo?.key
    )

    if (typeof value.toJSON === 'function') {
      update(value.toJSON())
      setValue(value)
    }
  }

  const handleChange = (changedValue, context) => {
    // MUI reports an invalid date while a typed value is still incomplete, and a validation
    // error when it is outside the limits: Show it, but don't save it
    if (
      changedValue &&
      (!changedValue.isValid?.() || context?.validationError)
    ) {
      setValue(changedValue)
      onParticipantValueChange(null, valueInfo?.key, true)
      return
    }

    const newValue = normalizeDateToViews(changedValue, views)
    // Resetting the parts the format doesn't show can move the date outside the limits,
    // e.g. May 2024 becomes 2024-05-01 with a minimum of 2024-05-15
    if (newValue && !isSavable(newValue)) {
      setValue(newValue)
      reportOutOfLimits(newValue)
      return
    }

    if (newValue) {
      update(newValue.toJSON())
      setValue(newValue)
    } else {
      update(null)
      onParticipantValueChange(null, valueInfo?.key)
      setValue(null)
      return
    }

    onParticipantValueChange(
      dayJsHelper(newValue).format(DATE_QUESTION_STORAGE_FORMAT),
      valueInfo?.key
    )
  }

  const handleClear = (e) => {
    e.stopPropagation()
    update(null)
    onParticipantValueChange(null, valueInfo?.key)
    setValue(null)
    setOpen(false)
  }

  const handleInputChange = (e) => {
    // Try to parse the input value as a date
    const inputValue = e?.target?.value
    try {
      // Use dayjs to parse the input, strictly in the shown format when there is one
      const parsedDate = normalizeDateToViews(
        format
          ? dayJsHelper(inputValue, format, true)
          : dayJsHelper(inputValue),
        views
      )

      // Check if it's a valid date within the limits
      if (isSavable(parsedDate)) {
        setValue(parsedDate)
        update(parsedDate.toJSON())
      }
    } catch (error) {
      // If parsing fails, just continue
    }
    setOpen(true)
  }

  return (
    <div data-testid="data-time-picker">
      {labelText && <Form.Label>{labelText}</Form.Label>}
      <div>
        <TooltipContainer
          tip={getTooltipMessages().NO_PERMISSION}
          showTip={showNoPermissionTip}
        >
          <LocalizationProvider dateAdapter={AdapterDayjs}>
            <DateTimePicker
              slotProps={{
                textField: {
                  onClick: () => setOpen(true),
                  // In the response editor MUI's own field handles typing, section by section in the
                  // question's format, and reports it through onChange like a picked date
                  ...(participantMode ? {} : { onChange: handleInputChange }),
                  inputProps: {
                    'readOnly': false,
                    'aria-label': ariaLabel,
                    'aria-describedby': ariaDescribedBy,
                  },
                  className: 'form-control',
                  InputProps: {
                    endAdornment: (
                      <div className="settings-buttons position-absolute end-0 top-0 h-100 d-flex align-items-center">
                        {value && (
                          <button
                            type="button"
                            onClick={handleClear}
                            className={'settings-button ps-3'}
                          >
                            <CloseIcon className="fill-current" />
                          </button>
                        )}
                        <button
                          type="button"
                          onClick={() => setOpen(true)}
                          className={'settings-button ps-3 pe-3'}
                        >
                          <DateIcon className="fill-current" />
                        </button>
                      </div>
                    ),
                  },
                },
              }}
              ampm={false}
              format={format}
              views={views}
              timeSteps={minuteStep ? { minutes: minuteStep } : undefined}
              minDateTime={minDateTime}
              maxDateTime={maxDateTime}
              onError={onError}
              timezone={participantMode ? null : timezone} // Using the timezone might display a different value than the one written inside the table.
              value={value}
              onAccept={handleOnAccept}
              onChange={handleChange}
              disabled={disabled}
              closeOnSelect={closeOnSelect}
              open={open}
              onOpen={() => setOpen(true)}
              onClose={() => setOpen(false)}
            />
          </LocalizationProvider>
        </TooltipContainer>
      </div>
    </div>
  )
}
