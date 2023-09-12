import { useState } from 'react'
import classNames from 'classnames'
import { Collapse } from 'react-bootstrap'
import Button from 'react-bootstrap/Button'
import { ChevronDown } from 'react-bootstrap-icons'

import { L10ns, LANGUAGE_CODES } from 'helpers'
import { useFocused } from 'hooks'
import { ContentEditor } from 'components/UIComponents'
import { QuestionTypeInfo } from 'components/QuestionTypes'

import { Section } from './Section'
import { Card } from './Card'

export const SurveyFooter = ({
  languageSettings,
  language,
  update,
  isEmpty,
}) => {
  // Todo: remove it once we have a value in the survey-detail json.
  const [thankYouValue, setThankYouValue] = useState('Thank you')
  const [collapse, setCollapse] = useState(false)
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
    setFocused({ info: QuestionTypeInfo.END_SCREEN })
  }

  if (isEmpty) {
    return <></>
  }

  const endScreenTitle = languageSettings[language]?.endScreenButtonTitle
  const defaultEndScreenTitle =
    languageSettings[LANGUAGE_CODES.EN]?.endScreenButtonTitle

  const endScreenForwardingButtonUrl =
    languageSettings[language]?.endScreenForwardingButtonUrl
  const defaultEndScreenForwardingButtonUrl =
    languageSettings[LANGUAGE_CODES.EN].endScreenForwardingButtonUrl

  const finishButtonTitle = endScreenTitle
    ? endScreenTitle
    : defaultEndScreenTitle
    ? defaultEndScreenTitle
    : 'Finish'

  const finishButtonForwardingUrl = endScreenForwardingButtonUrl
    ? endScreenForwardingButtonUrl
    : defaultEndScreenForwardingButtonUrl
    ? defaultEndScreenForwardingButtonUrl
    : ''

  return (
    <>
      <Section
        onClick={handleOnClick}
        className={classNames('survey-footer survey-container')}
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
            <div>End screen</div>
          </div>
        </div>
        <Card
          className={classNames('d-flex ', {
            'focus-element':
              focused.info?.type === QuestionTypeInfo.END_SCREEN.type,
          })}
        >
          <div
            className={classNames(
              'w-100 d-flex flex-column justify-content-between survey-header-container py-4'
            )}
          >
            <div id="survey-header-title" className="title">
              <h4>
                <ContentEditor
                  placeholder="Enter thank you title here"
                  value={thankYouValue}
                  id="survey-footer-title"
                  update={(value) => setThankYouValue(value)}
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
                  <div>
                    <h6>
                      <ContentEditor
                        value={L10ns({
                          prop: 'endText',
                          language,
                          l10ns: languageSettings,
                        })}
                        update={(value) => handleUpdate({ endText: value })}
                        placeholder="Enter your thank you message here."
                        style={{ marginLeft: '-4px' }}
                      />
                    </h6>
                    <div className="mt-4 ms-1">
                      <Button
                        className="start-button"
                        variant="outline-secondary"
                        style={{
                          width: 'fit-content',
                          minWidth: '110px',
                          marginLeft: '-4px',
                        }}
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
              </Collapse>
            </div>
          </div>
        </Card>
      </Section>
    </>
  )
}
