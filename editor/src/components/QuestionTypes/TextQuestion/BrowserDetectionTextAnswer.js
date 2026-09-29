import React, { useEffect, useState } from 'react'
import { Form } from 'react-bootstrap'
import Bowser from 'bowser'

import { ContentEditor } from 'components/UIComponents'

import './TextQuestion.scss'

export const BrowserDetectionTextAnswer = ({ attributes = {} }) => {
  const [browserInfo, setBrowserInfo] = useState('')

  useEffect(() => {
    const { browser, os } = Bowser.parse(window.navigator.userAgent)
    let info = ''
    if (attributes?.add_platform_info?.['']?.value === 'yes') {
      info = `${browser.name} (${browser.version}) | ${os.name} (${os.versionName})`
    } else {
      info = `${browser.name} (${browser.version})`
    }

    setBrowserInfo(info)
  }, [attributes?.add_platform_info])

  return (
    <div className={'question-body-content'}>
      <div className="d-flex gap-2 align-items-center justify-content-center">
        {attributes.prefix?.value && (
          <ContentEditor disabled={true} value={attributes.prefix?.value} />
        )}
        <Form.Group className="flex-grow-1">
          <Form.Control
            type={'text'}
            placeholder={st('Enter your answer here.')}
            data-testid="text-question-answer-input"
            value={browserInfo}
            disabled={true}
          />
        </Form.Group>
        {attributes.suffix && (
          <ContentEditor disabled={true} value={attributes.suffix?.value} />
        )}
      </div>
    </div>
  )
}
