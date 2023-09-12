import React, { useState } from 'react'
import { Form } from 'react-bootstrap'
import { Select } from 'components/UIComponents'
import { ToggleButtons } from 'components/UIComponents/Buttons'
import { LanguageSwitch } from './Attributes/LanguageSwitch'

const themeOptions = [
  {
    label: 'BootsWatch',
    value: 'bootswatch',
  },
  {
    label: 'Fruity',
    value: 'fruity',
  },
  {
    label: 'LS6 SurveyTheme',
    value: 'ls6_surveytheme',
  },
  {
    label: 'Vanilla',
    value: 'vanilla',
  },
]

export const GeneralSettings = ({
  survey,
  language,
  handleUpdate = () => {},
}) => {
  const [themeSetting, setThemeSetting] = useState(themeOptions[0])
  const [cookieEnable, setCoolieEnable] = useState(false)
  const [brandingEnable, setBrandingEnable] = useState(false)

  const handleLanguageChange = (language) => {
    handleUpdate({ language })
  }

  const handleThemeChange = (evt) => {
    const theme = themeOptions.find(
      (option) => option.value === evt.target.value
    )

    setThemeSetting({ ...theme })
  }

  const handleCookieChange = (value) => setCoolieEnable(value)
  const handleBrandingChange = (value) => setBrandingEnable(value)
  // const handleAllowLanguageSwitch = (allowLanguageSwitch) =>
  //   handleUpdate({ allowLanguageSwitch })

  return (
    <div className="mt-5 p-4 bg-white">
      <div className="">
        <LanguageSwitch
          handleLanguageChange={handleLanguageChange}
          language={language}
        />
      </div>
      <div className="mt-3">
        <Select
          labelText="Theme"
          options={themeOptions}
          onChange={handleThemeChange}
          selectedOption={themeSetting}
        />
      </div>
      <div className="mt-3 d-flex align-items-center">
        <div className="w-50">
          <p className="h6 mb-0">Show Question codes in structure</p>
          <Form.Label className="mb-0 text-secondary">
            optional multiline text description
          </Form.Label>
        </div>
        <div className="w-50 ms-2">
          <ToggleButtons
            id="question-code"
            labelText=""
            value={survey.showQuestionCode}
            onChange={(showQuestionCode) => handleUpdate({ showQuestionCode })}
          />
        </div>
      </div>
      <div className="mt-3 d-flex align-items-center">
        <div className="w-50">
          <p className="h6 mb-0">Cookie consent</p>
          <Form.Label className="mb-0 text-secondary">
            optional multiline text description
          </Form.Label>
        </div>
        <div className="w-50 ms-2">
          <ToggleButtons
            id="cookie-consent"
            labelText=""
            value={cookieEnable}
            onChange={handleCookieChange}
          />
        </div>
      </div>
      <div className="mt-3 d-flex align-items-center">
        <div className="w-50">
          <p className="h6 mb-0">LimeSurvey Branding</p>
          <Form.Label className="mb-0 text-secondary">
            optional multiline text description
          </Form.Label>
        </div>
        <div className="w-50 ms-2">
          <ToggleButtons
            id="branding"
            labelText=""
            value={brandingEnable}
            onChange={handleBrandingChange}
          />
        </div>
      </div>
      {/* <div className="mt-3">
        <ToggleButtons
          id="language-switch"
          labelText="Allow language switch"
          value={survey.allowLanguageSwitch}
          onChange={handleAllowLanguageSwitch}
        />
      </div> */}
    </div>
  )
}
