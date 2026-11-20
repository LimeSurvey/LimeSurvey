import {
  buildImageHtml,
  escapeAttribute,
  fitToMaxSize,
  getOtherSide,
  getSizeError,
  IMAGE_MAX_SIZE,
} from './imageHtml'

beforeAll(() => {
  global.t = (text) => text
})

describe('buildImageHtml', () => {
  test('writes src, alt, width and height with escaped values', () => {
    expect(
      buildImageHtml({
        src: '/upload/surveys/1/images/a "b".png',
        alt: ' Test <b>"quoted"</b> & more ',
        width: 300,
        height: 200,
      })
    ).toBe(
      '<img src="/upload/surveys/1/images/a &quot;b&quot;.png"' +
        ' alt="Test &lt;b&gt;&quot;quoted&quot;&lt;/b&gt; &amp; more"' +
        ' width="300" height="200" class="img-fluid">'
    )
  })

  test('writes an empty alt for decorative images', () => {
    expect(
      buildImageHtml({ src: '/a.png', alt: '', width: 1, height: 1 })
    ).toContain(' alt="" ')
  })
})

test('escapeAttribute escapes single quotes', () => {
  expect(escapeAttribute("it's")).toBe('it&#39;s')
})

describe('fitToMaxSize', () => {
  test('keeps smaller sizes', () => {
    expect(fitToMaxSize(800, 600)).toEqual({
      width: 800,
      height: 600,
      isReduced: false,
    })
  })

  test('scales the longer side down to the maximum, keeping the ratio', () => {
    expect(fitToMaxSize(1600, 1200)).toEqual({
      width: IMAGE_MAX_SIZE,
      height: 900,
      isReduced: true,
    })
    expect(fitToMaxSize(1000, 4000)).toEqual({
      width: 300,
      height: IMAGE_MAX_SIZE,
      isReduced: true,
    })
  })
})

test('getOtherSide keeps the ratio and at least 1 px', () => {
  expect(getOtherSide(600, 'width', 1.5)).toBe(400)
  expect(getOtherSide(400, 'height', 1.5)).toBe(600)
  expect(getOtherSide(1, 'width', 100)).toBe(1)
})

describe('getSizeError', () => {
  test.each(['1', '640', String(IMAGE_MAX_SIZE)])('accepts %s', (value) => {
    expect(getSizeError(value)).toBe('')
  })

  test.each(['', '0', '-5', '1.5', 'abc'])('rejects "%s"', (value) => {
    expect(getSizeError(value)).toBe(
      'Enter a whole number of pixels greater than 0.'
    )
  })

  test('rejects sizes above the maximum', () => {
    expect(getSizeError(String(IMAGE_MAX_SIZE + 1))).toBe(
      `The maximum is ${IMAGE_MAX_SIZE} pixels.`
    )
  })
})
