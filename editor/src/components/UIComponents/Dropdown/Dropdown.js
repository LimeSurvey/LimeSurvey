import React from 'react'
import { Dropdown as BootstrapDropdown } from 'react-bootstrap'
import { TooltipContainer } from 'components'

// {
//   type: 'item' || 'header || 'divider',
//   label: t('Editor'),
//   icon: 'ri-bar-chart-horizontal-line',
//   url: '/survey/'+ surveyId + '/structure',
//   onClick: () => {}
// },

/**
 * toggleSettings: {title, className, variant, id}
 */

export const Dropdown = ({
  menuItems = [],
  toggleSettings = {
    iconClassName: 'ri-more-fill',
    variant: 'light',
    id: '', // must be unique to trigger the menu
    title: '',
  },
}) => {
  return (
    <BootstrapDropdown className="lsr-dropdown" align="end">
      <BootstrapDropdown.Toggle
        variant={toggleSettings.variant}
        className="button me-2"
        id={toggleSettings.id}
        role="menu"
      >
        <i className={toggleSettings.iconClassName}></i>
        {toggleSettings.title}
      </BootstrapDropdown.Toggle>
      <BootstrapDropdown.Menu>
        {menuItems.map(
          ({ type, label, icon, url, onClick, disabled = {} }, index) => (
            <React.Fragment key={`${label}-dropdown-${type}-${index}`}>
              {type === 'header' && (
                <BootstrapDropdown.Header>{label}</BootstrapDropdown.Header>
              )}
              {type === 'divider' && <BootstrapDropdown.Divider />}
              {(type === 'item' || !type) && (
                <TooltipContainer
                  tip={disabled.tooltip}
                  showTip={disabled.state && disabled.tooltip}
                >
                  <BootstrapDropdown.Item
                    disabled={disabled.state}
                    href={url ? url : '#'}
                    onClick={onClick}
                  >
                    <i className={icon}></i>
                    {label}
                  </BootstrapDropdown.Item>
                </TooltipContainer>
              )}
            </React.Fragment>
          )
        )}
      </BootstrapDropdown.Menu>
    </BootstrapDropdown>
  )
}
