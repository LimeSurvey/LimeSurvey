import { useEffect, useMemo, useState } from 'react'
import { useParams } from 'react-router-dom'

import { createBufferOperation, Entities, STATES } from 'helpers'
import {
  useAppState,
  useBuffer,
  useErrors,
  useSurvey,
  useSurveyUpdatePermission,
} from 'hooks'
import { PluginSlot } from 'plugins/PluginSlot'
import { PLUGIN_SLOTS } from 'plugins/slots'

import { LanguageSelector } from './LanguageSelector'
import { SocialMediaCard } from './SocialMediaCard'
import { AliasSettingsCard } from './AliasSettingsCard'
import { QRCodeCard } from './QRCodeCard'
import { ParticipantsListCard } from './ParticipantsListCard'

export const SharingOverview = () => {
  const { surveyId } = useParams()
  const { survey, update } = useSurvey(surveyId)
  const [allLanguages] = useAppState(STATES.ALL_AVAILABLE_LANGUAGES)
  const [userDetails] = useAppState(STATES.USER_DETAIL)
  const languages = allLanguages[userDetails.lang]

  const [aliasHasError, setAliasHasError] = useState(false)
  const { errors } = useErrors()
  const { addToBuffer } = useBuffer()

  const [activeLanguage] = useAppState(STATES.ACTIVE_LANGUAGE)
  const [link, setLink] = useState('')
  const [selectedLanguage, setSelectedLanguage] = useState(activeLanguage)

  const hasUpdatePermission = useSurveyUpdatePermission(survey)

  const languageSelectOptions = useMemo(() => {
    const surveyLanguages = survey.language
      .concat(` ${survey.additionalLanguages}`)
      .trim()
      .split(' ')

    return surveyLanguages.map((option) => {
      const isBaseLanguage = option === survey.language
      let addOn = isBaseLanguage ? ' (' + t('Base language') + ')' : ''
      let languageOption = {
        value: option,
        label: languages
          ? languages[option]?.description + addOn
          : 'No data available',
      }

      return languageOption
    })
  }, [languages, survey.language])

  const onAliasChange = (alias) => {
    alias = typeof alias === 'number' ? '' : alias.trim()
    setAliasHasError(false)

    const operation = createBufferOperation(survey.sid)
      .languageSetting()
      .update({ [selectedLanguage]: { alias } })

    addToBuffer(operation)
    update({
      languageSettings: {
        ...survey.languageSettings,
        [selectedLanguage]: {
          ...survey.languageSettings[selectedLanguage],
          alias,
        },
      },
    })
  }

  useEffect(() => {
    setAliasHasError(JSON.stringify(errors).includes(Entities.languageSetting))
  }, [errors])

  return (
    <div
      key={`sharing-overview-${selectedLanguage}`}
      className="sharing-panel container-fluid  mt-5"
    >
      <div className="d-flex  mb-3 justify-content-between align-items-center">
        <h1 className="title">
          {' '}
          {t('Share your survey and collect responses')}
        </h1>
      </div>
      <div className="row g-4">
        <LanguageSelector
          selectOptions={languageSelectOptions}
          selectedLanguage={selectedLanguage}
          setSelectedLanguage={setSelectedLanguage}
        />
        <AliasSettingsCard
          survey={survey}
          onAliasChange={onAliasChange}
          setLink={setLink}
          selectedLanguage={selectedLanguage}
          aliasHasError={aliasHasError}
          update={update}
          createBufferOperation={createBufferOperation}
          addToBuffer={addToBuffer}
        />
        <QRCodeCard link={link} />
        <SocialMediaCard
          link={link}
          title={survey.languageSettings[selectedLanguage]?.title || ''}
        />
        <PluginSlot slotName={PLUGIN_SLOTS.SHARING_OVERVIEW_BOTTOM_LEFT} />
        <PluginSlot slotName={PLUGIN_SLOTS.SHARING_OVERVIEW_BOTTOM_RIGHT} />
        <ParticipantsListCard
          hasUpdatePermission={hasUpdatePermission}
          surveyId={surveyId}
        />
      </div>
    </div>
  )
}
