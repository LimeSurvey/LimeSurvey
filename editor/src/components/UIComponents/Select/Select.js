import React, { useState, useMemo } from 'react'
import { Form } from 'react-bootstrap'
import ReactSelect from 'react-select'
import CreatableSelect from 'react-select/creatable'
import { format } from 'util'
import classNames from 'classnames'

import { RemoveHTMLTagsInString } from '../../../helpers'
import { getTooltipMessages } from 'helpers/options'
import { TooltipContainer } from '../../TooltipContainer/TooltipContainer'
import { withSurveyUpdatePermission } from '../withSurveyUpdatePermission'

// the reason for using "==" instead of "===" is that sometimes the backend returns numbers and sometimes strings, we want to make sure it works in both cases
// till we have a backend that is consistent in the types it returns, we need to keep this logic here
const optionFilter = (options, fieldName, value) =>
  options.find((option) => option[fieldName] == value)

const SelectField = ({
  hasSurveyUpdatePermission = true,
  labelText,
  options = [],
  className = '',
  dataTestId = '',
  onChange = () => {},
  update = () => {},
  value,
  disabled = false,
  activeDisabled = false,
  noPermissionDisabled = false,
  noAccessDisabled = false,
  onMenuClose = () => {},
  onMenuOpen = () => {},
  isMultiselect = false,
  defaultValue = options[0],
  menuStyle = {},
  menuClassName = '',
  placeholder = t('Please choose...'),
  menuPlacement = 'auto',
  menuPosition = 'absolute',
  formatOptionLabel,
  // Lets the user type a value that is not in the options list
  isCreatable = false,
  isClearable = false,
}) => {
  const [isOpen, setIsOpen] = useState(false)

  options = useMemo(
    () =>
      options.map((option) => ({
        ...option,
        label: RemoveHTMLTagsInString(option.label),
      })),
    [options]
  )

  const permissionDisabled =
    activeDisabled ||
    (!hasSurveyUpdatePermission && noPermissionDisabled) ||
    noAccessDisabled
  const toolTip = activeDisabled
    ? getTooltipMessages().ACTIVE_DISABLED
    : getTooltipMessages().NO_PERMISSION

  if (isCreatable && !isMultiselect && value !== null && value !== undefined) {
    // A value that is not one of the options was typed in, so show it as is
    value =
      value === ''
        ? null
        : (optionFilter(options, 'value', value) ?? {
            label: String(value),
            value,
          })
  } else if (
    value !== null &&
    value !== undefined &&
    !isMultiselect &&
    options?.length
  ) {
    value = optionFilter(options, 'value', value)
  }

  if (
    defaultValue !== null &&
    defaultValue !== undefined &&
    !isMultiselect &&
    options?.length
  ) {
    defaultValue = optionFilter(options, 'value', defaultValue)
  }

  const handleOnChange = (selectedOption) => {
    onChange(selectedOption)

    if (isMultiselect) {
      const updateValue = selectedOption.map((option) => option.value)
      update(updateValue)
    } else {
      // selectedOption is null when the selection was cleared
      update(selectedOption?.value ?? '')
    }
  }

  const handleOnCreateOption = (inputValue) => {
    const newValue = inputValue.trim()
    onChange({ label: newValue, value: newValue })
    update(newValue)
  }

  const SelectComponent = isCreatable ? CreatableSelect : ReactSelect
  const creatableProps = isCreatable
    ? {
        onCreateOption: handleOnCreateOption,
        formatCreateLabel: (inputValue) => format(t('Use "%s"'), inputValue),
      }
    : {}

  const handleOnMenuOpen = () => {
    setIsOpen(true)
    onMenuOpen()
  }

  const handleOnMenuClose = () => {
    setIsOpen(false)
    onMenuClose()
  }

  return (
    <div
      className={classNames(`select-component ${className}`, {
        'select-component-open': isOpen,
      })}
      data-testid={dataTestId}
    >
      {labelText && <Form.Label htmlFor="select">{labelText}</Form.Label>}
      <TooltipContainer tip={toolTip} showTip={permissionDisabled}>
        <SelectComponent
          {...creatableProps}
          classNames={{
            control: () => 'select',
            menu: () => menuClassName,
          }}
          classNamePrefix="select"
          defaultValue={defaultValue}
          value={value}
          onChange={handleOnChange}
          options={options}
          placeholder={placeholder}
          isMulti={isMultiselect}
          menuPlacement={menuPlacement}
          menuPosition={menuPosition}
          menuPortalTarget={document.body}
          formatOptionLabel={formatOptionLabel}
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
              zIndex: 9999,
            }),
            dropdownIndicator: (base) => ({
              ...base,
              color: '#6E748C',
              minWidth: 'fit-content',
              // 1px less than the react-select default to make up for the 2px border, so the select is 38px high
              paddingTop: '7px',
              paddingBottom: '7px',
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
              zIndex: 9999,
            }),
            menu: (baseStyles) => ({
              ...baseStyles,
              ...menuStyle,
              zIndex: 9999,
              whiteSpace: 'normal',
              wordWrap: 'break-word',
            }),
          }}
          isDisabled={disabled || permissionDisabled}
          isClearable={isClearable}
          onMenuOpen={handleOnMenuOpen}
          onMenuClose={handleOnMenuClose}
        />
      </TooltipContainer>
    </div>
  )
}

export const Select = withSurveyUpdatePermission(SelectField, 'Select')

export const selectName = Select.name
