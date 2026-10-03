import { useRef, useEffect } from 'react'
import { format } from 'util'

// Only digits and an optional leading minus sign are allowed, mirroring the
// legacy `-?\d+` pattern used for the assessment value input.
const ASSESSMENT_VALUE_PATTERN = /^-?\d*$/

const getAccessibleLabel = (answerCode, scaleNumber) =>
  scaleNumber
    ? format(
        t('Assessment value for answer option %s (scale %s)'),
        answerCode,
        scaleNumber
      )
    : format(t('Assessment value for answer option %s'), answerCode)

export const AssessmentValueInput = ({
  assessmentValue,
  onChange,
  answerCode = '',
  scaleNumber,
}) => {
  const inputRef = useRef(null)
  const measureRef = useRef(null)

  useEffect(() => {
    if (!inputRef.current || !measureRef.current) return

    measureRef.current.textContent = assessmentValue || '0'

    // Grow with the content, but never below 32px.
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
        autoComplete="off"
        aria-label={getAccessibleLabel(answerCode, scaleNumber)}
        data-testid="assessment-value-input"
        value={assessmentValue}
        onChange={(e) => {
          if (ASSESSMENT_VALUE_PATTERN.test(e.target.value)) {
            onChange(e)
          }
        }}
      />
      <div
        ref={measureRef}
        aria-hidden="true"
        className="assessment-value-measure"
        style={{
          visibility: 'hidden',
          position: 'absolute',
          whiteSpace: 'nowrap',
        }}
      />
    </div>
  )
}
