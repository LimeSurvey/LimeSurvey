import classNames from 'classnames'
import { isString } from 'lodash'

import { EditableImage } from 'components/EditableImage/EditableImage'
import { ContentEditor, DropZone } from 'components/UIComponents'
import { CloseCircleFillIcon, DragIcon } from 'components/icons'
import { useParams } from 'react-router-dom'
import { useSurvey, useAppState } from 'hooks'
import { STATES } from 'helpers'
import { SubquestionCodeInput } from '../subquestionCodeComponents'
// Todo: handle switching between image and text using attributes.
export const RankingQuestionAnswer = ({
  answer,
  aid,
  onChange = () => {},
  isFocused,
  provided = {},
  handleRemovingAnswers,
  handleLocalCodeUpdate,
  index,
  code,
}) => {
  const { surveyId } = useParams()
  const { survey } = useSurvey(surveyId)
  const [isSurveyActive] = useAppState(STATES.IS_SURVEY_ACTIVE)

  const handleAnswerUpdate = (value) => {
    onChange(value)
  }

  return (
    <div className="d-flex answer-item align-items-center position-relative remove-option-button-parent">
      <div
        style={{ left: '-20px' }}
        className="cursor-pointer remove-option-button position-absolute "
        onClick={() => handleRemovingAnswers(aid)}
      >
        <CloseCircleFillIcon
          className={classNames('text-danger fill-current', {
            'd-none': !isFocused,
          })}
        />
      </div>
      <div
        {...provided.dragHandleProps}
        className={classNames({
          'disabled opacity-0': !provided.dragHandleProps,
        })}
      >
        <DragIcon className="text-secondary fill-current me-2" />
      </div>
      <div className="d-flex align-items-center ">
        {!answer?.preview && isString(answer) && (
          <div className="d-flex align-items-center gap-3">
            {isFocused && survey.showQNumCode?.showNumber && (
              <SubquestionCodeInput
                isSurveyActive={isSurveyActive}
                code={code}
                onChange={(e) =>
                  handleLocalCodeUpdate(e.target.value, index, false)
                }
              />
            )}
            <ContentEditor
              update={handleAnswerUpdate}
              value={answer}
              placeholder={t('Add text here...')}
            />
          </div>
        )}
        {isFocused && !answer && process.env.REACT_APP_DEV_MODE && (
          <div>{t('OR')}</div>
        )}
        {isFocused &&
          !answer.length &&
          !answer.preview &&
          process.env.REACT_APP_DEV_MODE && (
            <DropZone
              onReaderResult={handleAnswerUpdate}
              image={answer.preview}
            />
          )}
        {answer?.preview && (
          <EditableImage
            update={handleAnswerUpdate}
            imageSrc={answer}
            width={'200px'}
            showControllers={isFocused}
            handleRemoveImage={() => handleAnswerUpdate('')}
          />
        )}
      </div>
    </div>
  )
}
