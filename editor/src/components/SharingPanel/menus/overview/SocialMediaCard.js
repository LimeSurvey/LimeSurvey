import React from 'react'
import { Card } from 'react-bootstrap'

import { SocialMediaShare } from 'components/SocialMediaShare/SocialMediaShare'
import { PluginSlot } from 'plugins/PluginSlot'
import { PLUGIN_SLOTS } from 'plugins/slots'

export const SocialMediaCard = ({ link, title }) => {
  return (
    <div className="col-md-3 d-flex">
      <Card className="card d-flex flex-column justify-content-between  d-flex flex-column h-100 w-100">
        <SocialMediaShare shareUrl={link} surveyTitle={title} />
        <PluginSlot
          slotName={PLUGIN_SLOTS.SHARING_OVERVIEW_SOCIAL_MEDIA_CARD_BOTTOM}
        />
      </Card>
    </div>
  )
}
