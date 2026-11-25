// Import shared mocks
import 'tests/mocks'

import { renderWithProviders } from 'tests/testUtils'
import { QuestionTypeSelector } from './'
import { getQuestionItemsList } from './constants'
import { screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import questionTypesData from 'helpers/data/question-types.json'

// Tests run in demo mode, where useQuestionTypes returns this fixture
const offeredItems = getQuestionItemsList(
  questionTypesData.categories,
  questionTypesData.questionTypes
).flatMap((category) => category.items)

describe('getQuestionItemsList', () => {
  const categories = [
    { name: 'first', title: 'First', icon: 'TableIcon' },
    { name: 'second', title: 'Second', icon: 'UnknownIcon' },
  ]
  const makeType = (name, props) => ({
    name,
    type: 'L',
    title: name,
    category: 'first',
    sortOrder: 0,
    devOnly: false,
    visible: true,
    ...props,
  })

  test('Should group the themes by category in their order', () => {
    const list = getQuestionItemsList(categories, {
      bootstrap_buttons: makeType('bootstrap_buttons', { sortOrder: 1 }),
      listradio: makeType('listradio', { title: 'List' }),
      yesno: makeType('yesno', { type: 'Y', category: 'second' }),
    })

    expect(list.map((category) => category.title)).toEqual(['First', 'Second'])
    expect(list[0].icon).not.toBeNull()
    expect(list[1].icon).toBeNull()
    expect(list[0].items).toEqual([
      { value: 'L', label: 'List', theme: 'listradio' },
      { value: 'L', label: 'bootstrap_buttons', theme: 'bootstrap_buttons' },
    ])
    expect(list[1].items.map((item) => item.theme)).toEqual(['yesno'])
  })

  test('Should leave out hidden, unlisted and not renderable themes', () => {
    const list = getQuestionItemsList(categories, {
      listradio: makeType('listradio', { visible: false }),
      hugefreetext: makeType('hugefreetext', { type: 'U', category: null }),
      my_custom_theme: makeType('my_custom_theme'),
      // A custom theme reusing a core theme name for another question type
      bootstrap_buttons: makeType('bootstrap_buttons', { type: 'M' }),
      yesno: makeType('yesno', { type: 'Y' }),
    })

    expect(list[0].items.map((item) => item.theme)).toEqual(['yesno'])
  })

  test('Should offer the dev-only themes only in dev mode', () => {
    const devMode = process.env.REACT_APP_DEV_MODE
    const questionTypes = {
      ranking: makeType('ranking', { type: 'R', devOnly: true }),
    }

    try {
      delete process.env.REACT_APP_DEV_MODE
      expect(getQuestionItemsList(categories, questionTypes)[0].items).toEqual(
        []
      )

      process.env.REACT_APP_DEV_MODE = 'true'
      expect(
        getQuestionItemsList(categories, questionTypes)[0].items.map(
          (item) => item.theme
        )
      ).toEqual(['ranking'])
    } finally {
      if (devMode === undefined) {
        delete process.env.REACT_APP_DEV_MODE
      } else {
        process.env.REACT_APP_DEV_MODE = devMode
      }
    }
  })
})

describe('QuestionTypeSelector', () => {
  let selectedType

  beforeEach(async () => {
    selectedType = undefined
    await renderWithProviders(
      <QuestionTypeSelector
        callBack={(value) => {
          selectedType = value.type
        }}
      />
    )
  })

  test('Should use the labels exactly one time', async () => {
    expect(offeredItems.length).toBeGreaterThan(0)
    for (const item of offeredItems) {
      const questionTypeInputs = screen.getAllByTestId(
        `question-type-${item.theme}`
      )

      expect(questionTypeInputs.length).toBe(1)
    }
  })

  test('Should be mapping the labels to the correct values', async () => {
    for (const item of offeredItems) {
      const questionTypeInput = screen.getByTestId(
        `question-type-${item.theme}`
      )
      await userEvent.click(questionTypeInput)
      expect(selectedType).toBe(item.value)
    }
  })

  test('should be able to search for the text questions', async () => {
    const questionTypeSearchInput = screen.getByTestId('question-type-search')
    await userEvent.type(questionTypeSearchInput, 'text')

    const questionTypeInputs = screen.getAllByTestId(
      `question-type-selector-label`,
      {
        exact: false,
      }
    )
    expect(questionTypeInputs.length).toBe(5)
    questionTypeInputs.forEach((questionInput) => {
      expect(questionInput.innerHTML.toLocaleLowerCase().includes('text')).toBe(
        true
      )
    })
  })

  test('should be able to search with capital letters', async () => {
    const questionTypeSearchInput = screen.getByTestId('question-type-search')
    await userEvent.clear(questionTypeSearchInput)

    await userEvent.type(questionTypeSearchInput, 'TEXT')
    const questionTypeInputs = screen.getAllByTestId(
      `question-type-selector-label`,
      {
        exact: false,
      }
    )

    expect(questionTypeInputs.length).toBe(5)
    questionTypeInputs.forEach((questionInput) => {
      expect(questionInput.innerHTML.toLocaleLowerCase().includes('text')).toBe(
        true
      )
    })
  })

  test('Should focus the labels when using the up/down arrow keys', async () => {
    const questionTypeInputs = screen.getAllByTestId(
      `question-type-selector-label`,
      {
        exact: false,
      }
    )

    questionTypeInputs.forEach((input) => {
      expect(input.parentElement.classList.contains('focus-element')).toBe(
        false
      )
    })

    await userEvent.keyboard('[ArrowDown]')
    expect(
      questionTypeInputs[0].parentElement.classList.contains('focus-element')
    ).toBe(true)

    await userEvent.keyboard('[ArrowDown]')
    expect(
      questionTypeInputs[1].parentElement.classList.contains('focus-element')
    ).toBe(true)
    expect(
      questionTypeInputs[0].parentElement.classList.contains('focus-element')
    ).toBe(false)
  })
})
