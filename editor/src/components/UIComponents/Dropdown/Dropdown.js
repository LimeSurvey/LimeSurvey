import React, { useState } from 'react'
import { Dropdown as BootstrapDropdown } from 'react-bootstrap'
import { TooltipContainer } from 'components'

// {
//   type: 'item' || 'header || 'divider' || 'submenu',
//   label: t('Editor'),
//   icon: 'ri-bar-chart-horizontal-line',
//   url: '/survey/'+ surveyId + '/structure',
//   onClick: () => {},
//   submenu: [] // for type 'submenu'
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
  toggleSettings = {
    iconClassName: 'ri-more-fill',
    variant: 'light',
    id: '', // must be unique to trigger the menu
    title: '',
  },
}) => {
  const [openSubmenu, setOpenSubmenu] = useState(null)

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
          (
            { type, label, icon, url, onClick, disabled = {}, submenu },
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
