/**
 * How many chips fit on one line. When they don't all fit, room is kept for
 * the "Show more" button after the last one. At least one chip always shows:
 * a chip is capped in width, so it is cut off rather than wrapped.
 *
 * @param {number[]} chipWidths Width of each chip, in order.
 * @param {number} availableWidth Width of the line.
 * @param {number} toggleWidth Width of the "Show more" button.
 * @param {number} gap Space between two items on the line.
 * @returns {number}
 */
export const countChipsOnOneLine = (
  chipWidths,
  availableWidth,
  toggleWidth,
  gap
) => {
  const totalWidth = chipWidths.reduce(
    (sum, width, index) => sum + width + (index ? gap : 0),
    0
  )
  if (totalWidth <= availableWidth) {
    return chipWidths.length
  }

  let usedWidth = 0
  let count = 0
  for (const width of chipWidths) {
    const nextWidth = usedWidth + (count ? gap : 0) + width
    if (nextWidth + gap + toggleWidth > availableWidth) {
      break
    }
    usedWidth = nextWidth
    count += 1
  }

  return Math.max(count, 1)
}
