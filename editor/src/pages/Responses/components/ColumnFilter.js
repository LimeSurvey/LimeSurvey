import { format } from 'date-fns'

import { DateRangePicker, getQuestionTypeInfo, Input, Select } from 'components'
import {
  completedColumnKey,
  containfilter,
  dateRangeFilter,
  multiSelectFilter,
  rangeFilter,
} from '../utils'

export const filterFns = {
  [multiSelectFilter]: (row, columnId, filterValue) => {
    return filterValue?.includes(row.getValue(columnId))
  },
  [rangeFilter]: (row, columnId, filterValue) => {
    const [min, max] = filterValue ?? []
    const value = row.getValue(columnId)
    if (min !== '' && value < min) return false
    if (max !== '' && value > max) return false
    return true
  },
  [dateRangeFilter]: (row, columnId, filterValue) => {
    const [from, to] = filterValue ?? []
    const cellDate = row.getValue(columnId)

    if (!cellDate) return false

    const cellTime = new Date(cellDate).getTime()
    const fromTime = from ? new Date(from).getTime() : null
    const toTime = to ? new Date(to).getTime() : null

    if (fromTime && cellTime < fromTime) {
      return false
    }

    if (toTime && cellTime > toTime) {
      return false
    }

    return true
  },
}

const defaultDateFormatter = 'yyyy-MM-dd'
const minDate = new Date('2000-04-17T15:30')
const maxDate = new Date('2050-04-17T15:30')

export const ColumnFilter = ({ column, columnsFilters, setColumnsFilters }) => {
  const filterType = column.columnDef.meta?.filterType
  const question = column.columnDef.meta?.question
  const language = column.columnDef.meta?.language
  const columnFilterValue = columnsFilters[column.id] || {}
  const keys = column.columnDef.meta?.keys ?? []

  const setFilterValue = (value, type) => {
    let isValidValue = true

    if (type === dateRangeFilter) {
      isValidValue = value?.startDate || value?.endDate
    } else if (type === containfilter) {
      isValidValue = value?.length > 0
    } else if (type === rangeFilter) {
      const minValue = value[0] || 0
      const maxValue = value[1] || Infinity
      isValidValue = minValue <= maxValue && (value[0] || value[1])
    } else {
      isValidValue = value?.some((_value) => _value !== '')
    }

    if (!isValidValue) {
      delete columnsFilters[column.id]
      setColumnsFilters({ ...columnsFilters })

      return
    }

    if (type === dateRangeFilter) {
      const startDate = value?.startDate
        ? value?.startDate
        : format(minDate, defaultDateFormatter)
      const endDate = value?.endDate
        ? value?.endDate
        : format(maxDate, defaultDateFormatter)

      value = [startDate, endDate]
    }

    setColumnsFilters({
      ...columnsFilters,
      [column.id]: { value, keys, filterMethod: type },
    })
  }

  if (!filterType) {
    return null
  }

  let options = []
  if (filterType === multiSelectFilter) {
    const idIsCompletedColumnkey = column.id === completedColumnKey

    if (!question) {
      options =
        column.columnDef.meta?.answerOptions?.map((answer) => ({
          label: !idIsCompletedColumnkey
            ? answer?.toString()
            : answer
              ? 'Yes'
              : 'No',
          value: answer?.toString(),
        })) || []
    } else if (filterType !== null) {
      const isFivePointChoice =
        question.questionThemeName ===
        getQuestionTypeInfo().SINGLE_CHOICE_FIVE_POINT_CHOICE.theme

      if (isFivePointChoice) {
        options = [1, 2, 3, 4, 5].map((answer) => ({
          label: answer,
          value: answer,
        }))
      } else {
        options =
          question.answers?.map((answer) => ({
            label: answer?.l10ns[language]?.answer || '',
            value: answer?.l10ns[language]?.answer || '',
          })) || []
      }
    }

    const defaultValue = options.filter((option) =>
      columnFilterValue.value?.some((value) => value === option.value)
    )

    return (
      <div>
        <Select
          options={options}
          onChange={(selected) => {
            const values = selected?.map((item) => item.value) || []
            setFilterValue(values, multiSelectFilter)
          }}
          defaultValue={defaultValue}
          isMultiselect={true}
          placeholder={t('Filter column')}
          styles={{
            control: (base) => ({
              ...base,
              minHeight: '30px',
              fontSize: '0.8rem',
            }),
            menu: (base) => ({
              ...base,
              fontSize: '0.8rem',
            }),
          }}
        />
      </div>
    )
  }

  if (filterType === dateRangeFilter) {
    const [startDate, endDate] = columnFilterValue.value || ['', '']

    return (
      <div
        className="d-flex flex-column gap-1"
        onClick={(e) => e.stopPropagation()}
      >
        <DateRangePicker
          onUpdate={(value) => setFilterValue(value[0], dateRangeFilter)}
          startDate={startDate}
          endDate={endDate}
        />
      </div>
    )
  }

  if (filterType === rangeFilter) {
    const [min, max] = columnFilterValue.value || ['', '']

    return (
      <div className="d-flex gap-1" onClick={(e) => e.stopPropagation()}>
        <Input
          type="number"
          className="form-control-sm"
          placeholder={t('Min')}
          value={min}
          onChange={(e) =>
            setFilterValue(
              [e.target.value ? Number(e.target.value) : '', max],
              rangeFilter
            )
          }
          {...column.columnDef.props}
        />
        <Input
          type="number"
          className="form-control-sm"
          placeholder={t('Max')}
          value={max}
          onChange={(e) =>
            setFilterValue(
              [min, e.target.value ? Number(e.target.value) : ''],
              rangeFilter
            )
          }
          {...column.columnDef.props}
        />
      </div>
    )
  }

  return (
    <div onClick={(e) => e.stopPropagation()}>
      <Input
        type="text"
        className="form-control form-control-sm"
        placeholder={t('Filter column')}
        onChange={(e) => setFilterValue(e.target.value, containfilter)}
      />
    </div>
  )
}
