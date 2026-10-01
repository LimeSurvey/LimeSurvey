import React from 'react'

import { RightSideBar as RightSideBarComponent } from './RightSideBar'

export default {
  title: 'Page/Editor/RightSideBar',
  component: RightSideBarComponent,
}

const surveyId = '78f91e52-6028-11ed-82e1-7ac846e3af9d'

export const RightSideBar = () => {
  return <RightSideBarComponent surveyId={surveyId} />
}
