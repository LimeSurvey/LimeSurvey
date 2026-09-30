import { useRef, useEffect } from 'react'

// Only digits and an optional leading minus sign are allowed, mirroring the
// legacy `-?\d+` pattern used for the assessment value input.
const ASSESSMENT_VALUE_PATTERN = /^-?\d*$/

export const AssessmentValueInput = ({ assessmentValue, onChange }) => {
  const inputRef = useRef(null)
  const measureRef = useRef(null)

  useEffect(() => {
    if (!inputRef.current || !measureRef.current) return

    // Copy input value and styling to measure div
    measureRef.current.textContent = assessmentValue || '0'
    
    // Set input width based on measured width, minimum 32px
    const measuredWidth = measureRef.current.offsetWidth
    inputRef.current.style.width = Math.max(32, measuredWidth + 16) + 'px'
  }, [assessmentValue])

  return (
    <div className="assessment-value-container">
      <input
        ref={inputRef}
        className="assessment-value-input"
        type="text"
        inputMode="numeric"
        data-testid="assessment-value-input"
        value={assessmentValue}
        onChange={(e) => {
          if (ASSESSMENT_VALUE_PATTERN.test(e.target.value)) {
            onChange(e)
          }
        }}
      />
      {/* Hidden div for measuring text width */}
      <div
        ref={measureRef}
        className="assessment-value-measure"
        style={{ visibility: 'hidden', position: 'absolute', whiteSpace: 'nowrap' }}
      />
    </div>
  )
}
