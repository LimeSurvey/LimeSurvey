const mimeTypes = {
  // Image MIME Types
  jpg: 'image/jpeg',
  jpeg: 'image/jpeg',
  jfif: 'image/jpeg',
  jpe: 'image/jpeg',
  png: 'image/png',
  gif: 'image/gif',
  bmp: 'image/bmp',
  tif: 'image/tiff',
  tiff: 'image/tiff',
  webp: 'image/webp',

  // Text MIME Types
  txt: 'text/plain',
  csv: 'text/csv',
  xml: 'text/xml',
  json: 'application/json',
  html: 'text/html',
  htm: 'text/html',
  css: 'text/css',
  js: 'application/javascript',
  md: 'text/markdown',
  log: 'text/plain',
  sql: 'text/plain',
  py: 'text/x-python',
  java: 'text/x-java-source',
  c: 'text/x-c',
  cpp: 'text/x-c++',

  // Other MIME Types
  pdf: 'application/pdf',
  doc: 'application/msword',
  docx: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
  xls: 'application/vnd.ms-excel',
  xlsx: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
  ppt: 'application/vnd.ms-powerpoint',
  pptx: 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
  // Add more MIME types here
}

export default function categorizeFileTypes(fileTypeList) {
  if (!fileTypeList) {
    return {}
  }
  const files = fileTypeList.split(',')
  const acceptObject = {}

  files.forEach((file) => {
    const extension = file.trim()
    const mimeType = mimeTypes[extension]
    if (!mimeType) {
      return null
    }
    if (!acceptObject[mimeType]) {
      acceptObject[mimeType] = [`.${extension}`]
    } else {
      acceptObject[mimeType].push(`.${extension}`)
    }
  })

  return acceptObject
}
