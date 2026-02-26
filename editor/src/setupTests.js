/* eslint-disable no-console */
// jest-dom adds custom jest matchers for asserting on DOM nodes.
// allows you to do things like:
// expect(element).toHaveTextContent(/react/i)
// learn more: https://github.com/testing-library/jest-dom
import '@testing-library/jest-dom'
import { TextEncoder, TextDecoder } from 'util'

global.TextEncoder = TextEncoder
global.TextDecoder = TextDecoder

globalThis.t = () => {}
globalThis.st = () => {}

global.IntersectionObserver = class {
  constructor() {}
  observe() {}
  unobserve() {}
  disconnect() {}
}

// Mock ResizeObserver
global.ResizeObserver = class {
  constructor() {}
  observe() {}
  unobserve() {}
  disconnect() {}
}

window.scrollTo = jest.fn()
window.matchMedia = jest.fn()

jest.mock('sweetalert2-react-content', () => {
  return {
    __esModule: true,
    default: () => ({
      fire: jest.fn(() =>
        Promise.resolve({
          isConfirmed: true, // simulate clicking "OK", since sweetalert2 is not being rendered correctly in tests.
        })
      ),
    }),
  }
})

const originalError = console.error

// todo: fix the act issue (it's not important to fix, but it's a good practice to have)
console.error = jest.fn((message, ...args) => {
  if (
    typeof message === 'string' &&
    message.includes('inside a test was not wrapped in act')
  ) {
    return
  }
  originalError(message, ...args)
})
