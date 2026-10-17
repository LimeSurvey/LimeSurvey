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
    const handleFocusOut = (event) => {
      if (
        event.relatedTarget &&
        !containerRef?.current?.contains(event.relatedTarget)
      ) {
        onClose()
      }
    }
    const container = containerRef?.current

    document.addEventListener('mousedown', handlePointerDown)
    document.addEventListener('keydown', handleKeyDown)
    container?.addEventListener('focusout', handleFocusOut)
    menuRef.current?.querySelector('[aria-checked="true"]')?.focus()

    return () => {
      document.removeEventListener('mousedown', handlePointerDown)
      document.removeEventListener('keydown', handleKeyDown)
      container?.removeEventListener('focusout', handleFocusOut)
    }
  }, [])

  const handleSelect = (value) => {
    onSelect(value)
    chevronRef?.current?.focus()
  }

  const handleMenuKeyDown = (event) => {
    const items = [
      ...(menuRef.current?.querySelectorAll('[role="menuitemradio"]') ?? []),
    ]
    const index = items.indexOf(document.activeElement)
    const focusItem = (nextIndex) =>
      items[(nextIndex + items.length) % items.length]?.focus()

    switch (event.key) {
      case 'ArrowDown':
        event.preventDefault()
        focusItem(index < 0 ? 0 : index + 1)
        break
      case 'ArrowUp':
        event.preventDefault()
        focusItem(index < 0 ? items.length - 1 : index - 1)
        break
      case 'Home':
        event.preventDefault()
        focusItem(0)
        break
      case 'End':
        event.preventDefault()
        focusItem(items.length - 1)
        break
      case 'Tab':
        chevronRef?.current?.focus()
        onClose()
        break
      default:
        break
    }
  }

  return (
    <div
      ref={menuRef}
      className="responses-statistics-orientation-menu"
      role="menu"
      aria-label={t('Chart orientation')}
      onKeyDown={handleMenuKeyDown}
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
          tabIndex={-1}
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
