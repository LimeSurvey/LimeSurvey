import React from 'react'
import { Card } from 'react-bootstrap'

import { Select } from 'components/UIComponents'

export const LanguageSelector = ({
  selectOptions,
  selectedLanguage,
  setSelectedLanguage,
}) => {
  return (
    <div className="col-12 d-flex align-items-stretch">
      <Card className="card h-100 w-100">
        <h5 className="med16-c">{t('Survey language version')}</h5>
        <div className="d-flex justify-content-between align-items-center">
          <p className="reg14">
            {t('Select the language version of the survey you want to share.')}
          </p>
          <Select
            options={selectOptions}
            value={selectedLanguage}
            className="sharing-panel-select med14-c"
            onChange={(option) => setSelectedLanguage(option.value)}
          />
        </div>
      </Card>
    </div>
  )
}
