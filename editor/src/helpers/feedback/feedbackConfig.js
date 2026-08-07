export const FEEDBACK_TYPES = {
  GENERAL: 'general',
  SHARING: 'sharing',
}

export const cookieExpiresInterval = 365 * 24 * 60 * 60 * 1000

export const getFeedbackConfigs = () => {
  return {
    [FEEDBACK_TYPES.GENERAL]: {
      surveyId: 516925,
      cookieKey: 'LS_FEEDBACK_POPUP',
      title: t('What do you think so far?'),
      description: t(
        'We would very much appreciate it if you could answer two quick questions about your experience with the new editor?'
      ),
      triggerType: 'automatic', // if triggerType is 'automatic' define 'delayMins' & 'intervalMinutes'
      delayMins: process.env.REACT_APP_FEEDBACK_POPUP_DELAY_MINS,
      intervalMinutes: process.env.REACT_APP_FEEDBACK_POPUP_INTERVAL_MINS,
    },
    [FEEDBACK_TYPES.SHARING]: {
      surveyId: 763972,
      cookieKey: 'LS_FEEDBACK_POPUP_SHARING',
      title: t('Do you have a minute?'),
      description: t(
        'We would love to hear your thoughts on our sharing options!'
      ),
      triggerType: 'manual', // triggered when feedbackEventHandler event is emmited
      primaryCTA: t('Tell us'),
    },
  }
}
