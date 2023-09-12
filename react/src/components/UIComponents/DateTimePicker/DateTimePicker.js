import { useMemo, useState } from 'react'
import dayjs from 'dayjs'
import { Form } from 'react-bootstrap'
import PropTypes from 'prop-types'

import { DemoContainer } from '@mui/x-date-pickers/internals/demo'
import { AdapterDayjs } from '@mui/x-date-pickers/AdapterDayjs'
import { LocalizationProvider } from '@mui/x-date-pickers/LocalizationProvider'
import { DateTimePicker } from '@mui/x-date-pickers/DateTimePicker'

export const DateTimePickerComponent = ({
  labelText,
  value,
  handleOnChange,
  minDate,
  maxDate,
}) => {
  const [error, setError] = useState(null)
  const errorMessage = useMemo(() => {
    switch (error) {
      case 'maxDate': {
        return `Please select a date before ${maxDate ? maxDate : 2050} year`
      }
      case 'minDate': {
        return `Please select a date after ${minDate ? minDate : 2000} year`
      }
      case 'invalidDate': {
        return 'Your date is not valid'
      }
      default: {
        return ''
      }
    }
  }, [error, maxDate, minDate])
  return (
    <div>
      {labelText && <Form.Label>{labelText}</Form.Label>}
      <div>
        <LocalizationProvider dateAdapter={AdapterDayjs}>
          <DemoContainer components={['DateTimePicker']}>
            <DateTimePicker
              ampm={false}
              onError={(newError) => setError(newError)}
              value={dayjs(value)}
              slotProps={{
                textField: {
                  helperText: errorMessage,
                },
              }}
              onChange={(newValue) => handleOnChange(newValue)}
              minDate={dayjs(minDate)}
              maxDate={dayjs(maxDate)}
            />
          </DemoContainer>
        </LocalizationProvider>
      </div>
    </div>
  )
}
DateTimePickerComponent.propTypes = {
  labelText: PropTypes.string,
}
