import { expect } from '@storybook/test'
import { fireEvent } from '@storybook/test'
import { sleep } from 'helpers/sleep'

function canvasImage(src) {
  return new Promise((resolve, reject) => {
    const img = new Image()
    img.src = src
    img.onload = () => {
      const canvas = document.createElement('canvas')
      let w = (canvas.width = img.width)
      let h = (canvas.height = img.height)
      const ctx = canvas.getContext('2d')
      if (!ctx) {
        return
      }
      ctx.drawImage(img, 0, 0, w, h)
      img.onerror = (e) => {
        return reject(e)
      }
      return resolve(canvas)
    }
  })
}

function getCanvasBlob(canvas) {
  return new Promise((resolve, reject) => {
    if (!canvas) {
      return reject(null)
    }
    canvas.toBlob(resolve)
  })
}

export async function upload(input, name, src) {
  const canvas = await canvasImage(src)
  const canvasBlob = await getCanvasBlob(canvas)
  const file = new File([canvasBlob], name)

  fireEvent.change(input, {
    target: { files: [file] },
  })
  await expect(input.files.length).toBe(1)
  await expect(input.files[0].name).toBe(name)
  await sleep()
}
