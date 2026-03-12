import classNames from 'classnames'
import React from 'react'
import { Form } from 'react-bootstrap'

import { TooltipContainer } from 'components'

export const TextArea = ({
  labelText,
  labelCommentText,
  value,
  onChange = () => {},
  rows = 4,
  maxLength = 30,
  disabled = false,
}) => {
  return (
    <TooltipContainer
      tip={t('Coming soon')}
      showTip={disabled}
      placement="bottom"
    >
      <div className="w-full">
        {labelText && (
          <Form.Label className="ui-label">
            {labelText}{' '}
            {labelCommentText && (
              <span className="fw-normal ps-1">({labelCommentText})</span>
            )}
          </Form.Label>
        )}
        <div className="position-relative">
          <textarea
            id="text-area-custom"
            className={classNames({ disabled: disabled })}
            value={value}
            onChange={(e) => onChange(e.target.value)}
            rows={rows}
            maxLength={maxLength}
            disabled={disabled}
          />
          <span className="textarea-length-text">
            {value?.length ?? 0} / {maxLength}
          </span>
        </div>
      </div>
    </TooltipContainer>
  )
}
