import React from 'react'
import { Form } from 'react-bootstrap'

import { Input, ContentEditor, ToggleButtons } from 'components/UIComponents'

export const PrivacyPolicySettings = ({
  survey: {
    showLegalNotice,
    showPrivacyPolicy,
    legalNoticeMessage,
    privacyPolicyMessage,
    privacyPolicyErrMessage,
    privacyPolicyLabelMessage,
  },
  handleUpdate,
}) => {
  const handleShowPrivacyPolicy = (value) =>
    handleUpdate({ showPrivacyPolicy: value })
  const handleShowLegalNotice = (value) =>
    handleUpdate({ showLegalNotice: value })

  return (
    <div className="mt-5 p-4 bg-white">
      <div className="mt-3 d-flex align-items-center">
        <div className="w-50">
          <p className="h6 mb-0">Show legal notice in survey</p>
          <Form.Label className="mb-0 text-secondary">
            optional multiline text description
          </Form.Label>
        </div>
        <div className="w-50 ms-2">
          <ToggleButtons
            id="legal-notice"
            labelText="Show legal notice in survey"
            value={showLegalNotice}
            onChange={handleShowLegalNotice}
          />
        </div>
      </div>

      <div>
        {showLegalNotice && (
          <div className="mt-3 d-flex">
            <div className="w-50">
              <Form.Label>Legal Notice Message</Form.Label>
            </div>
            <div className="privacy-policy w-50">
              <ContentEditor
                value={legalNoticeMessage}
                update={(value) => handleUpdate({ legalNoticeMessage: value })}
                placeholder="Legal Notice Message"
                style={{ width: '100%' }}
              />
            </div>
          </div>
        )}
      </div>
      <div className="mt-3 d-flex align-items-center">
        <div className="w-50">
          <p className="h6 mb-0">Show data policy in survey</p>
          <Form.Label className="mb-0 text-secondary">
            optional multiline text description
          </Form.Label>
        </div>
        <div className="w-50 ms-2">
          <ToggleButtons
            id="privacy-policy"
            labelText="Show data policy in survey"
            value={showPrivacyPolicy}
            onChange={handleShowPrivacyPolicy}
          />
        </div>
      </div>

      {showPrivacyPolicy && (
        <React.Fragment>
          <div className="mt-3">
            <Form.Label>Privacy policy message</Form.Label>
            <div className="privacy-policy">
              <ContentEditor
                value={privacyPolicyMessage}
                update={(value) =>
                  handleUpdate({ privacyPolicyMessage: value })
                }
                placeholder="Privacy Policy Message"
              />
            </div>
          </div>
          <div className="mt-3">
            <Form.Label>Privacy policy error message</Form.Label>
            <Form.Control
              placeholder="Privacy policy error message"
              as="textarea"
              rows={6}
              data-testid="text-question-answer-input"
              value={privacyPolicyErrMessage}
              onChange={({ target: { value } }) =>
                handleUpdate({ privacyPolicyErrMessage: value })
              }
            />
          </div>
          <div className="mt-3">
            <Form.Label>Label (Text for the Checkbox)</Form.Label>
            <Input
              value={privacyPolicyLabelMessage}
              onChange={({ target: { value } }) =>
                handleUpdate({ privacyPolicyLabelMessage: value })
              }
            />
          </div>
        </React.Fragment>
      )}
    </div>
  )
}
