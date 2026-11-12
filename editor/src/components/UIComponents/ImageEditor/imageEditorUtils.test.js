import {
  applyImageSettings,
  canMoveImage,
  formatFileSize,
  getCropRect,
  getDefaultFrameSize,
  getFrameRatio,
  getVisibleSourceSize,
  resizeFrame,
} from './imageEditorUtils'

describe('imageEditorUtils', () => {
  test('Default frame is the natural size', () => {
    expect(getDefaultFrameSize(400, 300)).toEqual({ width: 400, height: 300 })
    expect(getDefaultFrameSize(1200, 1600)).toEqual({
      width: 1200,
      height: 1600,
    })
    expect(getDefaultFrameSize(20, 10)).toEqual({ width: 20, height: 10 })
    expect(getDefaultFrameSize(0, 0)).toEqual({ width: 300, height: 300 })
  })

  test('Resizing without ratio changes one side, from 0 without upper limit', () => {
    const frame = { width: 400, height: 300 }
    expect(resizeFrame(frame, 'width', 500)).toEqual({
      width: 500,
      height: 300,
    })
    expect(resizeFrame(frame, 'height', -10)).toEqual({ width: 400, height: 0 })
    expect(resizeFrame(frame, 'width', 5000)).toEqual({
      width: 5000,
      height: 300,
    })
  })

  test('Frame ratio is width / height, or null without area', () => {
    expect(getFrameRatio({ width: 400, height: 300 })).toBe(4 / 3)
    expect(getFrameRatio({ width: 0, height: 300 })).toBeNull()
    expect(getFrameRatio(null)).toBeNull()
  })

  test('Resizing with ratio changes both sides to whole pixels', () => {
    const frame = { width: 400, height: 200 }
    expect(resizeFrame(frame, 'width', 600, 2)).toEqual({
      width: 600,
      height: 300,
    })
    expect(resizeFrame(frame, 'height', 600, 2)).toEqual({
      width: 1200,
      height: 600,
    })
    // 4:3 gives 133.33px and 266.67px: Both are rounded.
    expect(resizeFrame(frame, 'width', 100.4, 4 / 3)).toEqual({
      width: 100,
      height: 75,
    })
    expect(resizeFrame(frame, 'width', 100, 3 / 4)).toEqual({
      width: 100,
      height: 133,
    })
    expect(resizeFrame(frame, 'height', 200, 4 / 3)).toEqual({
      width: 267,
      height: 200,
    })
  })

  test('Visible source size depends on frame ratio and zoom', () => {
    // A 4:3 image in a square frame: The sides are cut off.
    expect(
      getVisibleSourceSize(400, 300, { width: 300, height: 300 }, 1)
    ).toEqual({ width: 300, height: 300 })
    // A 4:3 image in a 16:9 frame: Top and bottom are cut off.
    expect(
      getVisibleSourceSize(1600, 1200, { width: 800, height: 450 }, 2)
    ).toEqual({ width: 800, height: 450 })
    // A frame without area shows nothing.
    expect(
      getVisibleSourceSize(400, 300, { width: 0, height: 300 }, 1)
    ).toBeNull()
  })

  test('Crop is the part of the image shown in the frame', () => {
    // A 4:3 image in a square frame: The middle 3/4 of the width.
    expect(getCropRect(400, 300, { width: 100, height: 100 })).toEqual({
      x: 0.125,
      y: 0,
      width: 0.75,
      height: 1,
    })
    // Zoom 2 at the top left corner: The position is kept inside the image.
    expect(getCropRect(400, 300, { width: 100, height: 100 }, 2, 0, 0)).toEqual(
      { x: 0, y: 0, width: 0.375, height: 0.5 }
    )
    expect(getCropRect(400, 300, { width: 0, height: 100 })).toBeNull()
  })

  test('Apply to all keeps the own position and crops for the own size', () => {
    const settings = {
      width: 200,
      height: 100,
      keepRatio: true,
      zoom: 2,
      positionX: 0.1,
      positionY: 0.1,
      radius: 10,
      crop: { x: 0, y: 0, width: 0.5, height: 0.5 },
    }

    expect(
      applyImageSettings(settings, { width: 100, height: 100 }, null)
    ).toEqual({
      width: 200,
      height: 100,
      keepRatio: true,
      zoom: 2,
      positionX: 0.5,
      positionY: 0.5,
      radius: 10,
      // A square image in a 2:1 frame at zoom 2: Half the width, a quarter of the height.
      crop: { x: 0.25, y: 0.375, width: 0.5, height: 0.25 },
    })
    expect(
      applyImageSettings(
        settings,
        { width: 100, height: 100 },
        { positionX: 0.8, positionY: 0.3 }
      )
    ).toEqual(expect.objectContaining({ positionX: 0.8, positionY: 0.3 }))
  })

  test('The image can move when zoomed or cut off by the frame', () => {
    // Same ratio, zoom 1: The whole image is shown.
    expect(canMoveImage(400, 300, { width: 400, height: 300 }, 1)).toBe(false)
    expect(canMoveImage(400, 300, { width: 200, height: 150 }, 1)).toBe(false)
    // Zoomed in.
    expect(canMoveImage(400, 300, { width: 400, height: 300 }, 1.5)).toBe(true)
    // Other ratio at zoom 1: The sides or the top and bottom are cut off.
    expect(canMoveImage(400, 300, { width: 100, height: 100 }, 1)).toBe(true)
    expect(canMoveImage(400, 300, { width: 400, height: 100 }, 1)).toBe(true)
    // A rounded frame size leaves less than a source pixel to move.
    expect(canMoveImage(1000, 334, { width: 300, height: 100 }, 1)).toBe(false)
    // Without the image size, only the zoom counts.
    expect(canMoveImage(undefined, undefined, { width: 1, height: 9 }, 1)).toBe(
      false
    )
    expect(canMoveImage(undefined, undefined, { width: 1, height: 9 }, 2)).toBe(
      true
    )
    // A frame without area has nothing to drag.
    expect(canMoveImage(400, 300, { width: 0, height: 300 }, 2)).toBe(false)
  })

  test('File sizes are formatted with a unit', () => {
    expect(formatFileSize(500)).toBe('500 B')
    expect(formatFileSize(2048)).toBe('2.0 KB')
    expect(formatFileSize(5.8 * 1024 * 1024)).toBe('5.8 MB')
    expect(formatFileSize(null)).toBe('')
  })
})
