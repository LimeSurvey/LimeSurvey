import React from 'react'

export const SurveyPresentationIcon = (props) => {
  return (
    <svg
      xmlns="http://www.w3.org/2000/svg"
      width="36"
      height="36"
      fill="none"
      className={props.className}
      {...props}
    >
      <g clipPath="url(#bg_presentation)">
        <rect width="36" height="36" fill={props.bgcolor} rx="4" />
        <g clipPath="url(#icon_presentation)" transform="translate(6, 6)">
          <path d="M18.2277 17.7321L19.9955 15.9644L21.7632 17.7321C22.7395 18.7084 22.7395 20.2914 21.7632 21.2677C20.7869 22.244 19.204 22.244 18.2277 21.2677C17.2514 20.2914 17.2514 18.7084 18.2277 17.7321ZM7.87861 0.0795898L19.1923 11.3933C19.5828 11.7838 19.5828 12.417 19.1923 12.8075L10.707 21.2928C10.3165 21.6833 9.6833 21.6833 9.2928 21.2928L0.807538 12.8075C0.417018 12.417 0.417018 11.7838 0.807538 11.3933L8.58572 3.61512L6.4644 1.4938L7.87861 0.0795898ZM9.9999 5.02934L2.92886 12.1004H17.071L9.9999 5.02934Z" />
        </g>
      </g>
      <defs>
        <clipPath id="bg_presentation">
          <rect width="36" height="36" />
        </clipPath>
        <clipPath id="icon_presentation">
          <rect width="24" height="24" />
        </clipPath>
      </defs>
    </svg>
  )
}
