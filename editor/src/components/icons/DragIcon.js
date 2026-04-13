import React from 'react'
export const DragIcon = (props) => {
  return (
    <svg
      {...props}
      style={{
        height: '14px',
        width: '14px',
        ...props.style,
      }}
    >
      <path d="M0.69339 0.125H3.47641V2.625H0.69339V0.125ZM6.25943 0.125H9.04245V2.625H6.25943V0.125ZM0.69339 5.75H3.47641V8.25H0.69339V5.75ZM6.25943 5.75H9.04245V8.25H6.25943V5.75ZM0.69339 11.375H3.47641V13.875H0.69339V11.375ZM6.25943 11.375H9.04245V13.875H6.25943V11.375Z" />
    </svg>
  )
}
