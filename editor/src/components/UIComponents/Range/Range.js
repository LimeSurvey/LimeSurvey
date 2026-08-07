import React, { useState } from 'react'
import { Range as ReactRange, getTrackBackground } from 'react-range'

export const Range = () => {
  const [values, setValues] = useState([50])
  const STEP = 0.1
  const MIN = 0
  const MAX = 100

  return (
    <div data-testid="range">
      <ReactRange
        values={values}
        step={STEP}
        min={MIN}
        max={MAX}
        onChange={(values) => setValues([...values])}
        renderTrack={({ props, children }) => (
          <div
            onMouseDown={props.onMouseDown}
            onTouchStart={props.onTouchStart}
            style={{
              ...props.style,
              height: '4px',
              display: 'flex',
              width: '100%',
            }}
          >
            <div
              ref={props.ref}
              style={{
                height: '4px',
                width: '100%',
                borderRadius: '2px',
                background: getTrackBackground({
                  values: values,
                  colors: ['#8146F6', '#DDDEE8'],
                  min: MIN,
                  max: MAX,
                }),
                alignSelf: 'center',
              }}
            >
              {children}
            </div>
          </div>
        )}
        renderThumb={({ props, isDragged }) => (
          <div
            {...props}
            style={{
              ...props.style,
              height: '12px',
              width: '12px',
              borderRadius: '50%',
              backgroundColor: '#8146F6',
              display: 'flex',
              justifyContent: 'center',
              alignItems: 'center',
              boxShadow: isDragged
                ? '0 0 0 1.5px #fff, 0 0 0 .3rem rgba(129, 70, 246, 0.25)'
                : 'none',
            }}
          >
            {isDragged && (
              <div
                data-testid="range-value"
                style={{
                  position: 'absolute',
                  top: '20px',
                  color: '#fff',
                  fontWeight: '400',
                  fontSize: '11px',
                  padding: '3px 6px',
                  borderRadius: '2px',
                  backgroundColor: '#1E1E1E',
                }}
              >
                {values[0].toFixed(1)}
              </div>
            )}
          </div>
        )}
      />
    </div>
  )
}
