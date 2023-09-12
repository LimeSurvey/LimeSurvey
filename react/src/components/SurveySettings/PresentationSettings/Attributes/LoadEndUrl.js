import { ToggleButtons } from 'components/UIComponents'
import { useState } from 'react'
import { Form } from 'react-bootstrap'

export const LoadEndUrl = () => {
  const [loadUrl, setLoadUrl] = useState(false)

  return (
    <div className="mt-3 d-flex align-items-center">
      <div className="w-50">
        <p className="h6 mb-0">
          Automatically load end URL when survey complete
        </p>
        <Form.Label className="mb-0 text-secondary">
          optional multiline text description
        </Form.Label>
      </div>
      <div className="w-50 ms-2">
        <ToggleButtons
          id="load-end-url"
          labelText=""
          value={loadUrl}
          onChange={(value) => setLoadUrl(value)}
        />
      </div>
    </div>
  )
}
