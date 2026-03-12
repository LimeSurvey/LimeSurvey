import { useEffect, useRef, useState } from 'react'
import { Form } from 'react-bootstrap'

import { useAppState } from 'hooks'
import { STATES } from 'helpers'
import { getTooltipMessages } from 'helpers/options'

import { TooltipContainer } from '../../TooltipContainer/TooltipContainer'
import { useQueryClient } from '@tanstack/react-query'

export const Input = ({
  value = '',
  dataTestId,
  onChange = () => {},
  onBlur = () => {},
  inputRef: inputRefProp = null,
  type = 'text',
  role = 'input',
  rows = 1,
  placeholder = t('Enter here'),
  id = '',
  Icon,
  leftIcons,
  labelText,
  paddinRight = '40px',
  paddingLeft = '40px',
  disabled = false,
  max = Infinity,
  min = -Infinity,
  className = '',
  iconClassName = '',
  style = {},
  errorMessage,
  inputClass = '',
  labelClass = '',
  showClassWhenValue = false,
  update = () => {},
  activeDisabled = false,
  noPermissionDisabled = false,
  noAccessDisabled = false,
  autoComplete = true,
  allowEmpty = true,
  focus,
}) => {
  const [canUseAppState, setCanUseAppState] = useState(false)
  const inputRef = inputRefProp ? inputRefProp : useRef(null)

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
    : [false]
  const [hasSurveyUpdatePermission] = canUseAppState
    ? useAppState(STATES.HAS_SURVEY_UPDATE_PERMISSION)
    : [true]

  const inputDisabled =
    (isSurveyActive && activeDisabled) ||
    disabled ||
    (!hasSurveyUpdatePermission && noPermissionDisabled) ||
    noAccessDisabled

  const toolTip =
    isSurveyActive && activeDisabled
      ? getTooltipMessages().ACTIVE_DISABLED
      : (!hasSurveyUpdatePermission && noPermissionDisabled) || noAccessDisabled
        ? getTooltipMessages().NO_PERMISSION
        : ''

  useEffect(() => {
    if (!inputRef?.current) {
      return
    }

    inputRef.current.value = value
  }, [inputRef, value])

  const handleOnChange = (event) => {
    const value = event.target.value

    if (value === '' && allowEmpty) {
      onChange(event)
      update(event.target.value)
    } else if (+value > max) {
      event.target.value = max
    } else if (+value < min) {
      event.target.value = min
    } else {
      event.target.value = value
    }

    onChange(event)
    update(event.target.value)
  }

  const handleOnBlur = (event) => {
    onBlur(event)
  }

  return (
    <>
      <Form.Group
        style={{
          position: Icon && 'relative',
          ...style,
        }}
        className={`qe-input-group ${className}`}
      >
        {leftIcons && typeof leftIcons === 'string' && (
          <img
            style={{ left: 0, right: '100%' }}
            src={leftIcons}
            className="qe-input-icon-left"
            alt="input icon"
          />
        )}
        {leftIcons && typeof leftIcons !== 'string' && (
          <span className="qe-input-icon-left">{leftIcons}</span>
        )}
        {labelText && (
          <Form.Label className={`ui-label ${labelClass}`}>
            {labelText}
          </Form.Label>
        )}
        <TooltipContainer tip={toolTip} showTip={inputDisabled}>
          <Form.Control
            pattern="[A-Z0-9]+"
            disabled={inputDisabled}
            id={id}
            ref={inputRef}
            data-testid={dataTestId}
            placeholder={placeholder}
            onChange={handleOnChange}
            defaultValue={value}
            type={type}
            role={role}
            rows={rows}
            as={role}
            onBlur={handleOnBlur}
            style={{
              paddingRight: Icon && paddinRight,
              paddingLeft: leftIcons && paddingLeft,
            }}
            className={`form-control ${
              showClassWhenValue ? (value ? inputClass : '') : inputClass
            }`}
            autoComplete={autoComplete ? 1 : 0}
            autoFocus={focus}
          />
        </TooltipContainer>
        {Icon && typeof Icon === 'string' && (
          <img
            src={Icon}
            className={`qe-input-icon-right ${iconClassName}`}
            alt="input icon"
          />
        )}
        {Icon && typeof Icon !== 'string' && (
          <span className={`qe-input-icon-right ${iconClassName}`}>{Icon}</span>
        )}
      </Form.Group>
      {errorMessage && (
        <div className={'text-nowrap d-block m-1'}>
          <Form.Text
            style={{ fontSize: '14px', fontWeight: '500' }}
            className="text-danger"
          >
            {errorMessage}
          </Form.Text>
        </div>
      )}
    </>
  )
}
