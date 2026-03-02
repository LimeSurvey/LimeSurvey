import React from 'react'

export const SurveyTranslationIcon = (props) => {
  return (
    <svg
      xmlns="http://www.w3.org/2000/svg"
      width="36"
      height="36"
      fill="none"
      className={props.className}
      {...props}
    >
      <g clipPath="url(#bg_translation)">
        <rect width="36" height="36" fill={props.bgcolor} rx="4" />
        <g clipPath="url(#icon_translation)" transform="translate(6, 6)">
          <path d="M17.5 10L21.9 21H19.745L18.544 18H14.454L13.255 21H11.101L15.5 10H17.5ZM9 2V4H15V6L13.0322 6.0006C12.2425 8.36616 10.9988 10.5057 9.4115 12.301C10.1344 12.9457 10.917 13.5176 11.7475 14.0079L10.9969 15.8855C9.9237 15.2781 8.91944 14.5524 7.99961 13.7249C6.21403 15.332 4.10914 16.5553 1.79891 17.2734L1.26257 15.3442C3.2385 14.7203 5.04543 13.6737 6.59042 12.3021C5.46277 11.0281 4.50873 9.57985 3.76742 8.00028L6.00684 8.00037C6.57018 9.03885 7.23979 10.0033 7.99967 10.877C9.2283 9.46508 10.2205 7.81616 10.9095 6.00101L1 6V4H7V2H9ZM16.5 12.8852L15.253 16H17.745L16.5 12.8852Z" />
        </g>
      </g>
      <defs>
        <clipPath id="bg_translation">
          <rect width="36" height="36" />
        </clipPath>
        <clipPath id="icon_translation">
          <rect width="24" height="24" />
        </clipPath>
      </defs>
    </svg>
  )
}
