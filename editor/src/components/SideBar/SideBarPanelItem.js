import classNames from 'classnames'
import React from 'react'
import { Form } from 'react-bootstrap'

import { TooltipContainer } from 'components'
import { useNavigate, useParams } from 'react-router-dom'

export const SideBarPanelItem = ({ key, options, page }) => {
  const { panel, menu, surveyId } = useParams()
  const navigate = useNavigate()

  const handleOnOptionClick = () => {
    if (options.redirect) {
      window.open(options.redirect, '_self')
    } else {
      const navPage = options.page !== undefined ? options.page : page
      const navPanel = options.panel !== undefined ? options.panel : panel
      const hasPanel = navPanel && navPanel !== ''

      navigate(
        `/${navPage}/${surveyId}/${navPanel}${hasPanel && '/'}${options.menu}`
      )
    }
  }

  const isActive =
    menu === options.menu ||
    (Array.isArray(options.activeMenus) && options.activeMenus.includes(menu))

  return (
    <TooltipContainer
      key={key}
      tip={options.disabledTip}
      showTip={options.disabled}
      placement="right"
    >
      <div
        onClick={() => handleOnOptionClick()}
        className={classNames(
          `px-4 py-3 d-flex align-items-center cursor-pointer rounded sidebar-panel`,
          {
            'focus-bg-purple': isActive,
            'disabled': options.disabled,
          }
        )}
      >
        <Form.Label
          className={classNames(`cursor-pointer mb-0`, {
            'text-white': isActive,
          })}
        >
          {options?.labelEditor}
        </Form.Label>
      </div>
    </TooltipContainer>
  )
}
