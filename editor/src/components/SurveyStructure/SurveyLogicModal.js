import React from 'react'

import { useSurveyLogic } from 'hooks'
import { ComponentModal } from 'components/Modals'

/**
 * Modal that shows the survey logic overview for a question or question group.
 *
 * The markup is generated server-side by the `survey-logic` API endpoint and
 * rendered as-is, the same way the legacy admin survey logic page does.
 */
export const SurveyLogicModal = ({
  show = false,
  onHide = () => {},
  sid,
  gid,
  qid,
  language,
}) => {
  const { surveyLogic, isFetching, isError } = useSurveyLogic({
    sid,
    gid,
    qid,
    language,
    // Only fetch while the modal is open.
    enabled: show,
  })

  const renderBody = () => {
    if (isFetching) {
      return (
        <div className="d-flex justify-content-center py-5">
          <span style={{ width: 48, height: 48 }} className="loader" />
        </div>
      )
    }

    if (isError || !surveyLogic) {
      return (
        <p className="text-center py-5">
          {t('The survey logic could not be loaded.')}
        </p>
      )
    }

    return (
      <div
        className="survey-logic-modal-content"
        // Trusted server-rendered HTML (scripts are already stripped by the
        // API endpoint), rendered as-is just like the legacy admin page.
        dangerouslySetInnerHTML={{ __html: surveyLogic.html }}
      />
    )
  }

  return (
    <ComponentModal
      show={show}
      onHide={onHide}
      modalClassname="survey-logic-modal w-100"
      headerClassname="position-absolute end-0"
      componentClassname="survey-logic-modal-body px-4 pb-4"
      Component={renderBody()}
    />
  )
}
