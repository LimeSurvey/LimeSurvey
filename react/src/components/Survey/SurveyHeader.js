import { useEffect, useState } from 'react'
import { Collapse, FormCheck } from 'react-bootstrap'
import { ChevronDown } from 'react-bootstrap-icons'
import Button from 'react-bootstrap/Button'
import classNames from 'classnames'

import { ContentEditor } from 'components/UIComponents'
import { MeatballMenu } from 'components/MeatballMenu/MeatballMenu'
import { L10ns } from 'helpers'
import { useFocused } from 'hooks'
import { LanguageSwitch } from 'components/SurveySettings/GeneralSettings/Attributes/LanguageSwitch'
import { EditableImage } from 'components/EditableImage/EditableImage'
import { QuestionTypeInfo } from '../QuestionTypes'

import { Section } from './Section'
import { Card } from './Card'

export const SurveyHeader = ({
  update,
  numberOfQuestions,
  survey,
  survey: {
    languageSettings,
    language,
    welcomeImage,
    imageAlign,
    showWelcome,
    showXQuestions,
    imageBrightness,
    allowLanguageSwitch,
    showPrivacyPolicy,
    privacyPolicyCheckBox,
    privacyPolicyMessage,
    privacyPolicyErrMessage,
    legalNoticeMessage,
    privacyPolicyLabelMessage,
    showLegalNotice,
  },
}) => {
  console.log('survey: ', survey)
  const [collapse, setCollapse] = useState(false)
  const [rgba, setRgba] = useState('')
  const { focused = {}, setFocused } = useFocused()

  const handleUpdate = (updated) => {
    const updateData = {
      ...languageSettings,
    }

    updateData[language] = {
      ...updateData[language],
      ...updated,
    }

    update({ languageSettings: updateData })
  }

  const handleOnClick = () => {
    setFocused({ info: QuestionTypeInfo.WELCOME_SCREEN })
  }

  const handleRemoveImage = () => {
    update({ welcomeImage: '' })
  }

  useEffect(() => {
    const brightness = imageBrightness || 0
    const rgba =
      brightness > 0
        ? `rgba(255, 255, 255, ${brightness / 100})`
        : `rgba(0, 0, 0, ${-brightness / 100})`

    setRgba(rgba)
  }, [imageBrightness])

  return (
    <Section
      onClick={handleOnClick}
      className={classNames('survey-header survey-container', {
        'participant-hidden': !showWelcome,
      })}
    >
      <div className="collapse-control d-flex justify-content-between mb-2">
        <div className="d-flex align-items-center gap-3 ">
          <div>
            <Button
              variant="link"
              className="p-0"
              onClick={() => setCollapse(!collapse)}
            >
              <ChevronDown
                style={{ cursor: 'pointer' }}
                className={classNames('transition-all mb-1', {
                  'rotate-180': !collapse,
                })}
                size={14}
                color={'#9094A7'}
              />
            </Button>
          </div>
          <div>Welcome screen</div>
        </div>
        <div>
          <MeatballMenu />
        </div>
      </div>
      <Card
        style={{
          backgroundImage: `linear-gradient(${rgba}, ${rgba}),url(${
            welcomeImage && imageAlign === 'center' && welcomeImage
          })`,
          backgroundSize: 'cover',
          backgroundPosition: 'center',
          backgroundRepeat: 'no-repeat',
        }}
        className={classNames('d-flex ', {
          'flex-row-reverse': imageAlign === 'right',
          'focus-element':
            focused.info?.type === QuestionTypeInfo.WELCOME_SCREEN.type,
        })}
      >
        {welcomeImage && imageAlign !== 'center' && (
          <EditableImage
            imageSrc={welcomeImage}
            width={collapse ? '0' : '100%'}
            handleRemoveImage={handleRemoveImage}
            update={(file) => update({ welcomeImage: { ...file } })}
            isBigSize
          />
        )}
        <div
          className={classNames(
            'w-100 d-flex flex-column justify-content-between survey-header-container py-4',
            {
              'w-50': welcomeImage && !collapse,
            }
          )}
        >
          <div id="survey-header-title" className="title">
            <h4
              className={classNames({
                disabled: !showWelcome,
              })}
            >
              <ContentEditor
                value={L10ns({
                  prop: 'title',
                  language,
                  l10ns: languageSettings,
                })}
                update={(value) => handleUpdate({ title: value })}
                placeholder="Enter title here"
                useRichTextEditor={true}
                language={language}
              />
            </h4>
          </div>
          <div
            className={classNames('ms-1 transition-all', {
              'my-3': !collapse,
            })}
          >
            <Collapse in={!collapse}>
              <div>
                {showXQuestions && (
                  <p className="fs-6 ms-1 fw-normal text-secondary">
                    There are {numberOfQuestions} questions in this survey.
                  </p>
                )}
                <div>
                  <h6
                    className={classNames({
                      disabled: !showWelcome,
                    })}
                  >
                    <ContentEditor
                      value={L10ns({
                        prop: 'description',
                        language: language,
                        l10ns: languageSettings,
                      })}
                      update={(value) => handleUpdate({ description: value })}
                      placeholder="Add an optional description here"
                    />
                  </h6>
                </div>
                {showPrivacyPolicy && (
                  <div className="survey-privacy">
                    <div className="d-flex align-items-center ms-1">
                      <FormCheck
                        checked={privacyPolicyCheckBox}
                        label={privacyPolicyLabelMessage || 'Privacy Policy'}
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
                          placeholder="Privacy Policy Message"
                        />
                      </h6>
                    </div>

                    {allowLanguageSwitch && (
                      <div className="mt-4 ms-1">
                        <LanguageSwitch
                          handleLanguageChange={(language) =>
                            update({ language })
                          }
                          language={language}
                          label="Survey language"
                        />
                      </div>
                    )}
                    <div className="mt-4 ms-1 ">
                      <Button
                        className="start-button"
                        variant="outline-secondary"
                      >
                        Start
                      </Button>
                    </div>
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
            </Collapse>
          </div>
        </div>
      </Card>
    </Section>
  )
}
