import { expect } from '@storybook/test'

export async function ratingTests(step, canvas) {
  const container = canvas.getByTestId('rating-question')
  const icons = canvas.getAllByTestId('rate-question-star-div')

  await step('Should render RatingQuestion correctly', async () => {
    expect(container).toBeInTheDocument()
    expect(icons.length).toBe(5)
  })
}
