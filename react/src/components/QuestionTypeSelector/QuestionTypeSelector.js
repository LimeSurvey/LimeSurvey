import React, { useEffect, useRef, useState } from 'react'
import ListGroup from 'react-bootstrap/ListGroup'
import {
  List,
  Bootstrap,
  Collection,
  Images,
  MenuButtonWide,
  Upload,
  BrowserChrome,
  Box2,
  GenderAmbiguous,
  Circle,
} from 'react-bootstrap-icons'
import classNames from 'classnames'

import { Input } from 'components/UIComponents'
import searchIcon from 'assets/icons/search-icon.svg'
import { RankingIcon, TableIcon, ClockIcon, TextIcon } from 'components/icons'

import { QuestionTypeInfo } from '../QuestionTypes'

import './QuestionTypeSelector.scss'

export const QuestionTypeSelector = ({
  callBack,
  hideQuestionGroup = false,
  disableAddingQuestions = false,
}) => {
  const [searchTerm, setSearchTerm] = useState('')
  const [selectedItemIndex, setSelectedItemIndex] = useState(-1)
  const searchInputRef = useRef(null)
  const listRef = useRef(null)
  const listItemRef = useRef(null)

  const itemsList = [
    {
      title: 'Array',
      items: [
        {
          value: QuestionTypeInfo.ARRAY.type,
          label: QuestionTypeInfo.ARRAY.title,
          theme: QuestionTypeInfo.ARRAY.theme,
          icon: <TableIcon />,

          hidden: disableAddingQuestions,
        },
        {
          value: QuestionTypeInfo.ARRAY_TEXT.type,
          label: QuestionTypeInfo.ARRAY_TEXT.title,
          theme: QuestionTypeInfo.ARRAY_TEXT.theme,
          icon: <TableIcon />,

          hidden: disableAddingQuestions,
        },
        {
          value: QuestionTypeInfo.ARRAY_NUMBERS.type,
          label: QuestionTypeInfo.ARRAY_NUMBERS.title,
          theme: QuestionTypeInfo.ARRAY_NUMBERS.theme,
          icon: <TableIcon />,

          hidden: disableAddingQuestions,
        },
        {
          value: QuestionTypeInfo.ARRAY_COLUMN.type,
          label: QuestionTypeInfo.ARRAY_COLUMN.title,
          theme: QuestionTypeInfo.ARRAY_COLUMN.theme,
          icon: <TableIcon />,

          hidden: disableAddingQuestions,
        },
        {
          value: QuestionTypeInfo.ARRAY_DUAL_SCALE.type,
          label: QuestionTypeInfo.ARRAY_DUAL_SCALE.title,
          theme: QuestionTypeInfo.ARRAY_DUAL_SCALE.theme,
          icon: <TableIcon />,
          hidden: disableAddingQuestions,
        },
      ],
    },
    {
      title: 'Multiple Choice',
      items: [
        {
          value: QuestionTypeInfo.MULTIPLE_CHOICE.type,
          label: QuestionTypeInfo.MULTIPLE_CHOICE.title,
          theme: QuestionTypeInfo.MULTIPLE_CHOICE.theme,
          icon: <List />,
          hidden: disableAddingQuestions,
        },
        {
          value: QuestionTypeInfo.MULTIPLE_CHOICE_WITH_COMMENTS.type,
          label: QuestionTypeInfo.MULTIPLE_CHOICE_WITH_COMMENTS.title,
          theme: QuestionTypeInfo.MULTIPLE_CHOICE_WITH_COMMENTS.theme,
          icon: <List />,
          hidden: disableAddingQuestions,
        },
        {
          value: QuestionTypeInfo.MULTIPLE_CHOICE_BUTTONS.type,
          label: QuestionTypeInfo.MULTIPLE_CHOICE_BUTTONS.title,
          theme: QuestionTypeInfo.MULTIPLE_CHOICE_BUTTONS.theme,
          icon: <MenuButtonWide />,
          hidden: disableAddingQuestions,
        },
        {
          value: QuestionTypeInfo.MULTIPLE_CHOICE_IMAGE_SELECT.type,
          label: QuestionTypeInfo.MULTIPLE_CHOICE_IMAGE_SELECT.title,
          theme: QuestionTypeInfo.MULTIPLE_CHOICE_IMAGE_SELECT.theme,
          icon: <Images />,
          hidden: disableAddingQuestions,
        },
      ],
    },
    {
      title: 'Single Choice',
      items: [
        {
          value: QuestionTypeInfo.LIST_RADIO.type,
          label: QuestionTypeInfo.LIST_RADIO.title,
          theme: QuestionTypeInfo.LIST_RADIO.theme,
          icon: <List />,
          hidden: disableAddingQuestions,
        },
        {
          value: QuestionTypeInfo.LIST_RADIO_WITH_COMMENT.type,
          label: QuestionTypeInfo.LIST_RADIO_WITH_COMMENT.title,
          theme: QuestionTypeInfo.LIST_RADIO_WITH_COMMENT.theme,
          icon: <Bootstrap />,
          hidden: disableAddingQuestions,
        },
        {
          value: QuestionTypeInfo.SINGLE_CHOICE_LIST_IMAGE_SELECT.type,
          label: QuestionTypeInfo.SINGLE_CHOICE_LIST_IMAGE_SELECT.title,
          theme: QuestionTypeInfo.SINGLE_CHOICE_LIST_IMAGE_SELECT.theme,
          icon: <List />,
          hidden: disableAddingQuestions,
        },
        {
          value: QuestionTypeInfo.FIVE_POINT_CHOICE.type,
          label: QuestionTypeInfo.FIVE_POINT_CHOICE.title,
          theme: QuestionTypeInfo.FIVE_POINT_CHOICE.theme,
          icon: <Bootstrap />,
          hidden: disableAddingQuestions,
        },
        {
          value: QuestionTypeInfo.SINGLE_CHOICE_DROPDOWN.type,
          label: QuestionTypeInfo.SINGLE_CHOICE_DROPDOWN.title,
          theme: QuestionTypeInfo.SINGLE_CHOICE_DROPDOWN.theme,
          icon: <Images />,
          hidden: disableAddingQuestions,
        },
        {
          value: QuestionTypeInfo.SINGLE_CHOICE_BUTTONS.type,
          label: QuestionTypeInfo.SINGLE_CHOICE_BUTTONS.title,
          theme: QuestionTypeInfo.SINGLE_CHOICE_BUTTONS.theme,
          icon: <List />,
          hidden: disableAddingQuestions,
        },
      ],
    },
    {
      title: 'Text',
      items: [
        {
          value: QuestionTypeInfo.SHORT_TEXT.type,
          label: QuestionTypeInfo.SHORT_TEXT.title,
          theme: QuestionTypeInfo.SHORT_TEXT.theme,
          icon: <Collection />,
          hidden: disableAddingQuestions,
        },
        {
          value: QuestionTypeInfo.LONG_TEXT.type,
          label: QuestionTypeInfo.LONG_TEXT.title,
          theme: QuestionTypeInfo.LONG_TEXT.theme,
          icon: <Collection />,
          hidden: disableAddingQuestions,
        },
        {
          value: QuestionTypeInfo.MULTIPLE_SHORT_TEXTS.type,
          label: QuestionTypeInfo.MULTIPLE_SHORT_TEXTS.title,
          theme: QuestionTypeInfo.MULTIPLE_SHORT_TEXTS.theme,
          icon: <List />,
          hidden: disableAddingQuestions,
        },
      ],
    },
    {
      title: 'Mask',
      items: [
        {
          value: QuestionTypeInfo.MULTIPLE_NUMERICAL_INPUTS.type,
          label: QuestionTypeInfo.MULTIPLE_NUMERICAL_INPUTS.title,
          theme: QuestionTypeInfo.MULTIPLE_NUMERICAL_INPUTS.theme,
          icon: <TableIcon />,
          hidden: disableAddingQuestions,
        },

        {
          value: QuestionTypeInfo.DATE_TIME.type,
          label: QuestionTypeInfo.DATE_TIME.title,
          theme: QuestionTypeInfo.DATE_TIME.theme,
          icon: <ClockIcon />,
          hidden: disableAddingQuestions,
        },
        {
          value: QuestionTypeInfo.RATING.type,
          label: QuestionTypeInfo.RATING.title,
          theme: QuestionTypeInfo.RATING.theme,
          icon: <List />,
          hidden: disableAddingQuestions,
        },
        {
          value: QuestionTypeInfo.RANKING.type,
          label: QuestionTypeInfo.RANKING.title,
          theme: QuestionTypeInfo.RANKING.theme,
          icon: <RankingIcon />,
          hidden: disableAddingQuestions,
        },
        {
          value: QuestionTypeInfo.RANKING_ADVANCED.type,
          label: QuestionTypeInfo.RANKING_ADVANCED.title,
          theme: QuestionTypeInfo.RANKING_ADVANCED.theme,
          icon: <RankingIcon />,
          hidden: disableAddingQuestions,
        },
        {
          value: QuestionTypeInfo.FILE_UPLOAD.type,
          label: QuestionTypeInfo.FILE_UPLOAD.title,
          theme: QuestionTypeInfo.FILE_UPLOAD.theme,
          icon: <Upload />,
          hidden: disableAddingQuestions,
        },
        {
          value: QuestionTypeInfo.TEXT_DISPLAY.type,
          label: QuestionTypeInfo.TEXT_DISPLAY.title,
          theme: QuestionTypeInfo.TEXT_DISPLAY.theme,
          icon: <TextIcon />,
          hidden: disableAddingQuestions,
        },
        {
          value: QuestionTypeInfo.GENDER.type,
          label: QuestionTypeInfo.GENDER.title,
          theme: QuestionTypeInfo.GENDER.theme,
          icon: <GenderAmbiguous />,
          hidden: disableAddingQuestions,
        },
        {
          value: QuestionTypeInfo.YES_NO.type,
          label: QuestionTypeInfo.YES_NO.title,
          theme: QuestionTypeInfo.YES_NO.theme,
          icon: <Circle />,
          hidden: disableAddingQuestions,
        },
        {
          value: QuestionTypeInfo.BROWSER_DETECTION.type,
          label: QuestionTypeInfo.BROWSER_DETECTION.title,
          theme: QuestionTypeInfo.BROWSER_DETECTION.theme,
          icon: <BrowserChrome />,
          hidden: disableAddingQuestions,
        },
        {
          value: QuestionTypeInfo.EQUATION.type,
          label: QuestionTypeInfo.EQUATION.title,
          theme: QuestionTypeInfo.EQUATION.theme,
          icon: <Box2 />,
          hidden: disableAddingQuestions,
        },
      ],
    },
    {
      title: 'Structure',
      items: [
        {
          value: QuestionTypeInfo.QUESTION_GROUP.type,
          label: QuestionTypeInfo.QUESTION_GROUP.title,
          theme: QuestionTypeInfo.QUESTION_GROUP.theme,
          icon: <Collection />,
          hidden: hideQuestionGroup,
        },
      ],
    },
  ]

  const filteredItemsLists = itemsList.reduce((result, currentItem) => {
    const filteredItems = currentItem.items.filter(
      (item) =>
        item.label.toLowerCase().includes(searchTerm.toLowerCase()) &&
        !item.hidden
    )

    if (filteredItems.length) {
      result.push({
        title: currentItem.title,
        items: filteredItems,
      })
    }

    return result
  }, [])

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
  let previousItemsTotal = 0

  return (
    <div className="question-type-selector">
      <div className="search">
        <Input
          value={searchTerm}
          placeholder="Search for a question type"
          id="question-type-search"
          inputRef={searchInputRef}
          dataTestId={'question-type-search'}
          Icon={searchIcon}
          onChange={(e) => {
            setSearchTerm(e.target.value)
            setSelectedItemIndex(-1)
          }}
        />
        <hr />
      </div>
      <div ref={listRef} className="list">
        {filteredItemsLists.map((list, listGroupIndex) => {
          previousItemsTotal += list.items.length
          const offsetIndex = previousItemsTotal - list.items.length

          return (
            <React.Fragment
              key={listGroupIndex + list.title + 'question-type-selector'}
            >
              <h6>{list.title}</h6>
              <ListGroup className="my-2" variant="flush">
                {list.items.map((listItem, listItemIndex) => {
                  return (
                    <ListGroup.Item
                      key={'list-item' + listItemIndex}
                      onClick={() =>
                        callBack({
                          type: listItem.value,
                          theme: listItem.theme,
                        })
                      }
                      className={classNames('mb-1 px-2', {
                        'focus-element':
                          selectedItemIndex === offsetIndex + listItemIndex,
                      })}
                      ref={
                        selectedItemIndex === offsetIndex + listItemIndex
                          ? listItemRef
                          : null
                      }
                      data-testid={`question-type-${listItem.label} question-type-selector-label`}
                    >
                      {listItem.icon} {listItem.label}
                    </ListGroup.Item>
                  )
                })}
              </ListGroup>
            </React.Fragment>
          )
        })}
      </div>
    </div>
  )
}
