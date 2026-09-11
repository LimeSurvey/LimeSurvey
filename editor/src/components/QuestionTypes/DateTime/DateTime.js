import React, { useEffect, useState } from 'react'
import { DemoContainer } from '@mui/x-date-pickers/internals/demo'
import { AdapterDayjs } from '@mui/x-date-pickers/AdapterDayjs'
import { LocalizationProvider } from '@mui/x-date-pickers/LocalizationProvider'
import { DatePicker } from '@mui/x-date-pickers/DatePicker'

import { Select } from 'components/UIComponents'

const shortMonthOptions = [
  { label: t('Month'), value: 'month' },
  { label: t('Jan'), value: 'jan' },
  { label: t('Feb'), value: 'feb' },
  { label: t('Mar'), value: 'mar' },
  { label: t('Apr'), value: 'apr' },
  { label: t('May'), value: 'may' },
  { label: t('Jun'), value: 'jun' },
  { label: t('Jul'), value: 'jul' },
  { label: t('Aug'), value: 'aug' },
  { label: t('Sep'), value: 'sep' },
  { label: t('Oct'), value: 'oct' },
  { label: t('Nov'), value: 'nov' },
  { label: t('Dec'), value: 'dec' },
]
const fullMonthOptions = [
  { label: t('Month'), value: 'month' },
  { label: t('January'), value: 'jan' },
  { label: t('February'), value: 'feb' },
  { label: t('March'), value: 'mar' },
  { label: t('April'), value: 'apr' },
  { label: t('May'), value: 'may' },
  { label: t('June'), value: 'jun' },
  { label: t('July'), value: 'jul' },
  { label: t('August'), value: 'aug' },
  { label: t('September'), value: 'sep' },
  { label: t('October'), value: 'oct' },
  { label: t('November'), value: 'nov' },
  { label: t('December'), value: 'dec' },
]
const numberMonthOptions = [
  { label: t('Month'), value: 'month' },
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
  { label: t('Day'), value: 'day' },
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
  { label: t('Hour'), value: 'hour' },
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
  { label: t('Year'), value: 'year' },
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
      { label: t('Min'), value: 'min' },
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
        <div className="w-100" data-testid="date-time">
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
