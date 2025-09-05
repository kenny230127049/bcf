module.exports = {
  testEnvironment: 'jsdom',
  roots: ['<rootDir>/assets/js'],
  testMatch: [
    '**/__tests__/**/*.js',
    '**/?(*.)+(spec|test).js'
  ],
  collectCoverageFrom: [
    'assets/js/**/*.js',
    '!assets/js/**/*.test.js',
    '!assets/js/**/*.spec.js'
  ],
  coverageDirectory: 'coverage',
  coverageReporters: ['text', 'lcov', 'html'],
  setupFilesAfterEnv: ['<rootDir>/assets/js/setupTests.js'],
  moduleNameMapping: {
    '^@/(.*)$': '<rootDir>/assets/js/$1',
    '^@scss/(.*)$': '<rootDir>/assets/scss/$1',
    '^@images/(.*)$': '<rootDir>/assets/images/$1'
  },
  transform: {
    '^.+\\.js$': 'babel-jest'
  },
  moduleFileExtensions: ['js', 'json'],
  testPathIgnorePatterns: [
    '/node_modules/',
    '/dist/'
  ],
  collectCoverage: false,
  verbose: true
};
