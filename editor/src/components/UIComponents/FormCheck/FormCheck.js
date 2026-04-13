import React from 'react'
import { Form } from 'react-bootstrap'

import { useAppState } from '../../../hooks'
import { STATES } from '../../../helpers'
import { getTooltipMessages } from 'helpers/options'
import { TooltipContainer } from '../../TooltipContainer/TooltipContainer'

export const FormCheck = ({
  id,
  className = '',
  dataTestId = '',
  activeDisabled = false,
  noPermissionDisabled = false,
  noAccessDisabled = false,
  type,
  groupName = '',
  value,
  label,
  checked = null,
  defaultChecked = false,
  update = () => {},
  sendValueOnUpdate = false,
}) => {
  const [isSurveyActive] = useAppState(STATES.IS_SURVEY_ACTIVE)
  const [hasSurveyUpdatePermission] = useAppState(
    STATES.HAS_SURVEY_UPDATE_PERMISSION
  )
  const disabled =
    (isSurveyActive && activeDisabled) ||
    (!hasSurveyUpdatePermission && noPermissionDisabled) ||
    noAccessDisabled

  const toolTip =
    isSurveyActive && activeDisabled
      ? getTooltipMessages().ACTIVE_DISABLED
      : getTooltipMessages().NO_PERMISSION

  return (
    <TooltipContainer tip={toolTip} showTip={disabled}>
      <Form.Check
        disabled={disabled}
        type={type}
        id={id}
        name={groupName}
        label={label}
        value={value}
        className={`form-check-ui-component ${className}`}
        data-testid={dataTestId}
        defaultChecked={defaultChecked || checked}
        onChange={(event) =>
          update(sendValueOnUpdate ? value : event.target.checked)
        }
      />
    </TooltipContainer>
  )
}

export const formCheckName = FormCheck.name
