import { ToggleButtons } from 'components/UIComponents'
import { useState } from 'react'
import { Form } from 'react-bootstrap'

export const ShowProgressBar = () => {
  const [showProgressBar, setShowProgressBar] = useState(false)

  return (
    <div className="mt-3 d-flex align-items-center">
      <div className="w-50">
        <p className="h6 mb-0">Show progress bar</p>
        <Form.Label className="mb-0 text-secondary">
          optional multiline text description
        </Form.Label>
      </div>
      <div className="w-50">
        <ToggleButtons
          id="show-progress-bar"
          labelText=""
          value={showProgressBar}
          onChange={(value) => setShowProgressBar(value)}
        />
      </div>
    </div>
  )
}
