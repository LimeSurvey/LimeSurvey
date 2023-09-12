import React from 'react'
import Form from 'react-bootstrap/Form'
import Button from 'react-bootstrap/Button'

export const SettingsForm = () => {
  return (
    <Form>
      <Form.Group className="mb-3">
        <Form.Label>Site Name</Form.Label>
        <Form.Control
          type="site.name"
          defaultValue={'LimeSurvey'}
          onChange={(e) => null}
        />
        <Form.Text className="text-muted">
          What is the name of your site?
        </Form.Text>
      </Form.Group>

      <Button variant="primary" type="button" onClick={() => null}>
        Save
      </Button>
    </Form>
  )
}
