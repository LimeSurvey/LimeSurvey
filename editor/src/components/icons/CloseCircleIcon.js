import React from 'react'

export const CloseCircleIcon = ({
  width = 16,
  height = 16,
  color = '#6c757d',
  strokeWidth = 2,
  className = '',
}) => {
  return (
    <svg
      xmlns="http://www.w3.org/2000/svg"
      width={width}
      height={height}
      viewBox="0 0 16 16"
      fill="none"
      stroke={color}
      strokeWidth={strokeWidth}
      strokeLinecap="round"
      className={className}
    >
      <circle cx="8" cy="8" r="7" />
      <line x1="5" y1="5" x2="11" y2="11" />
      <line x1="11" y1="5" x2="5" y2="11" />
    </svg>
  )
}
