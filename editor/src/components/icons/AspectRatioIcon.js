import React from 'react'

/**
 * Icon of the aspect ratio lock: A frame with two corner marks.
 *
 * @param {number} width width in px
 * @param {number} height height in px
 * @param {string} className classes, the icon takes the text color
 * @returns {JSX.Element} the icon, hidden from screen readers
 */
export const AspectRatioIcon = ({
  width = 20,
  height = 18,
  className = '',
}) => {
  return (
    <svg
      xmlns="http://www.w3.org/2000/svg"
      width={width}
      height={height}
      viewBox="0 0 20 18"
      fill="none"
      className={className}
      aria-hidden="true"
      focusable="false"
    >
      <path
        d="M19 0C19.5523 0 20 0.44772 20 1V17C20 17.5523 19.5523 18 19 18H1C0.44772 18 0 17.5523 0 17V1C0 0.44772 0.44772 0 1 0H19ZM18 2H2V16H18V2ZM11 14V12H14V9H16V14H11ZM9 4V6H6V9H4V4H9Z"
        fill="currentColor"
      />
    </svg>
  )
}
