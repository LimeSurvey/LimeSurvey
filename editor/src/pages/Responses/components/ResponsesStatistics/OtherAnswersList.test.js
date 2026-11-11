import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'

import { OtherAnswersList } from './OtherAnswersList'

global.t = (key) => key

const item = {
  key: 'other',
  value: 10,
  otherAnswers: [
    { text: 'Darts', count: 3 },
    { text: 'Basketball', count: 2 },
    { text: 'Chess', count: 1 },
    { text: 'Climbing', count: 1 },
    { text: 'Disc golf', count: 1 },
    { text: 'Yoga', count: 1 },
  ],
}

const getRowTexts = () =>
  [
    ...document.querySelectorAll('.responses-statistics-other-answers-text'),
  ].map((node) => node.textContent)

describe('OtherAnswersList', () => {
  test('shows the first 5 answers, most frequent first', () => {
    render(<OtherAnswersList item={item} />)

    expect(getRowTexts()).toEqual([
      'Darts',
      'Basketball',
      'Chess',
      'Climbing',
      'Disc golf',
    ])
    expect(screen.getByText('3 responses')).toBeInTheDocument()
    expect(screen.getAllByText('1 response')).toHaveLength(3)
  })

  test('shows the rest, including "No text", after "Show more"', async () => {
    render(<OtherAnswersList item={item} />)

    await userEvent.click(screen.getByRole('button', { name: 'Show more' }))

    // 10 "other" responses, 9 of them with text.
    expect(getRowTexts()).toEqual([
      'Darts',
      'Basketball',
      'Chess',
      'Climbing',
      'Disc golf',
      'Yoga',
      'No text',
    ])
    expect(
      screen.queryByRole('button', { name: 'Show more' })
    ).not.toBeInTheDocument()
  })

  test('"Show more" reveals 50 more rows per click', async () => {
    const otherAnswers = Array.from({ length: 60 }, (_, index) => ({
      text: `Answer ${index + 1}`,
      count: 1,
    }))
    render(
      <OtherAnswersList item={{ key: 'other', value: 60, otherAnswers }} />
    )
    const showMore = () =>
      userEvent.click(screen.getByRole('button', { name: 'Show more' }))

    await showMore()
    expect(getRowTexts()).toHaveLength(55)
    expect(screen.getByText('Answer 6').closest('li')).toHaveFocus()

    await showMore()
    expect(getRowTexts()).toHaveLength(60)
    expect(
      screen.queryByRole('button', { name: 'Show more' })
    ).not.toBeInTheDocument()
  })

  test('has no "Show more" or "No text" row when not needed', () => {
    render(
      <OtherAnswersList
        item={{
          key: 'other',
          value: 2,
          otherAnswers: [{ text: 'Darts', count: 2 }],
        }}
      />
    )

    expect(getRowTexts()).toEqual(['Darts'])
    expect(
      screen.queryByRole('button', { name: 'Show more' })
    ).not.toBeInTheDocument()
  })

  test('links to the comments only when a handler is given', async () => {
    const onViewComments = jest.fn()
    const { rerender } = render(<OtherAnswersList item={item} />)
    expect(
      screen.queryByRole('button', { name: 'View comments' })
    ).not.toBeInTheDocument()

    rerender(<OtherAnswersList item={item} onViewComments={onViewComments} />)
    await userEvent.click(screen.getByRole('button', { name: 'View comments' }))

    expect(onViewComments).toHaveBeenCalledTimes(1)
  })
})
