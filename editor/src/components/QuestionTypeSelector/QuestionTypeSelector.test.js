// Import shared mocks
import 'tests/mocks'

import { renderWithProviders } from 'tests/testUtils'
import { QuestionTypeSelector } from './'
import { screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { getQuestionTypeInfo } from 'components/QuestionTypes/getQuestionTypeInfo'

describe('QuestionTypeSelector', () => {
  let selectedType

  beforeEach(async () => {
    await renderWithProviders(
      <QuestionTypeSelector
        callBack={(value) => {
          selectedType = value.type
        }}
      />
    )
  })

  test('Should use the labels exactly one time', async () => {
    for (const key of Object.keys(getQuestionTypeInfo())) {
      if (!getQuestionTypeInfo()[key].isQuestionType) {
        continue
      }

      const questionTypeInputs = screen.getAllByTestId(
        `question-type-${getQuestionTypeInfo()[key].theme}`
      )

      expect(questionTypeInputs.length).toBe(1)
    }
  })

  test('Should be mapping the labels to the correct values', async () => {
    for (const key of Object.keys(getQuestionTypeInfo())) {
      if (!getQuestionTypeInfo()[key].isQuestionType) {
        continue
      }

      const questionTypeInput = screen.getByTestId(
        `question-type-${getQuestionTypeInfo()[key].theme}`
      )
      await userEvent.click(questionTypeInput)
      expect(selectedType).toBe(getQuestionTypeInfo()[key].type)
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

describe('QuestionTypeSelector keyboard navigation', () => {
  const devMode = process.env.REACT_APP_DEV_MODE
  let selected

  /**
   * Returns the question type entries in the order of the keyboard navigation (the DOM order).
   * @returns {HTMLElement[]}
   */
  const getItems = () =>
    Array.from(
      document.querySelectorAll('.question-type-selector .list-group-item')
    ).filter((item) =>
      item.getAttribute('data-testid')?.startsWith('question-type-')
    )

  beforeAll(() => {
    // Like a production build, where the dev-only question types are hidden
    delete process.env.REACT_APP_DEV_MODE
  })

  afterAll(() => {
    if (devMode !== undefined) {
      process.env.REACT_APP_DEV_MODE = devMode
    }
  })

  beforeEach(async () => {
    selected = undefined
    await renderWithProviders(
      <QuestionTypeSelector
        callBack={(value) => {
          selected = value
        }}
      />
    )
  })

  test('Should highlight exactly one question type in all rows', async () => {
    const items = getItems()
    expect(items.length).toBeGreaterThan(15)

    for (let index = 0; index < items.length; index++) {
      await userEvent.keyboard('[ArrowDown]')
      const highlighted = items.filter((item) =>
        item.classList.contains('focus-element')
      )
      expect(highlighted).toEqual([items[index]])
    }
  })

  test('Should add the highlighted question type on Enter', async () => {
    // Go to the first entry of the second row of categories
    const firstRowCount = document.querySelectorAll(
      '.question-type-selector .row:first-child .list-group-item'
    ).length
    for (let index = 0; index <= firstRowCount; index++) {
      await userEvent.keyboard('[ArrowDown]')
    }
    const highlighted = getItems().filter((item) =>
      item.classList.contains('focus-element')
    )
    expect(highlighted.length).toBe(1)

    await userEvent.keyboard('[Enter]')

    expect(`question-type-${selected?.questionThemeName}`).toBe(
      highlighted[0].getAttribute('data-testid')
    )
  })

  test('Should not offer the hidden question types', async () => {
    expect(screen.queryByTestId('question-type-ranking')).toBeNull()
    expect(screen.queryByTestId('question-type-rating')).toBeNull()
    expect(
      getItems().filter((item) => item.classList.contains('d-none'))
    ).toEqual([])
  })
})
