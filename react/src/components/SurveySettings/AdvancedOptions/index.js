import React from 'react'
import { Form } from 'react-bootstrap'
import Button from 'react-bootstrap/Button'
import { ExternalLinkIcon } from 'components/icons'

export const AdvancedOptionsSettings = () => {
  return (
    <div className="mt-5 p-4 bg-white">
      <Form.Label>
        Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy
        eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam
        voluptua.
      </Form.Label>
      <Button variant="secondary" className="d-flex align-items-center">
        <span className="me-1">Go to advanced options</span>
        <ExternalLinkIcon className="text-white fill-current" />
      </Button>
    </div>
  )
}
