import {
  getImageChoiceAlt,
  getImageChoiceSrc,
  setImageChoiceImage,
} from 'helpers'

describe('getImageChoiceSrc', () => {
  test('returns the image URL of an HTML image', () => {
    expect(
      getImageChoiceSrc(
        '<p><img alt="Red car" src="/upload/a&amp;b.jpeg" /></p>'
      )
    ).toBe('/upload/a&b.jpeg')
  })

  test('returns a bare image URL as it is', () => {
    expect(getImageChoiceSrc('/upload/car.jpeg')).toBe('/upload/car.jpeg')
  })

  test('returns null without an image', () => {
    expect(getImageChoiceSrc('<b>Red car</b>')).toBeNull()
    expect(getImageChoiceSrc('')).toBeNull()
    expect(getImageChoiceSrc(undefined)).toBeNull()
  })
})

describe('getImageChoiceAlt', () => {
  test('returns the alternative text of an HTML image', () => {
    expect(
      getImageChoiceAlt('<img alt="Red car" src="/upload/car.jpeg" />')
    ).toBe('Red car')
  })

  test('returns an empty string for a bare image URL', () => {
    expect(getImageChoiceAlt('/upload/car.jpeg')).toBe('')
  })
})

describe('setImageChoiceImage', () => {
  test('creates an image from an empty text', () => {
    expect(setImageChoiceImage('', { src: '/upload/car.jpeg' })).toBe(
      '<img src="/upload/car.jpeg" alt="">'
    )
  })

  test('turns a bare image URL into an image', () => {
    expect(setImageChoiceImage('/upload/car.jpeg', { alt: 'Red car' })).toBe(
      '<img src="/upload/car.jpeg" alt="Red car">'
    )
  })

  test('changes the image and keeps the other content', () => {
    expect(
      setImageChoiceImage(
        '<p><img alt="Red car" src="/upload/old.jpeg"> Red</p>',
        {
          src: '/upload/new.jpeg',
        }
      )
    ).toBe('<p><img alt="Red car" src="/upload/new.jpeg"> Red</p>')
  })

  test('escapes the alternative text', () => {
    expect(
      setImageChoiceImage('<img src="/upload/car.jpeg" alt="">', {
        alt: 'Cars & "trucks"',
      })
    ).toBe('<img src="/upload/car.jpeg" alt="Cars &amp; &quot;trucks&quot;">')
  })
})
