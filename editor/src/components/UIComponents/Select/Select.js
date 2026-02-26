import React, { useEffect, useState } from 'react'
import { Form } from 'react-bootstrap'
import ReactSelect from 'react-select'
import classNames from 'classnames'
import { useQueryClient } from '@tanstack/react-query'

import { useAppState } from '../../../hooks'
import { STATES } from '../../../helpers'
import { getTooltipMessages } from 'helpers/options'
import { TooltipContainer } from '../../TooltipContainer/TooltipContainer'

export const Select = ({
  labelText,
  options = [],
  className = '',
  dataTestId = '',
  onChange = () => {},
  update = () => {},
  value,
  activeDisabled = false,
  noPermissionDisabled = false,
  noAccessDisabled = false,
  onMenuClose = () => {},
  onMenuOpen = () => {},
  isMultiselect = false,
  defaultValue = options[0],
  menuStyle = {},
  placeholder = t('Please choose...'),
  menuPlacement = 'bottom',
}) => {
  const [isOpen, setIsOpen] = useState(false)
  const [canUseAppState, setCanUseAppState] = useState(false)

  useEffect(() => {
    try {
      useQueryClient()
      setCanUseAppState(true)
    } catch (error) {
      setCanUseAppState(false)
    }
  }, [])

  const [isSurveyActive] = canUseAppState
    ? useAppState(STATES.IS_SURVEY_ACTIVE)
    : [true]
  const [hasSurveyUpdatePermission] = canUseAppState
    ? useAppState(STATES.HAS_SURVEY_UPDATE_PERMISSION)
    : [true]

  const disabled =
    (isSurveyActive && activeDisabled) ||
    (!hasSurveyUpdatePermission && noPermissionDisabled) ||
    noAccessDisabled
  const toolTip =
    isSurveyActive && activeDisabled
      ? getTooltipMessages().ACTIVE_DISABLED
      : getTooltipMessages().NO_PERMISSION

  if (
    value !== null &&
    value !== undefined &&
    !isMultiselect &&
    options?.length
  ) {
    value = options.find((option) => option.value === value)
  }

  if (
    defaultValue !== null &&
    defaultValue !== undefined &&
    !isMultiselect &&
    options?.length
  ) {
    defaultValue = options.find((option) => option.value === defaultValue)
  }

  const handleOnChange = (selectedOption) => {
    onChange(selectedOption)

    if (isMultiselect) {
      const updateValue = selectedOption.map((option) => option.value)
      update(updateValue)
    } else {
      update(selectedOption.value)
    }
  }

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
      <TooltipContainer tip={toolTip} showTip={disabled}>
        <ReactSelect
          classNames={{
            control: () => 'select',
          }}
          classNamePrefix="select"
          defaultValue={defaultValue}
          value={value}
          onChange={handleOnChange}
          options={options}
          placeholder={placeholder}
          isMulti={isMultiselect}
          menuPlacement={menuPlacement}
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
              zIndex: 9999,
            }),
            menu: (baseStyles) => ({
              ...baseStyles,
              ...menuStyle,
              width: '100%',
              minWidth: 'min-content',
              whiteSpace: 'normal',
              wordWrap: 'break-word',
            }),
          }}
          isDisabled={disabled}
          isClearable={false}
          onMenuOpen={handleOnMenuOpen}
          onMenuClose={handleOnMenuClose}
        />
      </TooltipContainer>
    </div>
  )
}

export const selectName = Select.name
