import {
  DropdownBox,
  MaximumDate,
  MiniumDate,
  MonthDisplayStyle,
} from '../Attributes'

export const DateTimeDisplaySettings = ({ question, handleUpdate }) => (
  <>
    <DropdownBox
      dropdownBox={{ ...question?.attributes?.dropdownBox }}
      update={(changes) =>
        handleUpdate({
          dropdownBox: {
            ...question.attributes?.dropdownBox,
            ...changes,
          },
        })
      }
    />
    {question.attributes.dropdownBox?.value && (
      <div className="mt-3">
        <MonthDisplayStyle
          monthDisplay={{ ...question?.attributes?.monthDisplay }}
          update={(changes) =>
            handleUpdate({
              monthDisplay: {
                ...question.attributes?.monthDisplay,
                ...changes,
              },
            })
          }
        />
      </div>
    )}
    <div className="mt-3">
      <MiniumDate
        minAnswer={{ ...question?.attributes?.minAnswer }}
        update={(changes) =>
          handleUpdate({
            minAnswer: { ...question.attributes?.minAnswer, ...changes },
          })
        }
      />
    </div>
    <div className="mt-3">
      <MaximumDate
        maxAnswer={{ ...question?.attributes?.maxAnswer }}
        update={(changes) =>
          handleUpdate({
            maxAnswer: { ...question.attributes?.maxAnswer, ...changes },
          })
        }
      />
    </div>
  </>
)
