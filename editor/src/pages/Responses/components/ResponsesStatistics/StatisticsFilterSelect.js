import React from 'react'
import { Dropdown } from 'react-bootstrap'

export const StatisticsFilterSelect = ({
  label,
  options = [],
  value,
  onChange = () => {},
  allOption,
}) => {
  const selected = options.find((option) => option.key === value)
  const selectedLabel = selected?.title || allOption?.label || ''

  return (
    <div className="responses-statistics-filter">
      {label && <span className="responses-statistics-filter-label">{label}</span>}
      <Dropdown className="responses-statistics-filter-dropdown">
        <Dropdown.Toggle variant="light" className="responses-statistics-filter-toggle">
          <span className="responses-statistics-filter-value">
            {selected?.fill && (
              <span
                className="responses-statistics-filter-swatch"
                style={{ backgroundColor: selected.fill }}
              />
            )}
            {selectedLabel}
          </span>
        </Dropdown.Toggle>
        <Dropdown.Menu>
          {allOption && (
            <Dropdown.Item active={!value} onClick={() => onChange('')}>
              {allOption.label}
            </Dropdown.Item>
          )}
          {options.map((option) => (
            <Dropdown.Item
              key={option.key}
              active={option.key === value}
              onClick={() => onChange(option.key)}
            >
              {option.fill && (
                <span
                  className="responses-statistics-filter-swatch"
                  style={{ backgroundColor: option.fill }}
                />
              )}
              {option.title || option.key}
            </Dropdown.Item>
          ))}
        </Dropdown.Menu>
      </Dropdown>
    </div>
  )
}
