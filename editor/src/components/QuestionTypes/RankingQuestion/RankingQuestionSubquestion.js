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
export const RankingQuestionSubquestion = ({
  subquestion,
  qid,
  onChange = () => {},
  isFocused,
  provided = {},
  handleRemovingSubquestions,
  handleCodeUpdate,
  index,
  title,
}) => {
  const { surveyId } = useParams()
  const { survey } = useSurvey(surveyId)
  const [isSurveyActive] = useAppState(STATES.IS_SURVEY_ACTIVE)

  const handleSubquestionUpdate = (value) => {
    onChange(value)
  }

  return (
    <div className="d-flex align-items-center position-relative remove-option-button-parent">
      <div
        style={{ left: '-20px' }}
        className="cursor-pointer remove-option-button position-absolute"
        onClick={() => handleRemovingSubquestions(qid)}
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
        {!subquestion?.preview && isString(subquestion) && (
          <div className="d-flex align-items-center gap-3">
            {isFocused && survey.showQNumCode?.showNumber && (
              <SubquestionCodeInput
                isSurveyActive={isSurveyActive}
                code={title}
                onChange={(e) => handleCodeUpdate(e.target.value, index)}
              />
            )}
            <ContentEditor
              update={handleSubquestionUpdate}
              value={subquestion}
              placeholder={t('Add text here...')}
            />
          </div>
        )}
        {isFocused && !subquestion && process.env.REACT_APP_DEV_MODE && (
          <div>{t('OR')}</div>
        )}
        {isFocused &&
          !subquestion.length &&
          !subquestion.preview &&
          process.env.REACT_APP_DEV_MODE && (
            <DropZone
              onReaderResult={handleSubquestionUpdate}
              image={subquestion.preview}
            />
          )}
        {subquestion?.preview && (
          <EditableImage
            update={handleSubquestionUpdate}
            imageSrc={subquestion}
            width={'200px'}
            showControllers={isFocused}
            handleRemoveImage={() => handleSubquestionUpdate('')}
          />
        )}
      </div>
    </div>
  )
}
