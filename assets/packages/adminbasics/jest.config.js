module.exports = {
    moduleFileExtensions: [
        'js',
        'jsx',
        'json',
    ],
    transform: {
        '^.+\\.jsx?$': 'babel-jest',
        '.+\\.(css|styl|less|sass|scss|svg|png|jpg|ttf|woff|woff2)$': 'jest-transform-stub',
    },
    transformIgnorePatterns: ['<rootDir>/node_modules/'],
    moduleNameMapper: {
        '^@/(.*)$': '<rootDir>/src/$1'
    },
    testMatch: [
        '**/tests/**/*.spec.(js|jsx|mjs|ts|tsx)|**/__tests__/*.(js|jsx|mjs|ts|tsx)'
    ],
    testURL: 'http://localhost/',
    watchPlugins: [
        'jest-watch-typeahead/filename',
        'jest-watch-typeahead/testname'
    ],
    collectCoverage: true,
    collectCoverageFrom: [
        '<rootDir>/src/**',
    ],
    coverageReporters: ['lcov', 'text-summary']
};
