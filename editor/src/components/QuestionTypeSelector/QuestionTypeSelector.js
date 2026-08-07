import React, { useEffect, useRef, useState } from 'react'
import ListGroup from 'react-bootstrap/ListGroup'
import classNames from 'classnames'
import Container from 'react-bootstrap/Container'
import Row from 'react-bootstrap/Row'
import Col from 'react-bootstrap/Col'

import { ClockArrowIcon, AddIcon } from 'components/icons'
import { Input } from 'components/UIComponents'
import searchIcon from 'assets/icons/search-icon.svg'

import { getQuestionGroupItem, getQuestionItemsList } from './constants'

export const QuestionTypeSelector = ({
  callBack,
  disableAddingQuestions = false,
  attributeTypeSelector = false,
}) => {
  const [searchTerm, setSearchTerm] = useState('')
  const [selectedItemIndex, setSelectedItemIndex] = useState(-1)
  const searchInputRef = useRef(null)
  const listRef = useRef(null)
  const listItemRef = useRef(null)
  const [searchedQuestionTypeOrGroup, setSearchedQuestionTypeOrGroup] =
    useState([])

  const filteredItemsLists = getQuestionItemsList().reduce(
    (result, currentItem) => {
      const filteredItems = currentItem.items.filter(
        (item) => item.label.toLowerCase().includes(searchTerm.toLowerCase())
        // && !item.hidden
      )

      if (filteredItems.length) {
        result.push({
          title: currentItem.title,
          icon: currentItem.icon,
          items: filteredItems,
        })
      }

      return result
    },
    []
  )

  useEffect(() => {
    searchInputRef.current.focus({ preventScroll: true })

    const itemsLength = filteredItemsLists.reduce(
      (acc, curr) => acc + curr.items.length,
      0
    )

    const handleKeyDown = (event) => {
      if (event.key === 'ArrowUp') {
        setSelectedItemIndex((prevSelectedItem) =>
          Math.max(prevSelectedItem - 1, -1)
        )
      } else if (event.key === 'ArrowDown') {
        setSelectedItemIndex((prevSelectedItem) =>
          Math.min(prevSelectedItem + 1, itemsLength - 1)
        )
      } else if (event.key === 'Enter') {
        // loop into the items until we find the selected item index.
        let index = 0
        for (let i = 0; i < filteredItemsLists.length; i++) {
          const listItems = filteredItemsLists[i].items
          for (let j = 0; j < listItems.length; j++) {
            const item = listItems[j]
            if (index === selectedItemIndex) {
              const temp = [...searchedQuestionTypeOrGroup]
              if (temp.length > 4) {
                temp.shift()
              }
              temp.push(searchTerm)
              setSearchedQuestionTypeOrGroup([...temp])
              callBack({
                type: item.value,
                questionThemeName: item.theme,
              })
            }

            index++
          }
        }
      }
    }

    // scroll to the selected item
    if (listRef.current && listItemRef.current) {
      const listGroupRect = listRef.current.getBoundingClientRect()
      const selectedListItemRect = listItemRef.current.getBoundingClientRect()

      if (selectedListItemRect.bottom > listGroupRect.bottom) {
        listRef.current.scrollTop +=
          selectedListItemRect.bottom - listGroupRect.bottom + 10
      } else if (selectedListItemRect.top < listGroupRect.top) {
        listRef.current.scrollTop -=
          listGroupRect.top - selectedListItemRect.top
      }
    }

    document.addEventListener('keydown', handleKeyDown)
    return () => {
      document.removeEventListener('keydown', handleKeyDown)
    }
  }, [selectedItemIndex, filteredItemsLists, callBack])

  // Keeps a running count on how many questions we have

  const renderData = (data) => {
    const rows = []
    for (let i = 0; i < data.length; i += 3) {
      let previousItemsTotal = 0

      const rowItems =
        data.slice(i, i + 3).length === 3
          ? data.slice(i, i + 3)
          : [...data.slice(i, i + 3), { items: [] }]
      const cols = rowItems.map((list, listGroupIndex) => {
        previousItemsTotal += list.items.length
        const offsetIndex = previousItemsTotal - list.items.length

        return (
          <Col key={listGroupIndex + list.title + 'question-type-selector'}>
            <div className="d-flex gap-2  align-items-center">
              {list.icon}
              <span className="fw-bold m-0 label-s">{list.title}</span>
            </div>
            <ListGroup className="my-2" variant="flush">
              {list?.items.map((listItem, listItemIndex) => {
                return (
                  <ListGroup.Item
                    key={'list-item' + listItemIndex}
                    onClick={() => {
                      if (listItem.hidden || disableAddingQuestions) {
                        return
                      }
                      const temp = [...searchedQuestionTypeOrGroup]
                      temp.push(searchTerm)
                      setSearchedQuestionTypeOrGroup([...temp])
                      callBack({
                        type: listItem.value,
                        questionThemeName: listItem.theme,
                      })
                    }}
                    className={classNames('px-2', {
                      'focus-element':
                        selectedItemIndex === offsetIndex + listItemIndex,
                      'disabled': disableAddingQuestions,
                      'd-none': listItem.hidden,
                    })}
                    ref={
                      selectedItemIndex === offsetIndex + listItemIndex
                        ? listItemRef
                        : null
                    }
                    data-testid={`question-type-${listItem.theme}`}
                    id={`question-type-${listItem.theme}-item`}
                  >
                    {/* {listItem.icon} */}
                    <span
                      data-testid="question-type-selector-label"
                      className="text-primary label-s"
                      id={`question-type-${listItem.theme}-item-span`}
                    >
                      {listItem.label}
                    </span>
                  </ListGroup.Item>
                )
              })}
            </ListGroup>
          </Col>
        )
      })
      rows.push(
        <Row
          className={classNames('mb-4', {
            'flex-column': attributeTypeSelector,
          })}
          key={i}
        >
          {cols}
        </Row>
      )
    }
    return rows
  }
  return (
    <div className="question-type-selector border border-primary rounded border-2 shadow-sm position-relative">
      <div className="search" style={{ marginBottom: 26, height: 52 }}>
        <Input
          value={searchTerm}
          placeholder={t('Search for a question type')}
          id="question-type-search"
          inputRef={searchInputRef}
          dataTestId={'question-type-search'}
          Icon={searchIcon}
          onChange={(e) => {
            setSearchTerm(e.target.value)
            setSelectedItemIndex(-1)
          }}
          inputClass="question-inserter-search"
          className="h-100"
          autoComplete={false}
        />
      </div>
      <div ref={listRef} className="list d-flex">
        <div
          style={{ width: '25%', minHeight: '300px' }}
          className={classNames('d-flex flex-column justify-content-between', {
            'd-none': attributeTypeSelector,
          })}
        >
          <div>
            <ListGroup variant="flush">
              <ListGroup.Item className="add-question-group">
                <AddIcon className="text-primary fill-current ms-1" />
                <span
                  className="text-primary label-s ms-1"
                  onClick={() =>
                    callBack({
                      type: getQuestionGroupItem().value,
                      questionThemeName: getQuestionGroupItem().theme,
                    })
                  }
                >
                  {t('Add question group')}
                </span>
              </ListGroup.Item>
            </ListGroup>
            {process.env.REACT_APP_DEV_MODE && (
              <div className="d-flex align-items-center gap-1">
                <ClockArrowIcon />
                <span className="fw-bold m-0 label-s">
                  {t('Recently used')}
                </span>
              </div>
            )}
            <ListGroup className="my-2" variant="flush">
              {searchedQuestionTypeOrGroup?.map((searchedText, index) => (
                <span
                  className="text-primary text-normal cursor-pointer"
                  onClick={() => setSearchTerm(searchedText)}
                  key={`${index}-searched-text`}
                >
                  {searchedText}
                </span>
              ))}
            </ListGroup>
          </div>
        </div>

        <Container>{renderData(filteredItemsLists)}</Container>
      </div>
    </div>
  )
}
