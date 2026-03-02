import { useEffect, useMemo, useRef, useState } from 'react'
import { Collapse, FormCheck } from 'react-bootstrap'
import classNames from 'classnames'
import { format } from 'util'
import { useFocused, useBuffer, useAppState } from 'hooks'
import { ReactComponent as DownArrow } from 'assets/icons/down-arrow.svg'
import {
  createBufferOperation,
  decodeHTMLEntities,
  L10ns,
  ScrollToElement,
  STATES,
} from 'helpers'
import { getTooltipMessages } from 'helpers/options'
import { Button, ContentEditor, Select } from 'components/UIComponents'
import { LanguageSwitch } from 'components/SurveySettings/GeneralSettings/LanguageSwitch'
import { getQuestionTypeInfo } from 'components/QuestionTypes'
import { LanguageIcon } from 'components/icons'

import { Section } from './Section'
import { TooltipContainer } from '../TooltipContainer/TooltipContainer'

export const SurveyHeader = ({
  update,
  numberOfQuestions,
  activeLanguage,
  survey: {
    sid,
    languageSettings,
    language,
    welcomeImage,
    showWelcome,
    showXQuestions,
    allowLanguageSwitch,
    showPrivacyPolicy,
    privacyPolicyCheckBox,
    privacyPolicyMessage,
    legalNoticeMessage,
    privacyPolicyLabelMessage,
    showLegalNotice,
    additionalLanguages,
    hasSurveyUpdatePermission,
  },
  allLanguages,
}) => {
  const [collapse, setCollapse] = useState(false)
  const { setFocused, focused = {} } = useFocused()
  const { addToBuffer } = useBuffer()
  const titleRef = useRef(null)
  const [attributeDescriptions] = useAppState(STATES.ATTRIBUTE_DESCRIPTIONS)

  useEffect(() => {
    setCollapse(!showWelcome)
  }, [showWelcome])

  useEffect(() => {
    const isWelcomeScreenFocused =
      focused.info?.type === getQuestionTypeInfo().WELCOME_SCREEN.type

    if (isWelcomeScreenFocused) {
      ScrollToElement(titleRef.current)
    }
  }, [focused.info?.type])

  const languageSource = allLanguages[activeLanguage]
  const languages = useMemo(() => {
    if (!additionalLanguages || additionalLanguages === '') {
      return [language]
    }

    return [language, ...additionalLanguages.split(' ')]
  }, [language, additionalLanguages])

  const getLanguages = (languages) => {
    if (!languages) return []

    return languages.map((language) => {
      return {
        value: language,
        label:
          decodeHTMLEntities(languageSource?.[language]?.nativedescription) +
          ' - ' +
          languageSource?.[language]?.description,
      }
    })
  }

  const handleUpdate = (updated) => {
    const updateData = {
      ...languageSettings,
    }

    updateData[activeLanguage] = {
      ...updateData[activeLanguage],
      ...updated,
    }

    const operation = createBufferOperation(null)
      .languageSetting()
      .update({
        [activeLanguage]: updateData[activeLanguage],
      })

    addToBuffer(operation)
    update({ languageSettings: updateData })
  }

  const handleOnClick = () => {
    if (hasSurveyUpdatePermission) {
      setFocused({ info: getQuestionTypeInfo().WELCOME_SCREEN })
    }
  }

  const welcomeTitle = useMemo(
    () =>
      L10ns({
        prop: 'welcomeText',
        language: activeLanguage,
        l10ns: languageSettings,
      }),
    [languageSettings]
  )

  if (!sid) {
    return <></>
  }

  return (
    <TooltipContainer
      tip={getTooltipMessages().NO_PERMISSION}
      showTip={!hasSurveyUpdatePermission}
      placement="left"
    >
      <Section
        onClick={handleOnClick}
        testId="survey-header-section"
        className={classNames('survey-header', {
          'inactive-section-header': !showWelcome,
          'cursor-not-allowed': !hasSurveyUpdatePermission,
        })}
      >
        <div
          id="survey-header"
          className="collapse-control d-flex justify-content-between mb-2"
        >
          <div className="d-flex align-items-center gap-3 header">
            <div>
              <span
                className="p-0 cursor-pointer"
                onClick={() => setCollapse(!collapse)}
              >
                <DownArrow
                  className={classNames('transition-all mb-1', {
                    'rotate-180': collapse,
                  })}
                />
              </span>
            </div>
            <div>{t('Welcome screen')}</div>
          </div>
        </div>
        <Collapse in={!collapse}>
          <div>
            <div
              className={classNames(
                'w-100 d-flex flex-column justify-content-between hover-element survey-header-container',
                {
                  'w-50': welcomeImage && !collapse,
                  'disabled': !showWelcome,
                  'focus-element':
                    focused?.info?.theme ===
                    getQuestionTypeInfo().WELCOME_SCREEN.theme,
                }
              )}
            >
              <div
                data-testid="language-change-select"
                className="language-change-header d-flex align-items-center gap-2"
              >
                <LanguageIcon />
                {st('Change language')}
                <TooltipContainer
                  placement="right"
                  showTip={true}
                  tip={t('This is a preview dropdown for selectable languages')}
                >
                  <Select
                    value={activeLanguage}
                    options={getLanguages(languages)}
                  />
                </TooltipContainer>
              </div>
              <div ref={titleRef}>
                <ContentEditor
                  value={welcomeTitle}
                  id="survey-header-welcome-title"
                  className="welcome-title"
                  update={(value) => handleUpdate({ welcomeText: value })}
                  placeholder={t('Welcome title')}
                  language={activeLanguage}
                  useRichTextEditor={true}
                  noPermissionDisabled={true}
                  showToolTip={false}
                  testId="survey-header-welcome-title"
                  showToolbar={true}
                  disabled={false}
                  surveyHeader={true}
                  attributeDescriptions={attributeDescriptions}
                />
              </div>
              <ContentEditor
                id="survey-header-welcome-description"
                className="welcome-description"
                value={L10ns({
                  prop: 'description',
                  language: activeLanguage,
                  l10ns: languageSettings,
                  disabled: !hasSurveyUpdatePermission,
                })}
                update={(value) => handleUpdate({ description: value })}
                placeholder={t('Welcome description')}
                language={language}
                useRichTextEditor={true}
                noPermissionDisabled={true}
                showToolTip={false}
                testId="survey-header-welcome-description"
                showToolbar={true}
                disabled={false}
                surveyHeader={true}
                attributeDescriptions={attributeDescriptions}
              />
              <div className={classNames('ms-1 transition-all')}>
                {showXQuestions && (
                  <p className="text-secondary mt-3 show-x-questions">
                    {format(
                      st('There are %s questions in this survey.'),
                      numberOfQuestions
                    )}
                  </p>
                )}
                {showPrivacyPolicy && (
                  <div className="survey-privacy">
                    <div className="d-flex align-items-center ms-1">
                      <FormCheck
                        checked={privacyPolicyCheckBox}
                        label={privacyPolicyLabelMessage || t('Privacy policy')}
                        type="checkbox"
                        name="privacy-policy-checkbox"
                        data-testid="privacy-policy-checkbox"
                        onChange={(e) =>
                          update({ privacyPolicyCheckBox: e.target.checked })
                        }
                      />
                    </div>
                    <div className="mt-3">
                      <h6>
                        <ContentEditor
                          value={privacyPolicyMessage}
                          update={(value) =>
                            update({ privacyPolicyMessage: value })
                          }
                          placeholder={t('Privacy policy message')}
                        />
                      </h6>
                    </div>
                    {allowLanguageSwitch && (
                      <div className="mt-4 ms-1">
                        <LanguageSwitch
                          language={language}
                          label={st('Survey language')}
                        />
                      </div>
                    )}
                  </div>
                )}
                {showLegalNotice && (
                  <div className="d-flex ">
                    <a href="/legalNotice" className="text-success ms-auto">
                      {legalNoticeMessage}
                    </a>
                  </div>
                )}
              </div>

              <div className="start-survey-section mt-4 ms-1 d-flex align-items-center gap-3">
                <Button className="start-button">{st('Start survey')}</Button>
                <span className="or-press-enter-text">
                  {format(st('or press %s'), '↩')}
                </span>
              </div>
            </div>
          </div>
        </Collapse>
      </Section>
    </TooltipContainer>
  )
}
