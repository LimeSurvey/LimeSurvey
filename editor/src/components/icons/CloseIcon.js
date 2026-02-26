import React from 'react'
export const CloseIcon = ({ className, width = 20, height = 20 }) => {
  return (
    <svg
      width={width}
      height={height}
      viewBox="0 0 24 24"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
      className={className}
    >
      <g clipPath="url(#clip0_1_4259)">
        <path d="M12.0007 10.586L16.9507 5.63599L18.3647 7.04999L13.4147 12L18.3647 16.95L16.9507 18.364L12.0007 13.414L7.05072 18.364L5.63672 16.95L10.5867 12L5.63672 7.04999L7.05072 5.63599L12.0007 10.586Z" />
      </g>
      <defs>
        <clipPath id="clip0_1_4259">
          <rect width="20" height="20" fill="white" />
        </clipPath>
      </defs>
    </svg>
  )
}
