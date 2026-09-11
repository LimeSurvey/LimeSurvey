import React from 'react'

const SmallCheckMarkIcon = ({ classname }) => {
  return (
    <svg
      className={classname}
      width="16"
      height="16"
      viewBox="0 0 16 16"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
    >
      <g clipPath="url(#clip0_9045_7635)">
        <path
          d="M7.9987 14.6673C4.3167 14.6673 1.33203 11.6827 1.33203 8.00065C1.33203 4.31865 4.3167 1.33398 7.9987 1.33398C11.6807 1.33398 14.6654 4.31865 14.6654 8.00065C14.6654 11.6827 11.6807 14.6673 7.9987 14.6673ZM7.33403 10.6673L12.0474 5.95332L11.1047 5.01065L7.33403 8.78198L5.44803 6.89598L4.50536 7.83865L7.33403 10.6673Z"
          fill="#1E1E1E"
        />
      </g>
      <defs>
        <clipPath id="clip0_9045_7635">
          <rect width="16" height="16" fill="white" />
        </clipPath>
      </defs>
    </svg>
  )
}

export default SmallCheckMarkIcon
