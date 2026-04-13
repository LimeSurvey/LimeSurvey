import React from 'react'

export const ExpandIcon = ({
  width = 16,
  height = 16,
  stroke = '#6c757d',
  strokeWidth = 2,
  className = '',
}) => {
  return (
    <svg
      xmlns="http://www.w3.org/2000/svg"
      width={width}
      height={height}
      fill="none"
      stroke={stroke}
      strokeWidth={strokeWidth}
      strokeLinecap="round"
      viewBox="0 0 16 16"
      className={className}
    >
      <path d="M3 3h3M3 3v3M13 3h-3M13 3v3M3 13h3M3 13v-3M13 13h-3M13 13v-3" />
    </svg>
  )
}
