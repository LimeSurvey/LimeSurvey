import { useEffect, useState } from 'react'
import { Button } from 'react-bootstrap'
import classNames from 'classnames'

import { L10ns } from 'helpers'
import { ContentEditor } from 'components/UIComponents'
import { MeatballMenu } from 'components/MeatballMenu/MeatballMenu'
import { ReactComponent as DownArrow } from 'assets/icons/down-arrow.svg'

import { TestValidation } from './QuestionGroupSchema'

export const QuestionGroupHeader = ({
  questionGroup,
  duplicateGroup,
  deleteGroup,
  handleFocusGroup,
  questionGroupTitleRef,
  setShowQuestions,
  showQuestions,
  handleUpdate,
  language,
  questionsLength = 0,
  onErrors,
}) => {
  const [errors, setErrors] = useState('')

  const handleDuplicate = () => {
    duplicateGroup()
  }

  const handleDelete = () => {
    deleteGroup()
  }

  const updateTitle = (groupName) => {
    handleUpdate({ groupName })
  }

  useEffect(() => {
    onErrors(errors)
  }, [errors, onErrors])

  return (
    <>
      <div
        className={classNames('header d-flex justify-content-between mb-2', {
          'error-focus': errors,
        })}
      >
        <div
          className={classNames(
            'title d-flex flex-grow-1 align-items-center gap-1'
          )}
          id={`question-group-${questionGroup.gid}`}
        >
          <Button
            variant="outline"
            onClick={() => setShowQuestions(!showQuestions)}
            className={classNames('p-0 pb-1 transition-all', {
              'rotate-180 ': showQuestions,
            })}
          >
            <DownArrow />
          </Button>
          <ContentEditor
            value={L10ns({
              prop: 'groupName',
              language,
              l10ns: questionGroup.l10ns,
            })}
            update={(groupName) => updateTitle(groupName)}
            placeholder="What's your question group is about?"
            contentEditableRef={questionGroupTitleRef}
            setErrors={setErrors}
            testValidation={TestValidation}
            onClick={handleFocusGroup}
            focus={questionGroup.tempFocusTitle}
          />
          {questionsLength >= 0 ? (
            <span onClick={handleFocusGroup} className="questions-length ms-2">
              {questionsLength}
            </span>
          ) : (
            <></>
          )}
        </div>
        <MeatballMenu
          deleteText={'Delete group'}
          duplicateText={'Duplicate group'}
          handleDelete={handleDelete}
          handleDuplicate={handleDuplicate}
          onClick={handleFocusGroup}
        />
      </div>
    </>
  )
}
