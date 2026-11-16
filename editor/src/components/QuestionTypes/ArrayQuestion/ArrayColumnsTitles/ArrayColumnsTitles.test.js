import 'tests/mocks'

import { act } from 'react'
import { fireEvent, screen } from '@testing-library/react'
import { renderWithProviders } from 'tests/testUtils'
import { WithCodesShown } from 'tests/surveyEditing'
import { Entities, SCALE_1, SCALE_2 } from 'helpers'
import { getQuestionTypeInfo } from 'components/QuestionTypes/getQuestionTypeInfo'

import { ArrayColumnsTitles } from './ArrayColumnsTitles'

const answer = (aid, code, scaleId) => ({
  aid,
  code,
  scaleId,
  l10ns: { en: { answer: code, language: 'en' } },
})

const answers = [
  answer(11, 'A1', SCALE_1),
  answer(12, 'A2', SCALE_1),
  answer(21, 'B1', SCALE_2),
  answer(22, 'B2', SCALE_2),
]

describe('ArrayColumnsTitles of a dual scale array', () => {
  test.each([
    ['first', SCALE_1, 'A2', 1],
    ['second', SCALE_2, 'B2', 3],
  ])(
    'recoding an answer of the %s scale passes the answers of both scales',
    async (_scaleName, scaleId, code, childIndex) => {
      const handleChildCodeUpdate = jest.fn()
      await renderWithProviders(
        <WithCodesShown>
          <ArrayColumnsTitles
            question={{
              qid: 5,
              answers,
              subquestions: [],
              attributes: {},
              questionThemeName: getQuestionTypeInfo().ARRAY_DUAL_SCALE.theme,
            }}
            isFocused={true}
            highestSubquestionWidth={0}
            dragIconSize={22}
            highestHeight={0}
            isArrayDualScale={true}
            scaleId={scaleId}
            arrayAnswersWidth={[]}
            setArrayAnswersWidth={jest.fn()}
            setNumberOfHorizontalEntities={jest.fn()}
            headersHeight={[0, 0]}
            handleChildCodeUpdate={handleChildCodeUpdate}
          />
        </WithCodesShown>
      )
      await act(async () => {
        await new Promise((resolve) => setTimeout(resolve, 0))
      })

      fireEvent.change(
        screen.getByRole('textbox', { name: `Answer code ${code}` }),
        { target: { value: 'C9' } }
      )

      expect(handleChildCodeUpdate).toHaveBeenCalledWith(
        expect.objectContaining({
          newCode: 'C9',
          childIndex,
          childArray: answers,
          entityType: Entities.answer,
        })
      )
    }
  )
})
