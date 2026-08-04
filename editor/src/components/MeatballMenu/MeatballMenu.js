import React, { useMemo, useState } from 'react'
import { Button, OverlayTrigger, Popover } from 'react-bootstrap'

import { useAppState } from 'hooks'
import { STATES } from 'helpers'
import { TooltipContainer } from 'components'
import { SmallThreeDotIcon } from 'components/icons'
import { getTooltipMessages } from 'helpers/options'

/**
 * MeatballMenu component
 * an example of an item in the meatball menu
 {
    label: 'Duplicate',
    onClick: handleDuplicate,
    tooltip: 'Duplicate the item',
  },
 }
*/
export const MeatballMenu = ({
  duplicateText,
  deleteText,
  handleDuplicate,
  handleDelete,
  testId = '',
  items = [],
  additionalItems = [],
  shouldDisableIfSurveyActive = true,
  meatballClassName = 'meatball-menu ',
  actionsTitle = '',
  placement = 'right',
  submenuPlacement = 'left',
  TogglerIcon = SmallThreeDotIcon,
}) => {
  const [showMeatballMenu, setShowMeatballMenu] = useState(false)
  const [isSurveyActive] = useAppState(STATES.IS_SURVEY_ACTIVE, false)
  const [hasSurveyUpdatePermission] = useAppState(
    STATES.HAS_SURVEY_UPDATE_PERMISSION
  )

  const onDuplicate = () => {
    setShowMeatballMenu(false)
    handleDuplicate()
  }

  const onDelete = () => {
    setShowMeatballMenu(false)
    handleDelete()
  }

  const toolTip = isSurveyActive
    ? getTooltipMessages().ACTIVE_DISABLED
    : getTooltipMessages().NO_PERMISSION

  const meatballMenu = useMemo(() => {
    // if we don't have items, return the default meatball menu (duplicate and delete buttons)
    if (!items.length) {
      return (
        <Popover
          id="meatball-menu-popover"
          bsPrefix="meatball-menu"
          className="meatball-menu ps-3 bg-white"
          data-testid="meatball-menu-overlay"
        >
          {additionalItems.map((item, index) => (
            <Button
              key={index}
              data-testid={item.testId || `${item.label}-button`}
              variant="layout"
              onClick={() => {
                setShowMeatballMenu(false)
                item.onClick()
              }}
            >
              {item.label}
            </Button>
          ))}
          <TooltipContainer
            tip={toolTip}
            showTip={
              (isSurveyActive || !hasSurveyUpdatePermission) &&
              shouldDisableIfSurveyActive
            }
          >
            <Button
              data-testid="duplicate-button"
              disabled={
                (isSurveyActive || !hasSurveyUpdatePermission) &&
                shouldDisableIfSurveyActive
              }
              variant="layout"
              onClick={onDuplicate}
            >
              {duplicateText}
            </Button>
          </TooltipContainer>
          <TooltipContainer
            tip={toolTip}
            showTip={
              (isSurveyActive || !hasSurveyUpdatePermission) &&
              shouldDisableIfSurveyActive
            }
          >
            <Button
              data-testid="delete-button"
              disabled={
                (isSurveyActive || !hasSurveyUpdatePermission) &&
                shouldDisableIfSurveyActive
              }
              className="meat-ball-delete-button"
              variant="layout"
              onClick={onDelete}
            >
              {deleteText}
            </Button>
          </TooltipContainer>
        </Popover>
      )
    }

    return (
      <Popover
        id="meatball-menu-popover"
        bsPrefix={`${meatballClassName}`}
        className={`${meatballClassName} ps-3 bg-white`}
        data-testid="meatball-menu-overlay"
      >
        <p className="label-s-med mb-2 action-title">{actionsTitle}</p>
        {items.map((item, index) => {
          if (item.subItems?.length) {
            return (
              <div key={index} className="meatball-submenu">
                <Button
                  data-testid={`${item.label.toLowerCase()}-button`}
                  variant="layout"
                  className="d-flex gap-2 meatball-button meatball-submenu-toggle"
                  aria-haspopup="true"
                >
                  {item.icon}
                  <span className="label-m">{item.label}</span>
                  <i className="meatball-submenu-caret ri-arrow-right-s-line" />
                </Button>
                <div
                  className={`meatball-submenu-items meatball-submenu-items--${submenuPlacement}`}
                >
                  {item.subItems.map((subItem, subIndex) => (
                    <Button
                      key={subIndex}
                      data-testid={`${subItem.label.toLowerCase()}-button`}
                      variant="layout"
                      onClick={() => {
                        subItem.onClick()
                        setShowMeatballMenu(false)
                      }}
                      className={`d-flex gap-2 meatball-button meatball-submenu-item ${
                        subItem.active ? 'is-active' : ''
                      }`}
                    >
                      {subItem.icon}
                      <span className="label-m">{subItem.label}</span>
                    </Button>
                  ))}
                </div>
              </div>
            )
          }

          return (
            <TooltipContainer
              key={index}
              tip={item.tooltip}
              showTip={
                (isSurveyActive || !hasSurveyUpdatePermission) &&
                shouldDisableIfSurveyActive
              }
            >
              <Button
                data-testid={`${item.label.toLowerCase()}-button`}
                disabled={
                  (isSurveyActive || !hasSurveyUpdatePermission) &&
                  shouldDisableIfSurveyActive
                }
                variant="layout"
                onClick={() => {
                  item.onClick()
                  setShowMeatballMenu(false)
                }}
                className={`d-flex gap-2 meatball-button ${item.className}`}
              >
                {item.icon}
                <span className="label-m">{item.label}</span>
              </Button>
            </TooltipContainer>
          )
        })}
      </Popover>
    )
  })

  return (
    <div data-testid={testId}>
      <OverlayTrigger
        overlay={meatballMenu}
        trigger="click"
        placement={placement}
        show={showMeatballMenu}
        onToggle={(show) => setShowMeatballMenu(show)}
        offset={[6, 22]}
        rootClose={true}
        transition={true}
      >
        <Button
          variant="outline"
          className="p-0"
          data-testid="meatball-menu-button"
        >
          <TogglerIcon />
        </Button>
      </OverlayTrigger>
    </div>
  )
}
