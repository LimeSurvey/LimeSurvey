import React, { useEffect, useState } from 'react'
import { Form } from 'react-bootstrap'
import classNames from 'classnames'
import { format } from 'util'

export const SurveySetting = (props) => {
  const [rerender, setRerender] = useState(false)

  const childComponentOnNewLine = props?.childOnNewLine
  if (props.renderFullWidth) {
    return (
      <div className="mt-2">
        <Form.Label>{props.subText}</Form.Label>
        <div className={` ${props.extraClass || ''}`}>
          <props.childComponent {...props} />
        </div>
      </div>
    )
  }

  useEffect(() => {
    if (props.rerenderSettings[props.setting.keyPath] === true) {
      setRerender(true)
      props.setRerenderSettings({
        ...props.rerenderSettings,
        [props.setting.keyPath]: false,
      })
    } else if (rerender) {
      setRerender(false)
    }
  }, [props.rerenderSettings, rerender])

  if (rerender) {
    return (
      <div
        style={{ height: '100%' }}
        className="d-flex flex-column justify-content-center align-items-center"
      >
        <span style={{ width: 48, height: 48 }} className="loader mb-4"></span>
      </div>
    )
  }

  return !childComponentOnNewLine ? (
    <div className="mt-2 d-flex align-items-center">
      <div className="w-50">
        <p className="h6 mb-0">
          {props.link ? (
            <span
              dangerouslySetInnerHTML={{
                __html: format(
                  props.mainText,
                  `<a href="${props.link}" target="_blank" rel="noopener noreferrer">`,
                  '</a>'
                ),
              }}
            />
          ) : (
            props.mainText
          )}
        </p>
        {props.subText && (
          <Form.Label
            className={classNames('mb-0 text-secondary', {
              'd-none': !process.env.REACT_APP_DEV_MODE,
            })}
          >
            {props.subText}
          </Form.Label>
        )}
      </div>
      <div className={`w-50 ms-2 ${props.extraClass || ''}`}>
        <props.childComponent {...props} />
        {props.secondaryChildComponent && (
          <props.secondaryChildComponent {...props} />
        )}
      </div>
    </div>
  ) : (
    <div className="mt-3">
      <div className="w-100 mb-1">
        <p className="h6 mb-0">{props.mainText}</p>
        {props.subText && (
          <Form.Label
            className={classNames('mb-0 text-secondary', {
              'd-none': !process.env.REACT_APP_DEV_MODE,
            })}
          >
            {props.subText}
          </Form.Label>
        )}
      </div>
      <div className={`w-100 ${props.extraClass || ''}`}>
        <props.childComponent {...props} />
        {props.secondaryChildComponent && (
          <props.secondaryChildComponent {...props} />
        )}
      </div>
    </div>
  )
}
