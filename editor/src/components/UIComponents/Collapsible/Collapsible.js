import { useState } from 'react'
import classNames from 'classnames'
import { Collapse } from 'react-bootstrap'

export const Collapsible = ({
  text,
  children,
  defaultOpen = true,
  open: openProp,
  onToggle,
  className,
  unmountOnExit = false,
}) => {
  const isControlled = openProp !== undefined
  const [internalOpen, setInternalOpen] = useState(defaultOpen)
  const isOpen = isControlled ? openProp : internalOpen

  const handleToggle = () => {
    const next = !isOpen
    if (!isControlled) setInternalOpen(next)
    if (onToggle) onToggle(next)
  }

  return (
    <div className={classNames('collapsible', className)}>
      {text !== undefined && (
        <button
          type="button"
          className="collapsible-header"
          onClick={handleToggle}
          aria-expanded={isOpen}
        >
          <i
            className={classNames(
              'collapsible-caret',
              isOpen ? 'ri-arrow-down-s-line' : 'ri-arrow-right-s-line'
            )}
          />
          <span className="collapsible-text">{text}</span>
        </button>
      )}
      <Collapse in={isOpen} unmountOnExit={unmountOnExit}>
        <div className="collapsible-body">{children}</div>
      </Collapse>
    </div>
  )
}
