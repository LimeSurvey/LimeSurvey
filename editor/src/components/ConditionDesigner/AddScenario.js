import React from 'react'

import { Button } from 'components'
import { AddIcon } from 'components/icons'

export const AddScenario = ({ onShowPanel }) => {
  return (
    <>
      <div className="mb-3">
        <hr className="mb-3" />
        <div>
          <p className="mb-3 fw-bolder">{t('Condition designer')}</p>
          <div className="d-flex align-items-center">
            <Button
              variant="secondary"
              className="add-condition-button d-flex align-items-center justify-content-center"
              onClick={() => onShowPanel(null, true)}
            >
              <AddIcon className="text-white fill-current" />
            </Button>
            <span className="d-inline fw-bolder">{t('New logic')}</span>
          </div>
        </div>
      </div>
    </>
  )
}
