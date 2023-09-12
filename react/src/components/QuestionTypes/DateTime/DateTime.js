import React, { useEffect, useState } from 'react'
import { DemoContainer } from '@mui/x-date-pickers/internals/demo'
import { AdapterDayjs } from '@mui/x-date-pickers/AdapterDayjs'
import { LocalizationProvider } from '@mui/x-date-pickers/LocalizationProvider'
import { DatePicker } from '@mui/x-date-pickers/DatePicker'
import { Select } from 'components/UIComponents'

const shortMonthOptions = [
  { label: 'Month', value: 'month' },
  { label: 'Jan', value: 'jan' },
  { label: 'Feb', value: 'feb' },
  { label: 'Mar', value: 'mar' },
  { label: 'Apr', value: 'apr' },
  { label: 'May', value: 'may' },
  { label: 'Jun', value: 'jun' },
  { label: 'Jul', value: 'jul' },
  { label: 'Aug', value: 'aug' },
  { label: 'Sep', value: 'sep' },
  { label: 'Oct', value: 'oct' },
  { label: 'Nov', value: 'nov' },
  { label: 'Dec', value: 'dec' },
]
const fullMonthOptions = [
  { label: 'Month', value: 'month' },
  { label: 'January', value: 'jan' },
  { label: 'February', value: 'feb' },
  { label: 'March', value: 'mar' },
  { label: 'April', value: 'apr' },
  { label: 'May', value: 'may' },
  { label: 'June', value: 'jun' },
  { label: 'July', value: 'jul' },
  { label: 'August', value: 'aug' },
  { label: 'September', value: 'sep' },
  { label: 'October', value: 'oct' },
  { label: 'November', value: 'nov' },
  { label: 'December', value: 'dec' },
]
const numberMonthOptions = [
  { label: 'Month', value: 'month' },
  ...Array(12)
    .fill('-')
    .map((_, idx) => {
      if (idx < 9) {
        return { label: `0${idx + 1}`, value: `0${idx + 1}` }
      }
      return { label: `${idx + 1}`, value: `${idx + 1}` }
    }),
]
const dayOptions = [
  { label: 'Day', value: 'day' },
  ...Array(31)
    .fill('-')
    .map((_, idx) => {
      if (idx < 9) {
        return { label: `0${idx + 1}`, value: `0${idx + 1}` }
      }
      return { label: `${idx + 1}`, value: `${idx + 1}` }
    }),
]
const hourOptions = [
  { label: 'Hour', value: 'hour' },
  ...Array(24)
    .fill('-')
    .map((_, idx) => {
      if (idx < 9) {
        return { label: `0${idx + 1}`, value: `0${idx + 1}` }
      }
      return { label: `${idx + 1}`, value: `${idx + 1}` }
    }),
]

function generateMinuteList(intervalValue = 1, startMinute, endMinute) {
  let interval = intervalValue
  if (interval < 1) {
    interval = 1
  }
  const minuteList = []
  let currentMinute = startMinute

  while (currentMinute <= endMinute) {
    minuteList.push(currentMinute)
    currentMinute += interval
  }

  return minuteList
}

const yearOptions = [
  { label: 'Year', value: 'year' },
  ...Array(300)
    .fill('-')
    .map((_, idx) => ({ label: 1900 + idx, value: 1900 + idx })),
]
const monthOptions = {
  short: shortMonthOptions,
  full: fullMonthOptions,
  numbers: numberMonthOptions,
}

export const DateTime = ({ question }) => {
  const [selectedMonth, setSelectedMonth] = useState('')
  const [selectedYear, setSelectedYear] = useState('')
  const [selectedHour, setSelectedHour] = useState('')
  const [selectedMin, setSelectedMin] = useState('')
  const [minuteOptions, setMinuteOptions] = useState([])

  const [selectedDay, setSelectedDay] = useState('')
  const dateTimeformat = question.attributes.dateTimeFormat?.value.split(
    ' '
  ) || ['YYYY-MM-DD']

  const splitSymbol = dateTimeformat[0].includes('-') ? '-' : '/'

  useEffect(() => {
    const minList = [
      ...generateMinuteList(
        parseInt(question?.attributes?.minuteStepInterval?.value || 1),
        0,
        59
      ),
    ]
    const defaultMinOptions = [
      { label: 'Min', value: 'min' },
      ...minList.map((data) => {
        if (data < 10) {
          return { label: `0${data}`, value: `0${data}` }
        }
        return { label: `${data}`, value: `${data}` }
      }),
    ]
    setMinuteOptions([...defaultMinOptions])
  }, [question?.attributes?.minuteStepInterval?.value])

  return (
    <LocalizationProvider dateAdapter={AdapterDayjs}>
      <DemoContainer components={['DatePicker']}>
        <div className="w-100">
          {question.attributes.dropdownBox?.value ? (
            <div className="d-flex align-items-center w-100">
              {dateTimeformat[0].split(splitSymbol).map((format, idx) => {
                if (['YYYY', 'YY'].includes(format)) {
                  return (
                    <div key={idx} className="d-flex align-items-center">
                      {idx !== 0 && <span className="mx-1">{splitSymbol}</span>}
                      <YearPicker
                        setSelectedYear={setSelectedYear}
                        selectedYear={selectedYear}
                        question={question}
                      />
                    </div>
                  )
                } else if (['MM'].includes(format)) {
                  return (
                    <div key={idx} className="d-flex align-items-center">
                      {idx !== 0 && <span className="mx-1">{splitSymbol}</span>}

                      <MonthPicker
                        setSelectedMonth={setSelectedMonth}
                        selectedMonth={selectedMonth}
                        selectedType={
                          question.attributes.monthDisplay?.value || 'short'
                        }
                      />
                    </div>
                  )
                } else if (['DD'].includes(format)) {
                  return (
                    <div key={idx} className="d-flex align-items-center">
                      {idx !== 0 && <span className="mx-1">{splitSymbol}</span>}
                      <DayPicker
                        setSelectedDay={setSelectedDay}
                        selectedDay={selectedDay}
                        question={question}
                      />
                    </div>
                  )
                }
                return <></>
              })}
              {dateTimeformat[1] && (
                <div className="ms-2 d-flex align-items-center">
                  <HourPicker
                    selectedHour={selectedHour}
                    setSelectedHour={setSelectedHour}
                  />
                  <span className="mx-1">:</span>

                  <MinutePicker
                    minuteOptions={minuteOptions}
                    selectedMinute={selectedMin}
                    setSelectedMinute={setSelectedMin}
                  />
                </div>
              )}
            </div>
          ) : (
            <div className="w-50">
              <DatePicker />
            </div>
          )}
        </div>
      </DemoContainer>
    </LocalizationProvider>
  )
}

const HourPicker = ({ selectedHour, setSelectedHour }) => (
  <Select
    options={hourOptions}
    onChange={({ target: { value } }) => {
      setSelectedHour(value)
    }}
    selectedOption={{
      label: hourOptions.find((option) => option?.value === selectedHour),
      value: selectedHour,
    }}
  />
)

const MinutePicker = ({ minuteOptions, selectedMinute, setSelectedMinute }) => (
  <Select
    options={minuteOptions}
    onChange={({ target: { value } }) => {
      setSelectedMinute(value)
    }}
    selectedOption={{
      label: minuteOptions.find((option) => option?.value === selectedMinute),
      value: selectedMinute,
    }}
  />
)

const YearPicker = ({ setSelectedYear, selectedYear }) => (
  <Select
    options={yearOptions}
    onChange={({ target: { value } }) => {
      setSelectedYear(value)
    }}
    selectedOption={{
      label: yearOptions.find((option) => option?.value === selectedYear),
      value: selectedYear,
    }}
  />
)

const DayPicker = ({ setSelectedDay, selectedDay }) => (
  <Select
    options={dayOptions}
    onChange={({ target: { value } }) => {
      setSelectedDay(value)
    }}
    selectedOption={{
      label: dayOptions.find((option) => option?.value === selectedDay),
      value: selectedDay,
    }}
  />
)

const MonthPicker = ({ selectedType, setSelectedMonth, selectedMonth }) => (
  <Select
    options={monthOptions[selectedType]}
    onChange={({ target: { value } }) => {
      setSelectedMonth(value)
    }}
    selectedOption={{
      label: monthOptions[selectedType]?.find(
        (option) => option?.value === selectedMonth
      ),
      value: selectedMonth,
    }}
  />
)
