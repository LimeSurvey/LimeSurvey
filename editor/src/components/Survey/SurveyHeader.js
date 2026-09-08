import { useEffect, useMemo, useRef, useState } from 'react'
import { Collapse } from 'react-bootstrap'
import classNames from 'classnames'
import { format } from 'util'
import { useFocused, useBuffer, useAppState } from 'hooks'
import { ReactComponent as DownArrow } from 'assets/icons/down-arrow.svg'
import {
  createBufferOperation,
  decodeHTMLEntities,
  L10ns,
  RemoveHTMLTagsInString,
  ScrollToElement,
  STATES,
} from 'helpers'
import { getImageObjectFromJsonData } from 'helpers/surveyImage'
import { getTooltipMessages } from 'helpers/options'
import {
  Button,
  ContentEditor,
  ImageWrapper,
  Select,
} from 'components/UIComponents'
import { getQuestionTypeInfo } from 'components/QuestionTypes'
import { LanguageIcon } from 'components/icons'

import { Section } from './Section'
import { TooltipContainer } from '../TooltipContainer/TooltipContainer'
import { SurveyPrivacyPolicy } from './SurveyPrivacyPolicy'

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
    showSurveyPolicyNotice,
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
  const welcomeImageObject = useMemo(
    () => getImageObjectFromJsonData(welcomeImage),
    [welcomeImage]
  )

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

  const handleTitleKeyDown = (event) => {
    if (event.key === 'Enter') {
      event.preventDefault()
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

  const policyNotice = useMemo(
    () =>
      L10ns({
        prop: 'policyNotice',
        language: activeLanguage,
        l10ns: languageSettings,
      }),
    [languageSettings, activeLanguage]
  )

  const policyNoticeLabel = useMemo(
    () =>
      L10ns({
        prop: 'policyNoticeLabel',
        language: activeLanguage,
        l10ns: languageSettings,
      }),
    [languageSettings, activeLanguage]
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
                'w-100 hover-element survey-header-container',
                {
                  'disabled': !showWelcome,
                  'focus-element':
                    focused?.info?.theme ===
                    getQuestionTypeInfo().WELCOME_SCREEN.theme,
                }
              )}
            >
              <ImageWrapper
                imageObject={welcomeImageObject}
                rowClassName="survey-header-row gap-5"
                imageContainerClassName="welcome-image-container flex-shrink-0"
                contentContainerClassName="flex-grow-1 min-w-0"
                overlayClassName="position-relative z-1"
                backgroundImageClassName="position-absolute top-0 start-0 end-0 bottom-0 w-100 h-100 object-fit-cover"
                imageTestId="welcome-image"
                backgroundImageTestId="welcome-background-image"
              >
                <div
                  className={classNames(
                    'survey-header-content w-100 d-flex flex-column justify-content-between',
                    {
                      // Padding is provided by survey-header-row instead when
                      // the image is rendered beside the content, to avoid
                      // insetting the text twice.
                      'p-0':
                        welcomeImageObject.hasImage &&
                        !welcomeImageObject.hasImageAsBackground,
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
                      tip={t(
                        'This is a preview dropdown for selectable languages'
                      )}
                    >
                      <Select
                        value={activeLanguage}
                        options={getLanguages(languages)}
                      />
                    </TooltipContainer>
                  </div>
                  <ContentEditor
                    id="survey-header-survey-title"
                    className="welcome-screen-survey-title"
                    value={RemoveHTMLTagsInString(
                      L10ns({
                        prop: 'title',
                        language: activeLanguage,
                        l10ns: languageSettings,
                        disabled: !hasSurveyUpdatePermission,
                      })
                    )}
                    update={(value) =>
                      handleUpdate({ title: RemoveHTMLTagsInString(value) })
                    }
                    placeholder={t('Survey title')}
                    language={language}
                    noPermissionDisabled={true}
                    showToolTip={false}
                    testId="survey-header-survey-title"
                    disabled={false}
                    onKeyDown={handleTitleKeyDown}
                    attributeDescriptions={attributeDescriptions}
                  />
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
                    noPermissionDisabled={true}
                    showToolTip={false}
                    testId="survey-header-welcome-description"
                    showToolbar={true}
                    disabled={false}
                    surveyHeader={true}
                    attributeDescriptions={attributeDescriptions}
                  />
                  <div ref={titleRef}>
                    <ContentEditor
                      value={welcomeTitle}
                      id="survey-header-welcome-title"
                      className="welcome-title"
                      update={(value) => handleUpdate({ welcomeText: value })}
                      placeholder={t('Welcome message')}
                      language={activeLanguage}
                      noPermissionDisabled={true}
                      showToolTip={false}
                      testId="survey-header-welcome-title"
                      showToolbar={true}
                      disabled={false}
                      surveyHeader={true}
                      attributeDescriptions={attributeDescriptions}
                    />
                  </div>
                  {!!showSurveyPolicyNotice && (
                    <div className="survey-privacy">
                      <SurveyPrivacyPolicy
                        mode={showSurveyPolicyNotice}
                        policyNotice={policyNotice}
                        policyNoticeLabel={policyNoticeLabel}
                      />
                    </div>
                  )}
                  {showXQuestions && (
                    <p className="text-secondary mt-4 show-x-questions">
                      {numberOfQuestions === 1
                        ? st('There is 1 question in this survey.')
                        : format(
                            st('There are %s questions in this survey.'),
                            numberOfQuestions
                          )}
                    </p>
                  )}

                  <div className="start-survey-section mt-4 ms-1 d-flex align-items-center gap-3">
                    <Button className="start-button">
                      {st('Start survey')}
                    </Button>
                    <span className="or-press-enter-text">
                      {format(st('or press %s'), '↩')}
                    </span>
                  </div>
                </div>
              </ImageWrapper>
            </div>
          </div>
        </Collapse>
      </Section>
    </TooltipContainer>
  )
}
