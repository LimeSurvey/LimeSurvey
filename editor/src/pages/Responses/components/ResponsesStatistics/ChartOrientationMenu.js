import { createContext, useContext, useEffect, useRef } from 'react'
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

export const ChartOrientationContext = createContext({
  orientation: ORIENTATION.VERTICAL,
  isActive: false,
  isOpen: false,
  onToggleMenu: () => {},
  chevronRef: null,
})

export const BarChartToggleIcon = () => {
  const { orientation, isActive, isOpen, onToggleMenu, chevronRef } =
    useContext(ChartOrientationContext)

  const handleClick = (event) => {
    event.preventDefault()
    event.stopPropagation()
    onToggleMenu()
  }

  const handleKeyDown = (event) => {
    if (event.key === 'Enter' || event.key === ' ') {
      handleClick(event)
    }
  }

  return (
    <span className="responses-statistics-bar-toggle-icon">
      <i className={getOrientationIconClassName(orientation)}></i>
      {isActive && (
        <span
          ref={chevronRef}
          role="button"
          tabIndex={0}
          aria-label={t('Chart orientation')}
          aria-haspopup="menu"
          aria-expanded={isOpen}
          className="responses-statistics-bar-toggle-chevron"
          onClick={handleClick}
          onKeyDown={handleKeyDown}
        >
          <i
            className={isOpen ? 'ri-arrow-up-s-line' : 'ri-arrow-down-s-line'}
          ></i>
        </span>
      )}
    </span>
  )
}

export const ChartOrientationMenu = ({
  orientation,
  onSelect,
  onClose,
  containerRef,
  chevronRef,
}) => {
  const menuRef = useRef(null)

  useEffect(() => {
    const handlePointerDown = (event) => {
      if (!containerRef?.current?.contains(event.target)) {
        onClose()
      }
    }
    const handleKeyDown = (event) => {
      if (event.key === 'Escape') {
        onClose()
        chevronRef?.current?.focus()
      }
    }

    document.addEventListener('mousedown', handlePointerDown)
    document.addEventListener('keydown', handleKeyDown)
    menuRef.current?.querySelector('[aria-checked="true"]')?.focus()

    return () => {
      document.removeEventListener('mousedown', handlePointerDown)
      document.removeEventListener('keydown', handleKeyDown)
    }
  }, [])

  const handleSelect = (value) => {
    onSelect(value)
    chevronRef?.current?.focus()
  }

  return (
    <div
      ref={menuRef}
      className="responses-statistics-orientation-menu"
      role="menu"
      aria-label={t('Chart orientation')}
    >
      <div className="responses-statistics-orientation-menu-title">
        {t('Chart orientation')}
      </div>
      {ORIENTATION_OPTIONS.map(({ value, label, iconClassName }) => (
        <button
          key={value}
          type="button"
          role="menuitemradio"
          aria-checked={value === orientation}
          className={classNames('responses-statistics-orientation-menu-item', {
            'responses-statistics-orientation-menu-item--selected':
              value === orientation,
          })}
          onClick={() => handleSelect(value)}
        >
          <i className={iconClassName}></i>
          {label()}
        </button>
      ))}
    </div>
  )
}
