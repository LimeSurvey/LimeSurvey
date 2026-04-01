import React from 'react'

export const ArrowLeftIcon = (props) => {
  const { width = 20, height = 20, className = '' } = props

  return (
    <svg
      width={width}
      height={height}
      viewBox="0 0 48 48"
      xmlns="http://www.w3.org/2000/svg"
      className={`fill-current ${className}`}
    >
      <path d="M0 0h48v48H0z" fill="none" />
      <path d="M40 22H15.66l11.17-11.17-2.83-2.83-16 16 16 16 2.83-2.83-11.17-11.17H40v-4z" />
    </svg>
  )
}
