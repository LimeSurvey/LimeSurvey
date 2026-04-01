import { useEffect, useRef, useState } from 'react'
import classNames from 'classnames'
import { Collapse } from 'react-bootstrap'
import { ReactComponent as DownArrow } from 'assets/icons/down-arrow.svg'

import { useFocused, useBuffer, useAppState } from 'hooks'
import {
  createBufferOperation,
  L10ns,
  LANGUAGE_CODES,
  ScrollToElement,
  STATES,
} from 'helpers'
import { getTooltipMessages } from 'helpers/options'
import { Button, ContentEditor } from 'components/UIComponents'
import { getQuestionTypeInfo } from 'components/QuestionTypes'
import { TooltipContainer } from 'components'

import { Section } from './Section'
import { Card } from './Card'

export const SurveyFooter = ({
  language,
  survey: { languageSettings, sid },
  update,
}) => {
  const titleRef = useRef(null)
  const [hasSurveyUpdatePermission] = useAppState(
    STATES.HAS_SURVEY_UPDATE_PERMISSION
  )
  const [collapse, setCollapse] = useState(false)
  const { focused = {}, setFocused } = useFocused()
  const { addToBuffer } = useBuffer()

  const handleUpdate = (updated) => {
    const updateData = {
      ...languageSettings,
    }

    updateData[language] = {
      ...updateData[language],
      ...updated,
    }
    const operation = createBufferOperation(null)
      .languageSetting()
      .update({
        [language]: updateData[language],
      })
    addToBuffer(operation)
    update({ languageSettings: updateData })
  }

  const handleOnClick = () => {
    if (hasSurveyUpdatePermission) {
      setFocused({ info: getQuestionTypeInfo().END_SCREEN })
    }
  }

  useEffect(() => {
    const isEndScreenFocused =
      focused.info?.type === getQuestionTypeInfo().END_SCREEN.type

    if (isEndScreenFocused) {
      ScrollToElement(titleRef.current)
    }
  }, [focused.info?.type])

  if (!sid) {
    return <></>
  }

  const endScreenTitle = languageSettings[language]?.urlDescription
  const defaultEndScreenTitle =
    languageSettings[LANGUAGE_CODES.EN]?.urlDescription

  const endScreenForwardingButtonUrl = languageSettings[language]?.url
  const defaultEndScreenForwardingButtonUrl =
    languageSettings[LANGUAGE_CODES.EN]?.url

  const finishButtonTitle = endScreenTitle
    ? endScreenTitle
    : defaultEndScreenTitle
      ? defaultEndScreenTitle
      : st('Finish')

  const finishButtonForwardingUrl = endScreenForwardingButtonUrl
    ? endScreenForwardingButtonUrl
    : defaultEndScreenForwardingButtonUrl
      ? defaultEndScreenForwardingButtonUrl
      : ''

  return (
    <TooltipContainer
      tip={getTooltipMessages().NO_PERMISSION}
      showTip={!hasSurveyUpdatePermission}
      placement="left"
    >
      <Section
        onClick={handleOnClick}
        testId="survey-footer-section"
        className={classNames('survey-footer', {
          'cursor-not-allowed': !hasSurveyUpdatePermission,
        })}
      >
        <div className="collapse-control d-flex justify-content-between mb-2">
          <div className="d-flex align-items-center gap-3 header">
            <div>
              <Button
                variant="link"
                className="p-0"
                onClick={() => setCollapse(!collapse)}
              >
                <DownArrow
                  style={{ cursor: 'pointer' }}
                  className={classNames('transition-all mb-1', {
                    'rotate-180': collapse,
                  })}
                />
              </Button>
            </div>
            <div>{t('End screen')}</div>
          </div>
        </div>
        <Collapse in={!collapse}>
          <div>
            <Card
              className={classNames('d-flex hover-element', {
                'focus-element':
                  focused.info?.type === getQuestionTypeInfo().END_SCREEN.type,
              })}
            >
              <div
                className={classNames(
                  'w-100 d-flex flex-column justify-content-between survey-footer-container py-4'
                )}
              >
                <div ref={titleRef} id="survey-footer-title" className="title">
                  <h6>
                    <ContentEditor
                      value={L10ns({
                        prop: 'endText',
                        language,
                        l10ns: languageSettings,
                      })}
                      update={(value) => handleUpdate({ endText: value })}
                      placeholder={t('Enter your end message here.')}
                      style={{ marginLeft: '-4px' }}
                      useRichTextEditor={false}
                      noPermissionDisabled={true}
                      showToolTip={false}
                      testId="survey-footer-end-text-content-editor"
                    />
                  </h6>
                </div>
                <div
                  className={classNames('ms-1 transition-all', {
                    'my-3': !collapse,
                  })}
                >
                  <div>
                    <div className="mt-4 ms-1">
                      <Button
                        className="finish-button"
                        testId="survey-footer-finish-button"
                      >
                        {finishButtonForwardingUrl ? (
                          <a href={finishButtonForwardingUrl}>
                            {finishButtonTitle}
                          </a>
                        ) : (
                          <>{finishButtonTitle}</>
                        )}
                      </Button>
                    </div>
                  </div>
                </div>
              </div>
            </Card>
          </div>
        </Collapse>
      </Section>
    </TooltipContainer>
  )
}
