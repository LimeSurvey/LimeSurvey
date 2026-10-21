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
          <path d="M19.2277 17.7321L20.9955 15.9644L22.7632 17.7321C23.7395 18.7084 23.7395 20.2914 22.7632 21.2677C21.7869 22.244 20.204 22.244 19.2277 21.2677C18.2514 20.2914 18.2514 18.7084 19.2277 17.7321ZM8.87861 0.0795898L20.1923 11.3933C20.5828 11.7838 20.5828 12.417 20.1923 12.8075L11.707 21.2928C11.3165 21.6833 10.6833 21.6833 10.2928 21.2928L1.80754 12.8075C1.41702 12.417 1.41702 11.7838 1.80754 11.3933L9.58572 3.61512L7.4644 1.4938L8.87861 0.0795898ZM10.9999 5.02934L3.92886 12.1004H18.071L10.9999 5.02934Z" />
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
