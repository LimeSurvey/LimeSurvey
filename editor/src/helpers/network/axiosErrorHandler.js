import { addBreadcrumb } from 'appInstrumentation'

/**
 * Error handler for Axios requests
 * @param {Error} error - The error object from Axios
 * @param {Object} options - Additional options for error handling
 * @param {boolean} options.throwError - Whether to throw the error after handling (default: false)
 * @param {boolean} options.logToConsole - Whether to log errors to console (default: true)
 * @returns {Object} - Normalized error object with code, message, and httpStatus
 */
export const handleAxiosError = (error, options = {}) => {
  const { throwError = false, logToConsole = true } = options

  const normalizedError = {
    code: 'UNKNOWN_ERROR',
    message: 'An unknown error occurred',
    httpStatus: 500,
    originalError: error,
  }

  if (error.name === 'CanceledError') {
    return
  }

  if (error.response) {
    const { status, data } = error.response

    switch (status) {
      case 400:
        normalizedError.httpStatus = 400
        normalizedError.code = 'BAD_REQUEST'
        break
      case 401:
        normalizedError.httpStatus = 401
        normalizedError.code = 'UNAUTHORIZED'
        break
      case 403:
        normalizedError.httpStatus = 403
        normalizedError.code = 'FORBIDDEN'
        break
      case 404:
        normalizedError.httpStatus = 404
        normalizedError.code = 'NOT_FOUND'
        break
      default:
        normalizedError.httpStatus = status
        normalizedError.code = 'SERVER_ERROR'
    }

    if (data && data.error) {
      if (data.error.code) {
        normalizedError.code = data.error.code
      }
      if (data.error.message) {
        normalizedError.message = data.error.message
      }
    }
  } else if (error.request) {
    // Request was made but no response received
    normalizedError.code = 'NO_RESPONSE'
    normalizedError.message = 'No response received from server'
    normalizedError.httpStatus = 0
  } else {
    // Error in setting up the request
    normalizedError.code = 'REQUEST_SETUP_ERROR'
    normalizedError.message = error.message || 'Error setting up the request'
    normalizedError.httpStatus = 0
  }

  addBreadcrumb({
    category: 'axios',
    message: `API Error: ${normalizedError.message}`,
    data: {
      code: normalizedError.code,
      httpStatus: normalizedError.httpStatus,
      url: error.config?.url || 'unknown',
      method: error.config?.method || 'unknown',
      originalError: JSON.stringify(normalizedError.originalError),
    },
    level: 'warning',
  })

  if (logToConsole) {
    // eslint-disable-next-line no-console
    console.error('API Error:', normalizedError)
  }

  if (throwError) {
    const err = new Error(normalizedError.message)
    err.name = normalizedError.code
    err.httpStatus = normalizedError.httpStatus
    err.originalError = normalizedError.originalError
    throw err
  }

  return normalizedError
}
