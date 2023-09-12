import { useEffect, useState } from 'react'
import { Form } from 'react-bootstrap'
import Bowser from 'bowser'

import { MINIMUM_INPUT_WIDTH_PERCENT } from 'helpers'
import { ContentEditor } from 'components/UIComponents'

import './TextQuestion.scss'

export const BrowserDetectionTextAnswer = ({ attributes = {} }) => {
  const [browserInfo, setBrowserInfo] = useState(false)

  useEffect(() => {
    const { browser, os } = Bowser.parse(window.navigator.userAgent)

    setBrowserInfo(
      `${browser.name} (${browser.version}) | ${os.name} (${os.versionName})`
    )
  }, [])

  return (
    <div
      style={{
        width:
          Math.max(
            attributes.text_input_width?.value,
            MINIMUM_INPUT_WIDTH_PERCENT
          ) + '%' || '100%',
      }}
      className={'question-body-content'}
    >
      <div className="d-flex gap-2 align-items-center justify-content-center">
        {attributes.prefix?.value && (
          <ContentEditor disabled={true} value={attributes.prefix?.value} />
        )}
        <Form.Group className="flex-grow-1">
          <Form.Control
            type={'text'}
            placeholder="Enter your answer here."
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
