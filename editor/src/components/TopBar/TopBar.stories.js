import React from 'react'

import { TopBar as TopbarComponent } from './TopBar'

export default {
  title: 'Page/Editor/TopBar',
  component: TopbarComponent,
}

export const TopBar = ({ survey }) => {
  return <TopbarComponent surveyId={survey.sid} />
}
