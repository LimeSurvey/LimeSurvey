import { useRef } from 'react'
import { Dropdown } from 'react-bootstrap'
import classNames from 'classnames'

export const ORIENTATION = {
  VERTICAL: 'vertical',
  HORIZONTAL: 'horizontal',
}

const ORIENTATION_OPTIONS = [
  {
    value: ORIENTATION.VERTICAL,
    label: () => t('Vertical bar chart'),
    iconClassName: 'ri-bar-chart-line',
  },
  {
    value: ORIENTATION.HORIZONTAL,
    label: () => t('Horizontal bar chart'),
    iconClassName: 'ri-bar-chart-horizontal-line',
  },
]

export const getOrientationIconClassName = (orientation) =>
  ORIENTATION_OPTIONS.find(({ value }) => value === orientation)
    ?.iconClassName ?? ORIENTATION_OPTIONS[0].iconClassName

const MENU_GAP = 6

const getMenuOffset = (dropdown, placement) => {
  const group = dropdown?.closest('.btn-group')
  if (!group) {
    return [0, MENU_GAP]
  }
  const groupRect = group.getBoundingClientRect()
  const dropdownRect = dropdown.getBoundingClientRect()
  const skidding =
    getComputedStyle(group).direction === 'rtl'
      ? groupRect.left - dropdownRect.left
      : groupRect.right - dropdownRect.right
  const distance = placement.startsWith('top')
    ? dropdownRect.top - groupRect.top
    : groupRect.bottom - dropdownRect.bottom
  return [skidding, distance + MENU_GAP]
}

export const ChartOrientationMenu = ({ orientation, onSelect }) => {
  const dropdownRef = useRef(null)
  const popperConfig = {
    modifiers: [
      {
        name: 'offset',
        options: {
          offset: ({ placement }) =>
            getMenuOffset(dropdownRef.current, placement),
        },
      },
    ],
  }

  return (
    <Dropdown
      ref={dropdownRef}
      drop="up"
      align="end"
      className="responses-statistics-orientation-dropdown"
    >
      <Dropdown.Toggle
        variant="light"
        aria-label={t('Chart orientation')}
        className="responses-statistics-orientation-toggle"
      >
        <i className="ri-arrow-down-s-line"></i>
      </Dropdown.Toggle>
      <Dropdown.Menu
        popperConfig={popperConfig}
        role="menu"
        aria-label={t('Chart orientation')}
        className="responses-statistics-orientation-menu"
      >
        <Dropdown.Header
          aria-hidden="true"
          className="responses-statistics-orientation-menu-title"
        >
          {t('Chart orientation')}
        </Dropdown.Header>
        {ORIENTATION_OPTIONS.map(({ value, label, iconClassName }) => (
          <Dropdown.Item
            key={value}
            role="menuitemradio"
            aria-checked={value === orientation}
            className={classNames(
              'responses-statistics-orientation-menu-item',
              {
                'responses-statistics-orientation-menu-item--selected':
                  value === orientation,
              }
            )}
            onClick={() => onSelect(value)}
          >
            <i className={iconClassName}></i>
            {label()}
          </Dropdown.Item>
        ))}
      </Dropdown.Menu>
    </Dropdown>
  )
}
