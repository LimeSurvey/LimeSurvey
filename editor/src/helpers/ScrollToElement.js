import { IsElementOnScreen } from './IsElementOnScreen'

export const ScrollToElement = (element, offset = 125) => {
  if (!element) {
    return
  }

  if (IsElementOnScreen(element)) {
    return
  }

  var elementPosition = element.getBoundingClientRect().top
  var offsetPosition = elementPosition + window.scrollY - offset

  setTimeout(() => {
    window.scrollTo({
      top: offsetPosition,
      behavior: 'smooth',
    })
  }, 1)
}
