export function downloadCanvasAsImage(
  canvas,
  filename = 'QRCode.png',
  type = 'image/png'
) {
  if (!canvas) return

  const dataURL = canvas.toDataURL(type)
  const link = document.createElement('a')
  link.href = dataURL
  link.download = filename
  link.click()
}
