import React from 'react'

const InfoIcon = ({ width = '40', height = '40' }) => {
  return (
    <svg
      width={width}
      height={height}
      viewBox="0 0 15 15"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
    >
      <path
        d="M8.00065 14.6673C4.31865 14.6673 1.33398 11.6827 1.33398 8.00065C1.33398 4.31865 4.31865 1.33398 8.00065 1.33398C11.6827 1.33398 14.6673 4.31865 14.6673 8.00065C14.6673 11.6827 11.6827 14.6673 8.00065 14.6673ZM7.33398 7.33398V11.334H8.66732V7.33398H7.33398ZM7.33398 4.66732V6.00065H8.66732V4.66732H7.33398Z"
        fill="#1E1E1E"
      />
    </svg>
  )
}

export default InfoIcon
