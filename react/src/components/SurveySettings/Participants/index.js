import React, { useState } from 'react'
import { Form } from 'react-bootstrap'
import { ToggleButtons } from 'components/UIComponents'

export const ParticipantsSettings = () => {
  const [showQuestion, setShowQuestion] = useState('0')
  const [allowBackward, setAllowBackward] = useState('0')
  const handleShowQuestion = (value) => setShowQuestion(value)
  const handleAllowBackward = (value) => setAllowBackward(value)
  return (
    <div className="mt-5 p-4 bg-white">
      <div className="mt-3 d-flex align-items-center">
        <div className="w-50">
          <p className="h6 mb-0">Anonymized responses</p>
          <Form.Label className="mb-0 text-secondary">
            optional multiline text description
          </Form.Label>
        </div>
        <div className="w-50 ms-2">
          <ToggleButtons
            id="anonymized-response"
            labelText=""
            value={showQuestion}
            onChange={handleShowQuestion}
          />
        </div>
      </div>
      <div className="mt-3 d-flex align-items-center">
        <div className="w-50">
          <p className="h6 mb-0">
            Allow multiple responses or update responses with one access code
          </p>
          <Form.Label className="mb-0 text-secondary">
            optional multiline text description
          </Form.Label>
        </div>
        <div className="w-50 ms-2">
          <ToggleButtons
            id="multiple-response"
            labelText=""
            value={allowBackward}
            onChange={handleAllowBackward}
          />
        </div>
      </div>
    </div>
  )
}
