import { useState } from 'react'
import { Form } from 'react-bootstrap'
import { AdapterDayjs } from '@mui/x-date-pickers/AdapterDayjs'
import { LocalizationProvider } from '@mui/x-date-pickers/LocalizationProvider'
import { DateTimePicker } from '@mui/x-date-pickers/DateTimePicker'
import { DateIcon, CloseIcon } from 'components/icons'

import { TooltipContainer } from 'components'
import { useAppState } from 'hooks'
import { dayJsHelper, STATES, getTooltipMessages } from 'helpers'

export const DateTimePickerComponent = ({
  labelText,
  defaultValue,
  update = () => {},
  minDate = dayJsHelper('2000-04-17T15:30'),
  maxDate = dayJsHelper('2050-04-17T15:30'),
  noPermissionDisabled = false,
  noAccessDisabled = false,
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
  const [value, setValue] = useState(
    defaultValue ? dayJsHelper(defaultValue) : null
  )
  const disabled =
    (noPermissionDisabled && !hasSurveyUpdatePermission) || noAccessDisabled
  const [siteSettings] = useAppState(STATES.SITE_SETTINGS)
  const timezone = siteSettings?.timezone ? siteSettings.timezone : null

  const handleOnAccept = (value) => {
    onParticipantValueChange(
      dayJsHelper(value).format('YYYY-MM-DD HH:mm:ss'),
      valueInfo?.key
    )

    if (value && typeof value.toJSON === 'function') {
      update(value.toJSON())
      setValue(value)
    }
  }

  const handleChange = (newValue) => {
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
      dayJsHelper(newValue).format('YYYY-MM-DD HH:mm:ss'),
      valueInfo?.key
    )
  }

  const handleClear = (e) => {
    e.stopPropagation()
    update(null)
    setValue(null)
    setOpen(false)
  }

  const handleInputChange = (e) => {
    // Try to parse the input value as a date
    const inputValue = e?.target?.value
    try {
      // Use dayjs to parse the input
      const parsedDate = dayJsHelper(inputValue)

      // Check if it's a valid date
      if (parsedDate.isValid()) {
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
          showTip={disabled}
        >
          <LocalizationProvider dateAdapter={AdapterDayjs}>
            <DateTimePicker
              slotProps={{
                textField: {
                  onClick: () => setOpen(true),
                  onChange: handleInputChange,
                  inputProps: { readOnly: false },
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
              minDate={dayJsHelper(minDate)}
              maxDate={dayJsHelper(maxDate)}
              defaultValue={dayJsHelper(valueInfo?.value || defaultValue)}
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
