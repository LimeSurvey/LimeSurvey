import React from 'react'
import { components } from 'react-select'
import { Form } from 'react-bootstrap'
import { Select } from 'components/UIComponents'

const options = [
  { value: 'numbers', label: t('Numbers') },
  { value: 'stars', label: t('Stars') },
]

const items = [
  {
    label: '1',
    value: '1',
  },
  {
    label: '2',
    value: '2',
  },
  {
    label: '3',
    value: '3',
  },
  {
    label: '4',
    value: '4',
  },
  {
    label: '5',
    value: '5',
  },
]

export const RatingItems = ({
  itemsCount,
  itemsType,
  update,
  activeDisabled = false,
}) => {
  const Control = ({ children, ...props }) => (
    <components.Control {...props}>
      {props.getValue().length > 0 &&
        (props.getValue()[0].value === 'stars' ? (
          <i className="ri-star-fill me-1"></i>
        ) : (
          <i className="ri-list-ordered me-1"></i>
        ))}
      {children}
    </components.Control>
  )

  const IconOption = (props) => (
    <components.Option {...props}>
      <div className=" flex align-items-center">
        {props.data.value === 'stars' ? (
          <i className="ri-star-fill me-1"></i>
        ) : (
          <i className="ri-list-ordered me-1"></i>
        )}

        {props.data.label}
      </div>
    </components.Option>
  )
  return (
    <div>
      <Form.Label htmlFor="select">{t('Rating items')}</Form.Label>
      <div className=" d-flex align-items-center justify-content-between">
        <Select
          labelText=""
          selectedOption={items?.find(
            (item) => item.value === (itemsCount?.value || '5')
          )}
          options={items}
          onChange={(selectedOption) => {
            update({
              items_count: {
                '': {
                  ...itemsCount,
                  value: selectedOption.value,
                },
              },
            })
          }}
          activeDisabled={activeDisabled}
        />

        <Select
          selectedOption={options?.find(
            (item) => item?.value === (itemsType?.value || 'numbers')
          )}
          options={options}
          onChange={(selectedOption) => {
            update({
              items_type: {
                '': {
                  ...itemsType,
                  value: selectedOption.value,
                },
              },
            })
          }}
          placeholder=""
          components={{
            IndicatorSeparator: () => null,
            Option: IconOption,
            Control,
          }}
          theme={(theme) => ({
            ...theme,
            colors: {
              ...theme.colors,
              primary: '#8146F6',
            },
          })}
          styles={{
            dropdownIndicator: (base) => ({
              ...base,
              color: '#6E748C',
            }),

            control: (baseStyles) => ({
              ...baseStyles,
              'borderRadius': '4px',
              'borderWidth': '2px',
              'borderColor': ' #6E748C',
              'paddingLeft': '0.5rem',
              'boxShadow': 'none',
              'fontWeight': 400,
              'fontSize': '0.9975rem',
              'width': '155px',

              '&:hover': {
                borderColor: ' #6E748C',
              },
            }),
          }}
          activeDisabled={activeDisabled}
        />
      </div>
    </div>
  )
}
