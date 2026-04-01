export const IsElementOnScreen = (
  element,
  bottomOffset = 300,
  topOffset = 200
) => {
  if (!element) {
    return false
  }

  var rect = element.getBoundingClientRect()

  var viewHeight = Math.max(
    document.documentElement.clientHeight,
    window.innerHeight
  )

  return !(
    rect.bottom - bottomOffset < 0 || rect.top + topOffset - viewHeight >= 0
  )
}
