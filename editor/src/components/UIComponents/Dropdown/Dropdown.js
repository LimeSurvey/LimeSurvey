import React, { useEffect, useRef, useState } from 'react'
import { createPortal } from 'react-dom'
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
//   trailingIcon: 'ri-eye-line', // for type 'item', icon class shown on the right
//   hideIconSpace: true, // for type 'item', no placeholder space for a missing left icon
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

// Rendered on document.body so an open menu is not clipped by a scrolling
// parent (the sidebar) and can stay position: absolute while that parent scrolls.
const DropdownMenuPortal = React.forwardRef(
  function DropdownMenuPortal(props, ref) {
    if (typeof document === 'undefined') {
      return null
    }

    const { portalClassName, children, ...domProps } = props
    delete domProps.show
    delete domProps.close
    delete domProps.align

    return createPortal(
      <div className={portalClassName}>
        <div ref={ref} {...domProps}>
          {children}
        </div>
      </div>,
      document.body
    )
  }
)

const DROPDOWN_OPEN_EVENT = 'ls-dropdown-open'
let dropdownIdCounter = 0

export const Dropdown = ({
  menuItems = [],
  className = '',
  testId = '',
  align = 'end',
  portalMenu = false,
  toggleSettings = {
    iconClassName: 'ri-more-fill',
    variant: 'light',
    id: '', // must be unique to trigger the menu
    title: '',
    testId: '',
  },
}) => {
  const [openSubmenu, setOpenSubmenu] = useState(null)
  const [show, setShow] = useState(false)
  const idRef = useRef(`dropdown-${++dropdownIdCounter}`)
  const toggleRef = useRef(null)
  const menuRef = useRef(null)

  // Only one dropdown is open at a time: opening one closes all others.
  useEffect(() => {
    const onOtherOpen = (e) => {
      if (e.detail !== idRef.current) setShow(false)
    }
    window.addEventListener(DROPDOWN_OPEN_EVENT, onOtherOpen)
    return () => window.removeEventListener(DROPDOWN_OPEN_EVENT, onOtherOpen)
  }, [])

  useEffect(() => {
    if (!show) return
    const onMouseDown = (e) => {
      const inToggle = toggleRef.current?.contains(e.target)
      const inMenu = menuRef.current?.contains(e.target)
      if (!inToggle && !inMenu) setShow(false)
    }
    const onKeyDown = (e) => {
      if (e.key === 'Escape') {
        setShow(false)
        toggleRef.current?.focus()
      }
    }
    document.addEventListener('mousedown', onMouseDown)
    document.addEventListener('keydown', onKeyDown)
    return () => {
      document.removeEventListener('mousedown', onMouseDown)
      document.removeEventListener('keydown', onKeyDown)
    }
  }, [show])

  const handleToggle = (nextShow) => {
    if (nextShow) {
      window.dispatchEvent(
        new CustomEvent(DROPDOWN_OPEN_EVENT, { detail: idRef.current })
      )
    }
    setShow(nextShow)
  }

  return (
    <BootstrapDropdown
      show={show}
      onToggle={handleToggle}
      className={classNames('lsr-dropdown', className)}
      align={align}
      data-testid={testId}
    >
      <BootstrapDropdown.Toggle
        ref={toggleRef}
        variant={toggleSettings.variant}
        className="button me-2 dropdown-toggler"
        id={toggleSettings.id}
        data-testid={toggleSettings.testId}
        role="menu"
      >
        {toggleSettings.title}
        <i className={toggleSettings.iconClassName}></i>
      </BootstrapDropdown.Toggle>
      <BootstrapDropdown.Menu
        ref={menuRef}
        {...(portalMenu
          ? {
              as: DropdownMenuPortal,
              portalClassName: classNames('lsr-dropdown', className),
              popperConfig: { strategy: 'absolute' },
            }
          : {})}
      >
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
              trailingIcon,
              hideIconSpace,
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
                      'has-trailing-icon': !!trailingIcon,
                      'no-icon-space': hideIconSpace,
                    })}
                  >
                    {trailingIcon ? (
                      <>
                        <span>
                          {icon && <i className={icon}></i>}
                          {label}
                        </span>
                        <i
                          className={trailingIcon}
                          data-testid={testId && `${testId}-trailing-icon`}
                        ></i>
                      </>
                    ) : checked === undefined ? (
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
