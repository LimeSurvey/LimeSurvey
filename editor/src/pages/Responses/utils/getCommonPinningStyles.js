export const getCommonPinningStyles = (column) => {
  const isPinned = column.getIsPinned()
  const isLastLeftPinnedColumn =
    isPinned === 'left' && column.getIsLastColumn('left')

  if (!isPinned) {
    return { position: 'relative' }
  }

  return {
    boxShadow: isLastLeftPinnedColumn && '-4px 0px 2px -4px gray inset',
    left: isPinned === 'left' && `${column.getStart('left') - 35}px`,
    right: isPinned === 'right' && `${column.getAfter('right') - 35}px`,
    opacity: 0.95,
    position: 'sticky',
    width: column.getSize(),
    zIndex: 2,
  }
}
