import { useRef } from 'react'
import { Badge } from 'react-bootstrap'

export const AssessmentValueBadge = () => {
  const nextInputIndex = useRef(0)

  const focusNextAssessmentValueInput = (event) => {
    const inputs = event.currentTarget
      .closest('.question')
      ?.querySelectorAll('input.assessment-value-input')

    if (!inputs?.length) return

    inputs[nextInputIndex.current % inputs.length].focus()
    nextInputIndex.current = (nextInputIndex.current + 1) % inputs.length
  }

  return (
    <Badge
      as="button"
      type="button"
      // Empty bg prevents Bootstrap's !important bg-* class from overriding
      // the SCSS background (incl. :hover).
      bg=""
      className="assessment-value-badge"
      data-testid="assessment-value-badge"
      onClick={focusNextAssessmentValueInput}
    >
      {t('Assessment value')}
    </Badge>
  )
}
