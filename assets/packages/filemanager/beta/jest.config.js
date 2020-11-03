module.exports = {
    moduleFileExtensions: [
        'js',
        'jsx',
        'json',
        'vue'
    ],
    transform: {
        '^.+\\.vue$': 'vue-jest',
        '.+\\.(css|styl|less|sass|scss|svg|png|jpg|ttf|woff|woff2)$': 'jest-transform-stub',
        '^.+\\.jsx?$': 'babel-jest'
    },
    transformIgnorePatterns: [
        '/node_modules/'
    ],
    moduleNameMapper: {
        '^@/(.*)$': '<rootDir>/src/$1'
    },
    snapshotSerializers: [
        'jest-serializer-vue'
    ],
    testMatch: [
        '**/tests/unit/**/*.spec.(js|jsx|ts|tsx)|**/__tests__/*.(js|jsx|ts|tsx)'
    ],
    testURL: 'http://localhost/',
    watchPlugins: [
        'jest-watch-typeahead/filename',
        'jest-watch-typeahead/testname'
    ],
    collectCoverage: true,
    collectCoverageFrom: [
        '**/*.{vue}', 
        '!**/node_modules/**', 
        '!<rootDir>/build/**', 
        '!<rootDir>/build.min/**', 
        '!<rootDir>/src/mixins/**', 
        '!<rootDir>/src/store/**', 
        '!<rootDir>/tests/mocks/**',
        '!<rootDir>/tests/unit/**'
    ],
    coverageReporters: ['lcov', 'text-summary']
}
