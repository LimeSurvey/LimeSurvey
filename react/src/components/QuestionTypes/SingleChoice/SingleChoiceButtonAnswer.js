import React from 'react'
import { Button } from 'react-bootstrap'
import ContentEditable from 'react-contenteditable'

export const SingleChoiceButtonAnswer = ({ answer = {} }) => {
  return (
    <Button variant="outline-secondary">
      <ContentEditable disabled={true} html={answer.assessmentValue} />
    </Button>
  )
}
