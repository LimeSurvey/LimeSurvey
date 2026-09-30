import { Badge } from 'react-bootstrap'

// Label badge shown above the answer option rows, right-aligned above
// the AssessmentValueInput column. Clicking the badge focuses the first
// assessment value input below it (standard label behavior).
export const AssessmentValueBadge = ({ onClick }) => (
  <Badge
    bg="white"
    className="assessment-value-badge"
    data-testid="assessment-value-badge"
    onClick={onClick}
    style={{ cursor: 'pointer' }}
  >
    {t('Assessment value')}
  </Badge>
)
