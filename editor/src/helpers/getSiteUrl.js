export const getSiteUrl = (path = '') => {
  let baseUrl = process.env.REACT_APP_SITE_URL
  if (!baseUrl || baseUrl === '/') {
    const host = window.location.protocol + '//' + window.location.host
    baseUrl = host
  }

  return baseUrl + path
}

export default getSiteUrl
