import React from 'react'

export const SurveyMenuIcon = (props) => {
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
        <g clipPath="url(#icon)" transform="translate(6, 6)">
          <path d="M21 8V20.993C21.0009 21.1243 20.976 21.2545 20.9266 21.3762C20.8772 21.4979 20.8043 21.6087 20.7121 21.7022C20.6199 21.7957 20.5101 21.8701 20.3892 21.9212C20.2682 21.9723 20.1383 21.9991 20.007 22H3.993C3.72981 22 3.47739 21.8955 3.2912 21.7095C3.105 21.5235 3.00027 21.2712 3 21.008V2.992C3 2.455 3.449 2 4.002 2H14.997L21 8ZM19 9H14V4H5V20H19V9ZM8 7H11V9H8V7ZM8 11H16V13H8V11ZM8 15H16V17H8V15Z" />
        </g>
      </g>
      <defs>
        <clipPath id="bg">
          <rect width="36" height="36" />
        </clipPath>
        <clipPath id="icon">
          <rect width="24" height="24" />
        </clipPath>
      </defs>
    </svg>
  )
}
