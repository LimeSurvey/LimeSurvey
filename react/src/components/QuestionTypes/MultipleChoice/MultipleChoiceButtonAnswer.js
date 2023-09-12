import { Button } from 'react-bootstrap'
import ContentEditable from 'react-contenteditable'

import { RandomNumber } from 'helpers'

export const MultipleChoiceButtonAnswer = ({ answer }) => {
  return (
    <Button
      key={`${answer.assessmentValue.toString()}-${RandomNumber()}`}
      className="me-2"
      variant="outline-secondary"
    >
      <ContentEditable
        disabled={true}
        html={answer.assessmentValue.toString()}
      />
    </Button>
  )
}
