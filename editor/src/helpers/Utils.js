export const formatUrlPreview = (url, maxLength) => {
  const cleanUrl = url.replace(/(^\w+:|^)\/\//, '') // Removes protocol
  if (cleanUrl.length <= maxLength) return cleanUrl

  const halfLength = Math.floor((maxLength - 3) / 2) /// Half of the remaining length for the ellipsis ( 3 is for ... )
  return cleanUrl.slice(0, halfLength) + '...' + cleanUrl.slice(-halfLength)
}
