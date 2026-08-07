import React from 'react'
import { Card } from 'react-bootstrap'

import { ACCESS_MODES } from 'helpers'
import { PublicSurveyAlias } from 'components'

export const AliasSettingsCard = ({
  survey,
  onAliasChange,
  setLink,
  selectedLanguage,
  aliasHasError,
  update,
  createBufferOperation,
  addToBuffer,
}) => {
  return (
    <div className="col-md-6 d-flex align-items-stretch">
      <Card className="card d-flex flex-column justify-content-between h-100 w-100">
        <PublicSurveyAlias
          survey={survey}
          update={update}
          onAliasChange={onAliasChange}
          setLink={setLink}
          language={selectedLanguage}
          currentSurveyAccessMode={
            survey?.access_mode || ACCESS_MODES.OPEN_TO_ALL
          }
          aliasHasError={aliasHasError}
          createBufferOperation={createBufferOperation}
          addToBuffer={addToBuffer}
        />
      </Card>
    </div>
  )
}
