import classNames from 'classnames'
import React, { useMemo } from 'react'
import { useParams } from 'react-router-dom'

import { FeedbackButton, TooltipContainer } from 'components'

import { SideBarPanel } from './SideBarPanel'

/**
 * An example of an item:
 * {
 *  icon?: <Icon />,
 *  label: 'Label',
 *  "tip"?: "Tooltip text",
 *  disabled?: (survey) => boolean, // A function that receives survey object and returns true if item should be disabled
 *  disabledMessage?: "Reason why disabled", // Message to show in tooltip when disabled
 *  onIconClickEvent?: 'Presentation', // a string (e.g., 'Presentation', 'Translation', 'Structure')
 *  onIconClickCallback?: () => {}, // A function to be called when the item is clicked
 *  onPanelClickEvent?: 'Menu', // a string (e.g., 'Menu', 'Structure')
 *  onPanelClickCallback?: () => {}, // A function to be called when the item is clicked
 *  onSidebarClose: () => {}, // A function to be called when the sidebar is closed
 * }
 */

export const SideBar = ({
  testId = '',
  sidebarClassName = '',
  items = [],
  onIconClick = () => {},
  page = 'survey',
  showCloseButton = true,
  showFeedbackButton = false,
}) => {
  const { panel: currentPanel } = useParams()
  const panelInfo = useMemo(() => {
    return items.find((item) => item.panel === currentPanel) || {}
  }, [currentPanel, items])

  return (
    <div
      className={`sidebar d-flex flex-row ${sidebarClassName}`}
      data-testid={testId}
      id="sidebar"
    >
      <div className="sidebar-icons d-flex flex-column pb-2">
        {items
          .filter((item) => item.icon)
          .map((item, index) => {
            return (
              <React.Fragment key={index + 'sidebar'}>
                <div
                  onClick={() => {
                    if (!item.isDisabled) {
                      onIconClick(item.panel)
                    }
                  }}
                  className={classNames('cursor-pointer', {
                    'margin-top-10': index > 0,
                    'sidebar-icon-disabled': item.isDisabled,
                  })}
                  data-testid={`btn-${item.panel}-open`}
                  id={`btn-${item.panel}-open`}
                >
                  <TooltipContainer
                    offset={[0, 20]}
                    placement="right"
                    tip={
                      item.isDisabled && item.disabledMessage
                        ? item.disabledMessage
                        : item.tip || item.label
                    }
                  >
                    <item.icon
                      className={classNames('fill-current', {
                        'text-black': item.panel === currentPanel,
                        'text-white': item.panel !== currentPanel,
                      })}
                      bgcolor={`${
                        currentPanel === item.panel ? '#EEEFF7' : '#333641'
                      }`}
                    />
                  </TooltipContainer>
                </div>
              </React.Fragment>
            )
          })}

        {showFeedbackButton && (
          <div className="mt-2 d-flex flex-wrap align-content-between pb-2">
            <FeedbackButton />
          </div>
        )}
      </div>
      {panelInfo.panelItems?.length ? (
        <SideBarPanel
          label={panelInfo.label}
          options={panelInfo.panelItems}
          page={page}
          showCloseButton={showCloseButton}
        />
      ) : (
        <></>
      )}
      {panelInfo.panelComponent ? panelInfo.panelComponent : <></>}
    </div>
  )
}
