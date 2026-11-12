import { useRef, useEffect } from 'react'
import { format } from 'util'

// Only digits and an optional leading minus sign are allowed, mirroring the
// legacy `-?\d+` pattern used for the assessment value input.
const ASSESSMENT_VALUE_PATTERN = /^-?\d*$/

// Same limit as the legacy editor (maxlength='5', minus sign included).
export const ASSESSMENT_VALUE_MAX_LENGTH = 5

const isIncompleteValue = (value) => String(value ?? '').trim() === '-'

const isAllowedInput = (value) =>
  value.length <= ASSESSMENT_VALUE_MAX_LENGTH &&
  ASSESSMENT_VALUE_PATTERN.test(value)

/**
 * Bring a typed value into the form the backend stores: empty becomes 0,
 * leading zeros and "-0" are dropped. A lone minus has no numeric meaning and
 * falls back to the given value.
 */
export const normalizeAssessmentValue = (value, fallback = '0') => {
  const stringValue = String(value ?? '').trim()

  if (stringValue === '') {
    return '0'
  }

  if (isIncompleteValue(stringValue)) {
    return fallback
  }

  const parsed = parseInt(stringValue, 10)
  return Number.isNaN(parsed) ? fallback : String(parsed)
}

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
  const displayValue = assessmentValue ?? '0'
  const lastValidValueRef = useRef(normalizeAssessmentValue(assessmentValue))

  useEffect(() => {
    if (!isIncompleteValue(assessmentValue)) {
      lastValidValueRef.current = normalizeAssessmentValue(assessmentValue)
    }
  }, [assessmentValue])

  // While typing, intermediate values like "" or "-" are allowed. Once the
  // user leaves the field, replace them with what is actually persisted so the
  // UI does not differ from the saved value.
  const handleBlur = () => {
    if (!onChange) return

    const normalizedValue = normalizeAssessmentValue(
      assessmentValue,
      lastValidValueRef.current
    )

    if (normalizedValue !== String(assessmentValue ?? '')) {
      onChange({ target: { value: normalizedValue } })
    }
  }

  useEffect(() => {
    if (!inputRef.current || !measureRef.current) return

    measureRef.current.textContent = displayValue || '0'

    // Grow with the content, but never below 32px.
    const measuredWidth = measureRef.current.offsetWidth
    inputRef.current.style.width = Math.max(32, measuredWidth + 16) + 'px'
  }, [displayValue])

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
        value={displayValue}
        maxLength={ASSESSMENT_VALUE_MAX_LENGTH}
        onChange={(e) => {
          if (isAllowedInput(e.target.value)) {
            onChange(e)
          }
        }}
        onBlur={handleBlur}
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
