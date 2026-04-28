const getStandardSurveyParticipantsAttributes = () => {
  return [
    { value: '{TOKEN:FIRSTNAME}', label: t('First name') },
    { value: '{TOKEN:LASTNAME}', label: t('Last name') },
    { value: '{TOKEN:EMAIL}', label: t('Email address') },
    { value: '{TOKEN:EMAILSTATUS}', label: t('Email status') },
    { value: '{TOKEN:TOKEN}', label: t('Access code') },
    { value: '{TOKEN:LANGUAGE}', label: t('Language code') },
    { value: '{TOKEN:SENT}', label: t('Invitation sent') },
    { value: '{TOKEN:REMINDERSENT}', label: t('Last reminder sent') },
    {
      value: '{TOKEN:REMINDERCOUNT}',
      label: t('Total number of reminders sent'),
    },
    { value: '{TOKEN:USESLEFT}', label: t('Uses left') },
    { value: '{TOKEN:COMPLETED}', label: t('Completed') },
  ]
}

export const getSurveyParticipantAttributes = (survey) => {
  const attributeDescriptions = survey?.attributeDescriptions || {}
  const customAttributes = Object.entries(attributeDescriptions).map(
    ([key, value]) => ({
      value: `{TOKEN:${key.toUpperCase()}}`,
      label: value.description ? value.description : key,
    })
  )

  const existingValues = new Set(
    getStandardSurveyParticipantsAttributes().map(({ value }) => value)
  )
  const uniqueCustomAttributes = customAttributes.filter(
    ({ value }) => !existingValues.has(value)
  )

  return [
    ...getStandardSurveyParticipantsAttributes(),
    ...uniqueCustomAttributes,
  ]
}
