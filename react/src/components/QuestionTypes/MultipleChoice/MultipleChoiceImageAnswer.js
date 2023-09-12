import { FormCheck, Image } from 'react-bootstrap'
import NoImageFound from 'assets/images/no-image-found.jpg'
import { DropZone } from 'components/UIComponents'

export const MultipleChoiceImageAnswer = ({
  isFocused,
  answer,
  onChange = () => {},
  value,
}) => {
  return (
    <div className="pe-4">
      <div className="mb-2 d-flex gap-2">
        {!isFocused && (
          <>
            <FormCheck
              type={'checkbox'}
              className="pointer-events-none"
              name={`${answer?.qid}-radio-list`}
              data-testid="multiple-choice-image-answer"
            />
            <div className="border border-3 border-secondary rounded">
              <Image
                src={value ? value : NoImageFound}
                alt="Image Select List"
                width={'200px'}
                height={'150px'}
                style={{
                  backgroundSize: 'cover',
                }}
              />
            </div>
          </>
        )}
        {isFocused && (
          <DropZone
            onReaderResult={(result) => onChange(result)}
            image={answer.assessmentValue}
          />
        )}
      </div>
    </div>
  )
}
