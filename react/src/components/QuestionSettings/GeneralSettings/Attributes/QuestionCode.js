import React from 'react'
import { Form } from 'react-bootstrap'

export const QuestionCode = ({ value, onChange }) => {
  return (
    <div className="d-flex justify-content-between align-items-center gap-1">
      <Form.Label className="m-0">Question Code</Form.Label>
      <div className="w-50">
        <Form.Control
          onChange={(event) => onChange(event.target.value)}
          value={value}
          onKeyDown={(event) => {}}
          className="question-code w-100"
        />
      </div>
    </div>
  )
}
