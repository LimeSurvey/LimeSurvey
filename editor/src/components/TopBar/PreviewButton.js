import { useMemo } from 'react'

import { Button } from 'components/UIComponents'
import { EyeIcon } from 'components/icons'
import { decodeHTMLEntities, getSurveyAccessLink, STATES } from 'helpers'
import { useAppState } from 'hooks'

export const PreviewButton = ({ survey }) => {
  const [allLanguages] = useAppState(STATES.ALL_AVAILABLE_LANGUAGES)
  const [userDetails] = useAppState(STATES.USER_DETAIL)
  const [activeLanguage] = useAppState(STATES.ACTIVE_LANGUAGE)

  const languageNames = allLanguages?.[userDetails?.lang]

  const surveyLanguages = useMemo(() => {
    if (!survey.additionalLanguages || survey.additionalLanguages === '') {
      return [survey.language]
    }

    return [survey.language, ...survey.additionalLanguages.split(' ')]
  }, [survey.language, survey.additionalLanguages])

  const languageOptions = useMemo(() => {
    return surveyLanguages.map((code) => ({
      code,
      label: decodeHTMLEntities(languageNames?.[code]?.description || code),
      href: getSurveyAccessLink({ survey, language: code }),
    }))
  }, [survey, surveyLanguages, languageNames])

  const currentLanguage = activeLanguage || survey.language

  return (
    <div className="preview-button-wrapper me-2">
      <Button
        className="preview-button p-0 d-flex align-items-center justify-content-center"
        variant="light"
        id="preview-button"
      >
        <EyeIcon />
      </Button>
      <div className="preview-button-flyout">
        <p className="preview-button-flyout-heading label-s-med">
          {t('Survey language version')}
        </p>
        {languageOptions.map(({ code, label, href }) => (
          <a
            key={code}
            href={href}
            target="_blank"
            rel="noreferrer"
            className={`preview-button-flyout-item`}
          >
            {label}
          </a>
        ))}
      </div>
    </div>
  )
}
