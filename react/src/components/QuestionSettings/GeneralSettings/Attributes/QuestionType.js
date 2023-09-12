import { Button, Form, OverlayTrigger } from 'react-bootstrap'
import { useState } from 'react'

import { QuestionTypeSelector } from 'components/QuestionTypeSelector'
import { ArrowDownIcon } from 'components/icons'
import { QuestionTypeInfo } from '../../../QuestionTypes'

export const QuestionType = ({ questionThemeName, update }) => {
  const [isAddingQuestionOrGroup, setIsAddingQuestionOrGroup] = useState(false)

  const questionTypeSelectorPopover = (
    <div
      className="question-type-selector-container"
      style={{
        width: 280,
        borderRadius: '2px',
        border: ' 1px solid #1e1e1e',
        boxShadow: '0px 0px 2px 0px black',
        background: 'white',
        fontSize: '14px',
      }}
    >
      <QuestionTypeSelector callBack={(type) => update({ ...type })} />
    </div>
  )

  const questionTypeKey = Object.keys(QuestionTypeInfo).find(
    (key) => QuestionTypeInfo[key].theme === questionThemeName
  )

  const questionTypeTitle = QuestionTypeInfo[questionTypeKey]?.title

  return (
    <div className="question-type-general-settings">
      <Form.Label className="d-block">Type</Form.Label>
      <OverlayTrigger
        trigger="click"
        offset={[0, 4]}
        placement={'bottom'}
        overlay={questionTypeSelectorPopover}
        show={isAddingQuestionOrGroup}
        onToggle={(show) => {
          setIsAddingQuestionOrGroup(show)
        }}
        rootClose
      >
        <Button variant="outline-dark w-100 d-flex align-items-center justify-content-between overlay-trigger-button">
          <div className="text-start">{questionTypeTitle}</div>
          <div>
            <ArrowDownIcon />
          </div>
        </Button>
      </OverlayTrigger>
    </div>
  )
}
