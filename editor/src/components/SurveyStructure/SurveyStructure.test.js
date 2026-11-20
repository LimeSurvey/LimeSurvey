// Import shared mocks
import 'tests/mocks'

import { act } from 'react'
import { renderWithProviders } from 'tests/testUtils'
import { SurveyStructure } from './SurveyStructure'
import { screen, waitFor, within } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import surveyData from 'helpers/data/survey-detail.json'
import { queryClient } from 'queryClient'
import { STATES } from 'helpers'

// Wrap DragDropContext so tests can trigger its drag callbacks directly
let mockDragDropContextProps
jest.mock('@hello-pangea/dnd', () => {
  const actual = jest.requireActual('@hello-pangea/dnd')
  const { createElement } = jest.requireActual('react')

  return {
    ...actual,
    DragDropContext: (props) => {
      mockDragDropContextProps = props
      return createElement(actual.DragDropContext, props)
    },
  }
})

// React Query notifies subscribers on a setTimeout(0), so wait a tick for the re-render
const setAppState = (key, value) =>
  act(async () => {
    queryClient.setQueryData(['appState', key], value)
    await new Promise((resolve) => setTimeout(resolve, 0))
  })

const getAllGroupsContainer = () =>
  document.querySelector('[data-rfd-droppable-id="all-groups"]')

const getGroupRows = () =>
  screen.getAllByTestId('survey-structure-question-group')

const dropItem = (type, droppableId, fromIndex, toIndex) =>
  act(() => {
    mockDragDropContextProps.onDragEnd({
      type,
      source: { droppableId, index: fromIndex },
      destination: { droppableId, index: toIndex },
    })
  })

describe('SurveyStructure', () => {
  const Survey = surveyData.survey

  afterEach(async () => {
    await setAppState(STATES.IS_SURVEY_ACTIVE, false)
    await setAppState(STATES.HAS_SURVEY_UPDATE_PERMISSION, true)
  })

  test('Should have the Header and Footer', async () => {
    await renderWithProviders(<SurveyStructure />)

    const header = await screen.findByTestId('survey-structure-header')
    const footer = await screen.findByTestId('survey-structure-footer')

    expect(header).toBeDefined()
    expect(footer).toBeDefined()
  })

  test('Welcome row is selected by click and by keyboard', async () => {
    await renderWithProviders(<SurveyStructure />)

    const header = await screen.findByTestId('survey-structure-header')
    const footer = await screen.findByTestId('survey-structure-footer')
    expect(header).not.toHaveAttribute('aria-current')

    header.focus()
    await userEvent.keyboard('{Enter}')
    await waitFor(() => expect(header).toHaveAttribute('aria-current', 'true'))

    await userEvent.click(footer)
    await waitFor(() => expect(footer).toHaveAttribute('aria-current', 'true'))
    expect(header).not.toHaveAttribute('aria-current')
  })

  test('Should have all of the survey question groups', async () => {
    await renderWithProviders(<SurveyStructure />)

    const questionGroups = await screen.findAllByTestId(
      'survey-structure-question-group'
    )

    expect(questionGroups.length).toBe(Survey?.questionGroups.length)
  })

  test('Should use the default cursor on the groups container', async () => {
    await renderWithProviders(<SurveyStructure />)

    expect(getAllGroupsContainer()).toHaveStyle({ cursor: 'auto' })
  })

  test('Should show not-allowed cursor without survey update permission', async () => {
    await renderWithProviders(<SurveyStructure />)
    await setAppState(STATES.HAS_SURVEY_UPDATE_PERMISSION, false)

    expect(getAllGroupsContainer()).toHaveStyle({ cursor: 'not-allowed' })
  })

  test('Should show not-allowed cursor while dragging a question out of its group on an active survey', async () => {
    await renderWithProviders(<SurveyStructure />)
    await setAppState(STATES.IS_SURVEY_ACTIVE, true)

    act(() => {
      mockDragDropContextProps.onDragUpdate({
        source: { droppableId: 'g0', index: 0 },
        destination: { droppableId: 'g1', index: 0 },
      })
    })

    expect(getAllGroupsContainer()).toHaveStyle({ cursor: 'not-allowed' })

    act(() => {
      mockDragDropContextProps.onDragEnd({
        source: { droppableId: 'g0', index: 0 },
        destination: null,
      })
    })

    expect(getAllGroupsContainer()).toHaveStyle({ cursor: 'auto' })
  })

  test('A group expanded by hand stays expanded after it is moved', async () => {
    await renderWithProviders(<SurveyStructure />)
    const movedGroupName = Survey.questionGroups[2].l10ns.en.groupName
    const toggler = within(getGroupRows()[2]).getByTestId(
      'sidebar-row-toggler-button'
    )
    await userEvent.click(toggler)
    expect(toggler).toHaveAttribute('aria-expanded', 'true')

    await dropItem('group', 'all-groups', 2, 0)

    const firstRow = getGroupRows()[0]
    expect(firstRow).toHaveTextContent(movedGroupName)
    expect(
      within(firstRow).getByTestId('sidebar-row-toggler-button')
    ).toHaveAttribute('aria-expanded', 'true')

    await dropItem('group', 'all-groups', 0, 2)
  })

  test('Reordering questions moves the existing rows instead of rebuilding them', async () => {
    await renderWithProviders(<SurveyStructure />)
    await userEvent.click(
      within(getGroupRows()[0]).getByTestId('sidebar-row-toggler-button')
    )
    const getQuestionRows = () =>
      within(
        document.querySelector('[data-rfd-droppable-id="g0"]')
      ).getAllByTestId('sidebar-row-question')
    const [firstRow] = getQuestionRows()

    await dropItem('question', 'g0', 0, 2)

    expect(getQuestionRows()[2]).toBe(firstRow)

    await dropItem('question', 'g0', 2, 0)
  })
})
