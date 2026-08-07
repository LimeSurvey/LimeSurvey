export const getSiteUrl = (path = '', needPrefix = true) => {
  let baseUrl = process.env.REACT_APP_SITE_URL
  if (!baseUrl || baseUrl === '/') {
    const host = window.location.protocol + '//' + window.location.host
    const pathPrefix = window.location.pathname.split('/editor')[0]
    baseUrl = needPrefix ? host + pathPrefix : host
  }

  return baseUrl + path
}

export default getSiteUrl
