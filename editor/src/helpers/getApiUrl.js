import getBasePath from './getBasePath'

export const getApiUrl = (
  relativeUrl = null,
  { version = 'v1', baseUrl = process.env.REACT_APP_API_BASE_URL } = {}
) => {
  if (!baseUrl) {
    const host = window.location.protocol + '//' + window.location.host
    const pathPrefix = getBasePath()
    baseUrl = host + pathPrefix + '/rest'
  }
  let parts = [baseUrl]
  if (version) parts.push(version)
  if (relativeUrl) parts.push(relativeUrl)

  return parts.join('/')
}

export default getApiUrl
