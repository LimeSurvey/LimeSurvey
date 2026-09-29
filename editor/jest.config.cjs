module.exports = {
  testEnvironment: 'jsdom',
  testTimeout: 30000,
  transform: {
    '^.+\\.[j]s?$': 'babel-jest',
  },
  moduleNameMapper: {
    // Stub static asset imports
    '\\.(jpg|jpeg|png|gif|webp)$': '<rootDir>/__mocks__/fileMock.cjs',
    '\\.svg$': '<rootDir>/__mocks__/svgMock.cjs',
    // SVG with ?raw query (Vite/rsbuild) - returns raw string
    '\\.svg\\?raw$': '<rootDir>/__mocks__/rawSvgMock.cjs',
    '\\.(css|less|scss|sass)$': 'identity-obj-proxy',
    '^/(.*)$': '<rootDir>/src/$1',
    'pluginRegistry': '<rootDir>/src/plugins/pluginRegistry',
  },
  transformIgnorePatterns: [
    '/node_modules/(?!(react-leaflet|react-google-maps|dayjs|@mui|@babel/runtime|@react-leaflet|copy-text-to-clipboard)/)',
  ],
  moduleDirectories: ['node_modules', 'src'],
  setupFilesAfterEnv: ['<rootDir>/src/setupTests.js'],
  globalTeardown: '<rootDir>/src/tests/teardown.cjs',
}
