import React from 'react'
import classNames from 'classnames'
import { Card } from 'react-bootstrap'
import { PlusLg } from 'react-bootstrap-icons'

import { getSiteUrl } from 'helpers'
import { TooltipContainer } from 'components'

export const ParticipantsListCard = ({ hasUpdatePermission, surveyId }) => {
  const handleParticipantListNav = () => {
    if (hasUpdatePermission) {
      window.open(
        getSiteUrl(`/admin/tokens/sa/index/surveyid/${surveyId}`),
        '_self'
      )
    }
  }

  return (
    <div
      className={classNames('col-md-3', 'd-flex', 'align-items-stretch', {
        'opacity-50': !hasUpdatePermission,
      })}
    >
      <TooltipContainer
        tip={t('You currently have "View only" access.')}
        showTip={!hasUpdatePermission}
      >
        <Card className="card h-100 w-100">
          <h5 className="med16-c">{t('Participant lists')}</h5>
          <p className="reg14 mb-5">
            {t('Setup participant lists and invite participants via email.')}
          </p>
          <span
            onClick={handleParticipantListNav}
            className="text-primary cursor-pointer med14-c"
          >
            <PlusLg className={'icon-align'} />
            {t('Create participant list')}
          </span>
        </Card>
      </TooltipContainer>
    </div>
  )
}
