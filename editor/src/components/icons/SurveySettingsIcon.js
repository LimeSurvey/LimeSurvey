import React from 'react'

export const SurveySettingsIcon = (props) => {
  return (
    <svg
      xmlns="http://www.w3.org/2000/svg"
      width="36"
      height="36"
      fill="none"
      className={props.className}
      {...props}
    >
      <g clipPath="url(#bg)">
        <rect width="36" height="36" fill={props.bgcolor} rx="4" />
        <g clipPath="url(#b)">
          <path d="M16.295 9.842a8.324 8.324 0 0 1 3.409-.002c.148.966.717 1.86 1.629 2.386.912.527 1.97.573 2.88.218a8.325 8.325 0 0 1 1.703 2.953A3.327 3.327 0 0 0 24.666 18c0 1.054.489 1.993 1.252 2.604a8.37 8.37 0 0 1-1.705 2.952 3.328 3.328 0 0 0-2.88.218 3.328 3.328 0 0 0-1.629 2.384 8.325 8.325 0 0 1-3.409.002 3.327 3.327 0 0 0-1.629-2.386 3.327 3.327 0 0 0-2.88-.218 8.326 8.326 0 0 1-1.703-2.953 3.327 3.327 0 0 0 1.25-2.603 3.327 3.327 0 0 0-1.251-2.603 8.365 8.365 0 0 1 1.704-2.952c.91.355 1.968.308 2.88-.218a3.327 3.327 0 0 0 1.629-2.384ZM18 20.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z" />
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
