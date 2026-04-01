import { Col, Row } from 'react-bootstrap'
import { LANGUAGE_CODES } from 'helpers/constants'
import { Question } from 'components/Survey/Questions/Question'
import { useState } from 'react'
import { QuestionSettings } from 'components/QuestionSettings/QuestionSettings'

const SURVEY_ID = '78f91e52-6028-11ed-82e1-7ac846e3af9d'

export function QuestionPreview({
  question,
  surveySettings = {},
  showAttributesTogether = true,
}) {
  const [questions, setQuestions] = useState([question])

  const handleQuestionUpdate = (value, index) => {
    const updatedQuestions = [...questions]
    updatedQuestions[index] = {
      ...updatedQuestions[index],
      ...value,
    }
    setQuestions(updatedQuestions)
  }

  const handleDuplicate = (index) => {
    setQuestions([...questions, questions[index]])
  }

  const handleRemove = (index) => {
    if (index === 0) {
      alert("You can't delete the first question")
    } else {
      setQuestions(questions.filter((_, idx) => idx !== index))
    }
  }

  return (
    <div style={{ paddingTop: 64, minHeight: 'calc(100vh - 32px)' }}>
      <Row>
        <Col sm={8} className="mb-2">
          {questions &&
            questions.map((question, index) => (
              <div key={`${question.qid}-${index}`} className="mb-2">
                <Question
                  key={`${question.qid}-${index}`}
                  language={LANGUAGE_CODES.EN}
                  question={question}
                  update={(value) => handleQuestionUpdate(value, index)}
                  handleSwapQuestionPosition={() => {}}
                  handleDuplicate={() => handleDuplicate(index)}
                  handleRemove={() => handleRemove(index)}
                  isTestMode
                  surveySettings={surveySettings}
                />
              </div>
            ))}
        </Col>
        {showAttributesTogether && (
          <Col className="mb-2">
            <QuestionSettings surveyId={SURVEY_ID} />
          </Col>
        )}
      </Row>
    </div>
  )
}
