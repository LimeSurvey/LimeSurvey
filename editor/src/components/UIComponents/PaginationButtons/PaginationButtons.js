import { useMemo } from 'react'
import { Button } from '../Buttons'
import { DoubleGreaterThanArrow, GreaterThanArrow } from 'components/icons'
import ReactSelect from 'react-select'

export const PaginationButtons = ({
  OnPageNumberClick = () => {},
  onNextPageClick = () => {},
  onPrevPageClick = () => {},
  onFirstPageClick = () => {},
  onLastPageClick = () => {},
  onPageSizeChange = () => {},
  canGoNextPage = false,
  canGoPrevPage = false,
  currentPageIndex = 0,
  totalPages = 0,
  maxNumberOfButtons = 5,
  pageSize = 10,
}) => {
  const pageNumbers = useMemo(() => {
    const startPage = Math.max(
      1,
      currentPageIndex + 1 - Math.floor(maxNumberOfButtons / 2)
    )
    const endPage = Math.min(totalPages, startPage + (maxNumberOfButtons - 1))

    const pages = []
    for (let i = startPage; i <= endPage; i++) {
      pages.push(i)
    }

    return pages
  }, [currentPageIndex, totalPages, maxNumberOfButtons])

  return (
    <div className="pagination-buttons">
      <Button
        className={`pagination-button arrows ${!canGoPrevPage ? 'disabled' : ''}`}
        variant="outline-dark"
        onClick={onFirstPageClick}
        disabled={!canGoPrevPage}
      >
        <div className="rotate-180">
          <DoubleGreaterThanArrow />
        </div>
      </Button>
      <Button
        className={`pagination-button arrows${!canGoPrevPage ? 'disabled' : ''}`}
        variant="outline-dark"
        onClick={onPrevPageClick}
        disabled={!canGoPrevPage}
      >
        <div className="rotate-180">
          <GreaterThanArrow />
        </div>
      </Button>
      {pageNumbers.map((number) => (
        <Button
          key={`pagination-${number}`}
          onClick={() => OnPageNumberClick(number - 1)}
          className={`pagination-button ${currentPageIndex + 1 === number ? 'active' : ''}`}
          variant="outline-dark"
        >
          {number}
        </Button>
      ))}

      <Button
        className="pagination-button arrows"
        variant="outline-dark"
        onClick={onNextPageClick}
        disabled={!canGoNextPage}
      >
        <GreaterThanArrow />
      </Button>
      <Button
        className="pagination-button arrows"
        variant="outline-dark"
        onClick={onLastPageClick}
        disabled={!canGoNextPage}
      >
        <DoubleGreaterThanArrow />
      </Button>
      <span className="ps-4"> {t('Rows per page')}</span>
      <ReactSelect
        options={[
          { value: 10, label: 10 },
          { value: 20, label: 20 },
          { value: 50, label: 50 },
          { value: 100, label: 100 },
        ]}
        defaultValue={{ value: pageSize, label: pageSize }}
        onChange={({ value }) => onPageSizeChange(value)}
        className="pagination-select"
        menuPlacement="top"
        theme={(theme) => ({
          ...theme,
          colors: {
            ...theme.colors,
            primary: '#8146F6',
          },
        })}
        components={{
          IndicatorSeparator: () => null,
        }}
        styles={{
          control: (baseStyles) => ({
            ...baseStyles,
            fontWeight: 400,
            minHeight: 24,
            minWidth: 60,
            color: '$g-900',
            borderColor: '$g-700',
          }),
          indicatorsContainer: (baseStyles) => ({
            ...baseStyles,
            minHeight: 24,
          }),
          dropdownIndicator: (baseStyles) => ({
            ...baseStyles,
            padding: 0,
            color: '$g-900',
          }),
          option: (baseStyles) => ({
            ...baseStyles,
            whiteSpace: 'normal',
            wordWrap: 'break-word',
            zIndex: 9999,
          }),
          menu: (baseStyles) => ({
            ...baseStyles,
            width: '100%',
            minWidth: 'min-content',
            whiteSpace: 'normal',
            wordWrap: 'break-word',
            color: '$g-900',
            zIndex: 99,
          }),
        }}
      />
    </div>
  )
}
