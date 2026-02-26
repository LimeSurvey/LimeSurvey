import classNames from 'classnames'

import { useAppState } from 'hooks'
import { STATES } from 'helpers'

import { AddQuestion } from '../AddQuestion'

export const QuestionGroupFooter = ({ groupIndex }) => {
  const [isSurveyActive] = useAppState(STATES.IS_SURVEY_ACTIVE, false)

  return (
    <div
      className={classNames(
        'add-question',
        'd-flex',
        'justify-content-center',
        'mt-4'
      )}
      style={{ color: isSurveyActive ? '#63c792' : '#14ae5c' }}
    >
      <AddQuestion groupIndex={groupIndex} />
    </div>
  )
}
