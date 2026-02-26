import React from 'react'
import { Form } from 'react-bootstrap'
import Button from 'react-bootstrap/Button'
import { ExternalLinkIcon } from 'components/icons'

import getSiteUrl from '../../../helpers/getSiteUrl'

export const AdvancedOptionsSettings = ({ surveyId }) => {
  const openSurveySettingsInMainApp = () => {
    window.open(
      getSiteUrl('/surveyAdministration/view/surveyid/' + surveyId),
      '_blank'
    )
  }
  return (
    <>
      <Form.Label>
        {t(
          'Explore additional configuration options, more detailed features and settings.'
        )}
      </Form.Label>
      <Button
        variant="secondary"
        className="d-flex align-items-center"
        onClick={() => {
          openSurveySettingsInMainApp()
        }}
      >
        <span className="me-1">{t('Go to advanced options')}</span>
        <ExternalLinkIcon className="text-white fill-current" />
      </Button>
    </>
  )
}
