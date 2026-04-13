import { useState, useEffect, useRef } from 'react'
import { useNavigate } from 'react-router-dom'
import { Button, Card, Container } from 'react-bootstrap'

import { PublicSurveyAlias } from 'components'
import { ACCESS_MODES, PAGES, SURVEY_MENU_TITLES } from 'helpers'
import { useStatisticsAtGlance } from 'hooks'
import { getSharingPanels } from 'shared/getSharingPanels'
import { CheckIcon, EyeIcon, StopIcon } from 'components/icons'
import { BrandedQRCode } from 'components/BrandedQRCode/BrandedQRCode'

import rightArrowIcon from 'assets/icons/right-arrow.svg'
import pencilIconWhite from 'assets/icons/pencil-icon-white.svg'

import OverViewToast from './OverViewToast'

export const SurveyOverview = ({
  survey = {},
  update,
  hasOperations,
  numberOfQuestions = 0,
  hasSurveyUpdatePermission,
  editSurvey,
  togglePublish,
  toastMessage,
  activeLanguage,
  setShowOverViewModal = () => {},
  createBufferOperation = () => {},
  addToBuffer = () => {},
}) => {
  const navigate = useNavigate()
  const [link, setLink] = useState('')

  const {
    statistics = {},
    refetch,
    isFetching,
  } = useStatisticsAtGlance(survey?.sid)

  const surveyTitle = survey?.languageSettings?.[survey?.language]?.title
  const dateCreated = new Date(survey.dateCreated)

  useEffect(() => {
    // Refetch statistics every time the modal opens
    if (survey?.sid) {
      refetch()
    }
  }, [survey?.sid, refetch])

  const numberRefs = useRef([])

  const handleShowingSharingPanel = () => {
    setShowOverViewModal(false)
    navigate(
      `/${PAGES.SHARE}/${survey.sid}/${getSharingPanels().sharing.panel}/${SURVEY_MENU_TITLES.sharingOverview}`
    )
  }

  useEffect(() => {
    // Adjust font size based on content length
    numberRefs.current.forEach((ref) => {
      if (ref) {
        const content = ref.textContent
        if (content.length > 3) {
          const newSize = Math.max(16, 28 - (content.length - 3))
          ref.style.fontSize = `${newSize}px`
        }
      }
    })
  }, [statistics?.completionRate])

  return (
    <Container className="overview-modal p-0 w-100 h-100 d-flex flex-row justfiy-content-between">
      {!survey?.sid || !statistics || isFetching ? ( // Loading state here within the modal so there's no flicker when switching states
        <div className="overview-section overview-main-section w-75 d-flex align-items-center justify-content-center gap-3">
          <span
            style={{ width: 48, height: 48 }}
            className="loader"
            role="status"
          ></span>
          <h2 className="text-muted m-0">{t('Loading overview...')}</h2>
        </div>
      ) : (
        <>
          <div className="overview-section overview-main-section w-75">
            {toastMessage && (
              <div className="row mb-4 px-3">
                <OverViewToast message={toastMessage} />
              </div>
            )}
            <div className="row g-4">
              <div className="col-md-4">
                <Card className="h-100 w-100">
                  <Card.Body className="d-flex flex-row flex-wrap justify-content-center align-items-center gap-2">
                    <span
                      className="reg28 text-nowrap"
                      ref={(el) => (numberRefs.current[0] = el)}
                    >
                      {statistics.totalResponses}
                    </span>
                    <span className="reg14">{t('Total responses')}</span>
                  </Card.Body>
                </Card>
              </div>
              <div className="col-md-4">
                <Card className="h-100 w-100">
                  <Card.Body className="d-flex flex-row flex-wrap justify-content-center align-items-center gap-2">
                    <span
                      className="reg28 text-nowrap"
                      ref={(el) => (numberRefs.current[1] = el)}
                    >
                      {statistics.totalResponses -
                        statistics.incompleteResponses}
                    </span>
                    <span className="reg14">{t('Full responses')}</span>
                  </Card.Body>
                </Card>
              </div>
              <div className="col-md-4">
                <Card className="h-100 w-100">
                  <Card.Body className="d-flex flex-row flex-wrap justify-content-center align-items-center gap-2">
                    {statistics.completionRate ? (
                      <>
                        <span
                          className="reg28 text-nowrap"
                          ref={(el) => (numberRefs.current[2] = el)}
                        >
                          {`${statistics.completionRate}%`}
                        </span>
                        <span className="reg14">{t('Response rate')}</span>
                      </>
                    ) : (
                      <span className="reg14">{t('No responses yet.')}</span>
                    )}
                  </Card.Body>
                </Card>
              </div>
              <div className="col-md-4">
                <Card className="h-100 w-100">
                  <Card.Body className="d-flex flex-row flex-wrap justify-content-center align-items-center gap-2">
                    <span
                      className="reg28 text-nowrap"
                      ref={(el) => (numberRefs.current[3] = el)}
                    >
                      {statistics.incompleteResponses}
                    </span>
                    <span className="reg14">{t('Incomplete responses')}</span>
                  </Card.Body>
                </Card>
              </div>
            </div>
            <div
              onClick={() => navigate(`/responses/${survey.sid}`)}
              className="text-primary text-start arrow-link med14-c cursor-pointer"
            >
              {t('View results overview')} <img src={rightArrowIcon} />
            </div>
            <div className="row g-4">
              <div className="col-md-8 d-flex align-items-stretch">
                <Card className="card d-flex flex-column justify-content-between h-100 w-100">
                  <PublicSurveyAlias
                    parentName="Overview"
                    survey={survey}
                    update={update}
                    setLink={setLink}
                    language={activeLanguage}
                    editable={false}
                    currentSurveyAccessMode={
                      survey?.access_mode || ACCESS_MODES.OPEN_TO_ALL
                    }
                    createBufferOperation={createBufferOperation}
                    addToBuffer={addToBuffer}
                  />
                </Card>
              </div>
              <div className="col-md-4 d-flex align-items-stretch">
                <Card className="text-center h-100 w-100">
                  <BrandedQRCode value={link} />
                </Card>
              </div>
            </div>

            <div
              onClick={handleShowingSharingPanel}
              className="text-primary med14-c arrow-link text-start cursor-pointer"
            >
              {t('View sharing overview')} <img src={rightArrowIcon} />
            </div>
          </div>

          <div className="overview-section overview-right-section w-25 bg-white d-flex flex-column justify-content-between">
            <div className="text-start">
              <div className="reg18">{surveyTitle || t('Survey title')}</div>
              <div className="reg12">{dateCreated.toLocaleDateString()}</div>
            </div>
            <div>
              <Button
                className="text-start d-flex align-items-center gap-2 reg14 font-normal w-100"
                onClick={editSurvey}
              >
                <img src={pencilIconWhite} />
                {t('Edit survey')}
              </Button>
              <Button
                variant={survey.active ? 'danger' : 'success'}
                className={
                  ' align-items-center w-100 my-2 d-flex gap-2 text-start reg14'
                }
                onClick={() => togglePublish()}
                disabled={
                  hasOperations ||
                  numberOfQuestions === 0 ||
                  !hasSurveyUpdatePermission
                }
              >
                <div className="d-flex align-items-center stop-icon">
                  {survey.active ? (
                    <StopIcon />
                  ) : (
                    <CheckIcon className="fill-current text-white" />
                  )}
                </div>
                <p className="m-0 reg-14 text-white">
                  {survey.active ? t('Deactivate') : t('Activate')}
                </p>
              </Button>

              <Button
                variant="secondary"
                className="w-100 d-flex align-items-center gap-2 text-start reg14"
                onClick={() => window.open(survey.previewLink, '_blank')}
              >
                <EyeIcon fill="#FFF" /> {t('Preview survey')}
              </Button>
            </div>
          </div>
        </>
      )}
    </Container>
  )
}
