// Parses the filename out of a Content-Disposition: attachment; filename="..." header
export function getFilenameFromContentDisposition(contentDisposition, fallback) {
  const match = /filename="?([^"]+)"?/i.exec(contentDisposition || '')
  return match ? match[1] : fallback
}

export function downloadBlob(blob, filename) {
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = filename
  document.body.appendChild(link)
  link.click()
  link.remove()
  URL.revokeObjectURL(url)
}
