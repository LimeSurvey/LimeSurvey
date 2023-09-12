import { ToggleButtons } from 'components/UIComponents'
import { useState } from 'react'
import { Form } from 'react-bootstrap'

export const BackwardNavigation = () => {
  const [allowBackward, setAllowBackward] = useState(false)

  return (
    <div className="mt-3 d-flex align-items-center">
      <div className="w-50">
        <p className="h6 mb-0">Allow backward navigation</p>
        <Form.Label className="mb-0 text-secondary">
          optional multiline text description
        </Form.Label>
      </div>
      <div className="w-50 ms-2">
        <ToggleButtons
          id="backward-navigation"
          labelText=""
          value={allowBackward}
          onChange={(value) => setAllowBackward(value)}
        />
      </div>
    </div>
  )
}
