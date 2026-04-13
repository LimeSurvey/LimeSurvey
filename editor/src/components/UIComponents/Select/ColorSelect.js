import { Form } from 'react-bootstrap'
import ReactSelect from 'react-select'

export const ColorSelect = ({ labelText, value, options = [], onChange }) => {
  const selectedValue =
    typeof value === 'string'
      ? options.find((opt) => opt.value === value)
      : value

  const formatOptionLabel = ({ label, value }) => {
    return (
      <div className="color-select-option">
        <span
          className="color-select-preview"
          style={{ backgroundColor: value }}
        />
        <span className="color-select-label">{label}</span>
      </div>
    )
  }

  return (
    <div className="select-component color-select">
      {labelText && <Form.Label>{labelText}</Form.Label>}
      <ReactSelect
        classNames={{
          control: () => 'select',
        }}
        formatOptionLabel={formatOptionLabel}
        value={selectedValue}
        onChange={onChange}
        options={options}
        components={{
          IndicatorSeparator: () => null,
        }}
        theme={(theme) => ({
          ...theme,
          colors: {
            ...theme.colors,
            primary: '#8146F6',
          },
        })}
        styles={{
          menuPortal: (base) => ({
            ...base,
            zIndex: 4,
          }),
          dropdownIndicator: (base) => ({
            ...base,
            color: '#6E748C',
            minWidth: 'fit-content',
          }),
          control: (baseStyles) => ({
            ...baseStyles,
            'borderRadius': '4px',
            'borderWidth': '2px',
            'borderColor': ' #6E748C',
            'boxShadow': 'none',
            'fontWeight': 400,
            'fontSize': '0.9975rem',
            '&:hover': {
              borderColor: ' #6E748C',
            },
          }),
          option: (baseStyles) => ({
            ...baseStyles,
            whiteSpace: 'normal',
            wordWrap: 'break-word',
          }),
          menu: (baseStyles) => ({
            ...baseStyles,
            width: '100%',
            minWidth: 'min-content',
            whiteSpace: 'normal',
            wordWrap: 'break-word',
          }),
        }}
        isClearable={false}
      />
    </div>
  )
}
