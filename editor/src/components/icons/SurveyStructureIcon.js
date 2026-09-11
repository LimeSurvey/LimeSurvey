import React from 'react'

export const SurveyStructureIcon = (props) => {
  return (
    <svg
      xmlns="http://www.w3.org/2000/svg"
      width="36"
      height="36"
      fill="none"
      className={props.className}
    >
      <g clipPath="url(#bg)">
        <rect width="36" height="36" fill={props.bgcolor} rx="4" />
        <g clipPath="url(#b)">
          <path d="M18 10.5v1.667h-7.5V10.5H18Zm3.333 13.333V25.5H10.5v-1.667h10.833Zm5-6.666v1.666H10.5v-1.666h15.833Z" />
        </g>
      </g>
      <defs>
        <clipPath id="a">
          <rect width="36" height="36" rx="4" />
        </clipPath>
        <clipPath id="b">
          <path d="M8 8h20v20H8z" />
        </clipPath>
      </defs>
    </svg>
  )
}
