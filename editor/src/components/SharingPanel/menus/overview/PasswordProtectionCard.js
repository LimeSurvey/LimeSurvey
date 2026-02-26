import React from 'react'
import { Card, OverlayTrigger, Tooltip } from 'react-bootstrap'
import { PlusLg } from 'react-bootstrap-icons'

export const PasswordProtectionCard = () => {
  return (
    <div className="col-md-3 d-flex align-items-stretch opacity-50">
      <OverlayTrigger
        placement="top"
        delay={{ show: 100, hide: 100 }}
        offset={[0, 10]}
        overlay={<Tooltip>{t('Coming soon')}</Tooltip>}
      >
        <Card className="card h-100 w-100 position-relative">
          <h5 className="med16-c">{t('Password protected survey')}</h5>
          <p className="reg14 mb-5">{t('One survey link, one password.')}</p>
          <span className="text-primary med14-c">
            <PlusLg className={'icon-align'} />
            {t('Create password')}
          </span>
        </Card>
      </OverlayTrigger>
    </div>
  )
}
