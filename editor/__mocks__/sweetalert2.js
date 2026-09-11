export default {
  fire: jest.fn(() => Promise.resolve({})),
  close: jest.fn(),
  mixin: jest.fn(() => ({
    fire: jest.fn(() => Promise.resolve({})),
  })),
}
