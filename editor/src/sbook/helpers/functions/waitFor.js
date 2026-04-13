import { waitFor as storybookWaitFor } from '@storybook/test'

export const waitFor = (callback, options = { timeout: 10000 }) => {
  return storybookWaitFor(callback, { ...options })
}
