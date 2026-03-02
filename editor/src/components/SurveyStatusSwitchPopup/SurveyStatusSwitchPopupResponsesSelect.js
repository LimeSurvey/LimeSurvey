import React, { useCallback, useMemo } from 'react'
import { Form } from 'react-bootstrap'
import ReactSelect from 'react-select'
import classNames from 'classnames'
import { format } from 'util'

const SurveyStatusSwitchPopupResponsesSelect = ({
  responseTableSelectionOptions,
  isDisabled,
}) => {
  const formatDateTime = useCallback((dateTimeStr) => {
    const year = dateTimeStr.substring(0, 4)
    const month = dateTimeStr.substring(4, 6)
    const day = dateTimeStr.substring(6, 8)
    const hour = dateTimeStr.substring(8, 10)
    const minute = dateTimeStr.substring(10, 12)

    const formattedDate = `${year}-${month}-${day} ${hour}:${minute}`

    return formattedDate
  }, [])

  const selectOptions = useMemo(() => {
    return responseTableSelectionOptions.options.map((option, index) => ({
      value: index,
      label: `${formatDateTime(option.timestamp)} (${option.count} responses)`,
    }))
  }, [responseTableSelectionOptions.options])

  const handleChangeSelect = (option) => {
    responseTableSelectionOptions.onChange(
      responseTableSelectionOptions.options[option.value]
    )
  }
  return (
    <div
      className={classNames({
        'survey-status-switch-popup-footer-disabledSelect': isDisabled,
        'survey-status-switch-popup-footer-enabledSelect': !isDisabled,
      })}
    >
      <Form.Label htmlFor="select">
        {t('Import responses from a deactivated data set')}
      </Form.Label>
      <ReactSelect
        classNames={{
          control: () => 'select',
        }}
        placeholder={format(
          t('Latest response table (%s responses)'),
          responseTableSelectionOptions.options[0]?.count
        )}
        onChange={handleChangeSelect}
        options={selectOptions}
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
        menuPortalTarget={document.body}
        styles={{
          menuPortal: (base) => ({
            ...base,
            zIndex: 9999,
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
            'backgroundColor': 'white',
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
        isDisabled={isDisabled}
      />
    </div>
  )
}

export default SurveyStatusSwitchPopupResponsesSelect
