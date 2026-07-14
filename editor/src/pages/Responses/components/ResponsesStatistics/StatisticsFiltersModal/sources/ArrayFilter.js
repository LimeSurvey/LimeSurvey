import React from 'react'

import { Input } from 'components'

import { FilterSelect } from '../FilterSelect'
import { NumberRangeField } from './fields'

// Array question value UI: pick a row (subquestion) and column, plus — depending
// on the kind — a second column scale or a per-cell value. Shape of `array`
// (rows / columns / columns2 / labels / valueKind) is built in buildQuestionOptions.
//   arrayScale (F/H) → row + column
//   arrayDual  (1)   → row + two labelled column scales
//   arrayGrid  (:/;) → row + column + value (Min/Max or keywords)
export const ArrayFilter = ({ question, filter, onUpdate }) => {
  const { kind, array } = question

  const rowSelect = (
    <div className="responses-statistics-filters-row-field">
      <FilterSelect
        options={array.rows}
        value={filter.row}
        defaultValue={null}
        placeholder={t('Select row ...')}
        update={(value) => onUpdate('row', value)}
      />
    </div>
  )

  // A column dropdown, optionally captioned (used labelled for the dual scales).
  const renderColumn = ({ options, value, key, label }) => (
    <div className="responses-statistics-filters-row-field">
      {label && (
        <span className="responses-statistics-filters-col-label">{label}</span>
      )}
      <FilterSelect
        options={options}
        value={value}
        defaultValue={null}
        placeholder={t('Select column ...')}
        update={(next) => onUpdate(key, next)}
      />
    </div>
  )

  if (kind === 'arrayDual') {
    return (
      <>
        {rowSelect}
        <div className="responses-statistics-filters-row-range">
          {renderColumn({
            options: array.columns,
            value: filter.column,
            key: 'column',
            label: array.columnLabel,
          })}
          {renderColumn({
            options: array.columns2,
            value: filter.column2,
            key: 'column2',
            label: array.columnLabel2,
          })}
        </div>
      </>
    )
  }

  if (kind === 'arrayGrid') {
    return (
      <>
        <div className="responses-statistics-filters-row-range">
          {rowSelect}
          {renderColumn({
            options: array.columns,
            value: filter.column,
            key: 'column',
          })}
        </div>
        {array.valueKind === 'number' ? (
          <NumberRangeField
            min={filter.numberMin}
            max={filter.numberMax}
            onMinChange={(value) => onUpdate('numberMin', value)}
            onMaxChange={(value) => onUpdate('numberMax', value)}
          />
        ) : (
          <div className="responses-statistics-filters-row-field">
            <Input
              placeholder={t('Filter for keywords ...')}
              value={filter.textValue}
              update={(value) => onUpdate('textValue', value)}
            />
          </div>
        )}
      </>
    )
  }

  // arrayScale: row + column, no value.
  return (
    <div className="responses-statistics-filters-row-range">
      {rowSelect}
      {renderColumn({
        options: array.columns,
        value: filter.column,
        key: 'column',
      })}
    </div>
  )
}
