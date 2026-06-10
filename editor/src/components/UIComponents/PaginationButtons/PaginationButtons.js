import { useMemo } from 'react'
import { format } from 'util'
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
  totalResults,
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
      <div className="pagination-buttons__content">
        {totalResults != null && (
          <span className="pagination-buttons__results">
            {format(t('%s results'), totalResults)}
          </span>
        )}
        <div className="pagination-buttons__nav">
          <Button
            className={`pagination-button arrows arrows-left ${!canGoPrevPage ? 'disabled' : ''}`}
            variant="outline-dark"
            onClick={onFirstPageClick}
            disabled={!canGoPrevPage}
          >
            <DoubleGreaterThanArrow />
          </Button>
          <Button
            className={`pagination-button arrows arrows-left ${!canGoPrevPage ? 'disabled' : ''}`}
            variant="outline-dark"
            onClick={onPrevPageClick}
            disabled={!canGoPrevPage}
          >
            <GreaterThanArrow />
          </Button>
          {pageNumbers.map((number) => (
            <Button
              key={`pagination-${number}`}
              onClick={() => OnPageNumberClick(number - 1)}
              className={`pagination-button page-number ${currentPageIndex + 1 === number ? 'active' : ''}`}
              variant="outline-dark"
              active={currentPageIndex + 1 === number}
            >
              {number}
            </Button>
          ))}
          <Button
            className={`pagination-button arrows ${!canGoNextPage ? 'disabled' : ''}`}
            variant="outline-dark"
            onClick={onNextPageClick}
            disabled={!canGoNextPage}
          >
            <GreaterThanArrow />
          </Button>
          <Button
            className={`pagination-button arrows ${!canGoNextPage ? 'disabled' : ''}`}
            variant="outline-dark"
            onClick={onLastPageClick}
            disabled={!canGoNextPage}
          >
            <DoubleGreaterThanArrow />
          </Button>
        </div>
        <div className="pagination-buttons__page-size">
          <span>{t('items per page')}</span>
          <ReactSelect
            options={[
              { value: 10, label: 10 },
              { value: 20, label: 20 },
              { value: 50, label: 50 },
              { value: 100, label: 100 },
            ]}
            value={{ value: pageSize, label: pageSize }}
            onChange={({ value }) => onPageSizeChange(value)}
            className="pagination-select"
            classNamePrefix="pagination-select"
            menuPlacement="top"
            isSearchable={false}
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
          />
        </div>
      </div>
    </div>
  )
}
