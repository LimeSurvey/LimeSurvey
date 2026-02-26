import React from 'react'
import BootstrapButton from 'react-bootstrap/Button'
import ContentEditable from 'react-contenteditable'

export const Button = React.forwardRef(function Button(
  {
    onClick = () => {},
    id = '',
    text,
    name = '',
    Icon,
    iconSize = 24,
    children,
    className = '',
    style = {},
    disabled = false,
    variant = 'success',
    testId = 'button',
    value = '',
    href = null,
    active = false,
    size = '',
  },
  ref
) {
  return (
    <BootstrapButton
      name={name || id}
      variant={variant}
      onClick={onClick}
      style={{ ...style }}
      className={`button ${className}`}
      id={id}
      disabled={disabled}
      ref={ref}
      data-testid={testId}
      value={value}
      href={href}
      active={active}
      size={size}
    >
      {Icon && <div className={`button-icon `}>{<Icon size={iconSize} />}</div>}
      {text && (
        <ContentEditable className="button-text" disabled={true} html={text} />
      )}
      {children}
    </BootstrapButton>
  )
})
