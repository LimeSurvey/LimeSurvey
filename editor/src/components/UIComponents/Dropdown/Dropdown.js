import React, { useState } from 'react'
import { Dropdown as BootstrapDropdown } from 'react-bootstrap'
import classNames from 'classnames'

import { TooltipContainer } from 'components/TooltipContainer/TooltipContainer'

// {
//   type: 'item' || 'header || 'divider' || 'submenu',
//   label: t('Editor'),
//   icon: 'ri-bar-chart-horizontal-line',
//   url: '/survey/'+ surveyId + '/structure',
//   onClick: () => {},
//   submenu: [], // for type 'submenu'
//   checked: true || false, // for type 'item', shows a checkmark on the right
// },

/**
 * toggleSettings: {title, className, variant, id}
 */

const Submenu = ({
  label,
  icon,
  submenu,
  isOpen,
  onMouseEnter,
  onMouseLeave,
}) => {
  return (
    <BootstrapDropdown
      drop="end"
      show={isOpen}
      onMouseEnter={onMouseEnter}
      onMouseLeave={onMouseLeave}
    >
      <BootstrapDropdown.Toggle as="div" className="dropdown-item has-submenu">
        <span>
          <i className={icon}></i>
          {label}
        </span>
        <i className="ri-arrow-right-s-line"></i>
      </BootstrapDropdown.Toggle>
      <BootstrapDropdown.Menu
        className="dropdown-submenu"
        popperConfig={{ modifiers: [{ name: 'flip', enabled: false }] }}
      >
        {submenu &&
          submenu.map((subItem, subIndex) => (
            <TooltipContainer
              key={`${subItem.label}-submenu-${subIndex}`}
              tip={subItem.disabled?.tooltip}
              showTip={subItem.disabled?.state && subItem.disabled?.tooltip}
            >
              <BootstrapDropdown.Item
                href={subItem.url || '#'}
                onClick={subItem.onClick}
                disabled={subItem.disabled?.state}
              >
                {subItem.icon && <i className={subItem.icon}></i>}
                {subItem.label}
              </BootstrapDropdown.Item>
            </TooltipContainer>
          ))}
      </BootstrapDropdown.Menu>
    </BootstrapDropdown>
  )
}

export const Dropdown = ({
  menuItems = [],
  className = '',
  testId = '',
  align = 'end',
  toggleSettings = {
    iconClassName: 'ri-more-fill',
    variant: 'light',
    id: '', // must be unique to trigger the menu
    title: '',
    testId: '',
  },
}) => {
  const [openSubmenu, setOpenSubmenu] = useState(null)

  return (
    <BootstrapDropdown
      className={classNames('lsr-dropdown', className)}
      align={align}
      data-testid={testId}
    >
      <BootstrapDropdown.Toggle
        variant={toggleSettings.variant}
        className="button me-2"
        id={toggleSettings.id}
        data-testid={toggleSettings.testId}
        role="menu"
      >
        {toggleSettings.title}
        <i className={toggleSettings.iconClassName}></i>
      </BootstrapDropdown.Toggle>
      <BootstrapDropdown.Menu>
        {menuItems.map(
          (
            {
              type,
              label,
              icon,
              url,
              onClick,
              disabled = {},
              submenu,
              checked,
              className,
              testId,
            },
            index
          ) => (
            <React.Fragment key={`${label}-dropdown-${type}-${index}`}>
              {type === 'header' && (
                <BootstrapDropdown.Header>{label}</BootstrapDropdown.Header>
              )}
              {type === 'divider' && <BootstrapDropdown.Divider />}
              {type === 'submenu' && (
                <Submenu
                  label={label}
                  icon={icon}
                  submenu={submenu}
                  isOpen={openSubmenu === index}
                  onMouseEnter={() => setOpenSubmenu(index)}
                  onMouseLeave={() => setOpenSubmenu(null)}
                />
              )}
              {(type === 'item' || !type) && (
                <TooltipContainer
                  tip={disabled.tooltip}
                  showTip={disabled.state && disabled.tooltip}
                >
                  <BootstrapDropdown.Item
                    disabled={disabled.state}
                    href={url ? url : '#'}
                    onClick={onClick}
                    data-testid={testId}
                    className={classNames(className, {
                      'has-checkmark': checked !== undefined,
                      'is-checked': checked,
                    })}
                  >
                    {checked === undefined ? (
                      <>
                        <i className={icon}></i>
                        {label}
                      </>
                    ) : (
                      <>
                        <span>
                          <i className={icon}></i>
                          {label}
                        </span>
                        {checked && <i className="ri-check-line"></i>}
                      </>
                    )}
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
