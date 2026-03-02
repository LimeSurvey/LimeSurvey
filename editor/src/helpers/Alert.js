import { CloseIcon, ExclamationMark, XIcon } from 'components/icons'
import toast from 'react-hot-toast'
import Swal from 'sweetalert2'
import withReactContent from 'sweetalert2-react-content'
import React from 'react'
import ReactDOMServer from 'react-dom/server'

export const SwalAlert = withReactContent(Swal)

export const confirmAlert = ({
  title = t('Are you sure?'),
  text = t("You won't be able to revert this!"),
  showCancelButton = true,
  confirmButtonText = t('Yes, delete it'),
  icon = '',
  html = '',
  width = 400,
  confirmButtonClass = '',
  containerClass = '',
  showCloseButton = true,
  closeButtonClass = '',
}) => {
  return SwalAlert.fire({
    showCloseButton,
    closeButtonHtml: ReactDOMServer.renderToString(
      <CloseIcon className="text-black fill-current" />
    ),
    title,
    text,
    icon,
    showCancelButton,
    confirmButtonText,
    html: html,
    width: width,
    customClass: {
      confirmButton: `${confirmButtonClass}`,
      container: `${containerClass}`,
      closeButton: `${closeButtonClass}`,
    },
  })
}

export const htmlPopup = ({
  title = '',
  icon,
  html = '',
  showCloseButton,
  showCancelButton,
  showConfirmButton,
  confirmButtonClass = '',
  focusConfirm,
  confirmButtonText,
  confirmButtonAriaLabel,
  cancelButtonText,
  cancelButtonAriaLabel,
  containerClass,
  closeButtonClass,
  popupClass,
  width,
  preConfirm = () => {},
}) => {
  return SwalAlert.fire({
    title,
    icon,
    html,
    showCloseButton,
    showCancelButton,
    focusConfirm,
    confirmButtonText,
    confirmButtonAriaLabel,
    cancelButtonText,
    cancelButtonAriaLabel,
    showConfirmButton,
    customClass: {
      confirmButton: `${confirmButtonClass}`,
      closeButton: `${closeButtonClass}`,
      container: `${containerClass}`,
      popup: `${popupClass}`,
    },
    width,
    preConfirm,
  })
}

export const errorToast = (title, position = 'bottom-right') => {
  return Toast({
    message: title,
    position: position,
    leftIcon: <ExclamationMark />,
    rightIcon: <XIcon />,
    duration: 50000,
  })
}

export const Toast = ({
  message,
  position = 'bottom-right',
  className = 'error-toast',
  leftIcon = '',
  rightIcon = <XIcon />,
  onRightIconClick = () => {},
  onLeftIconClick = () => {},
  duration = 5000,
  id,
}) => {
  toast.custom(
    (t) => (
      <div
        className={`${className} ${
          t.visible ? 'animate-enter' : 'animate-leave'
        }`}
      >
        <div onClick={onLeftIconClick}> {leftIcon}</div>
        <div>{message}</div>
        <div
          className="mouse-pointer"
          onClick={() => {
            onRightIconClick()
            toast.remove(t.id)
          }}
        >
          {rightIcon}
        </div>
      </div>
    ),
    {
      position,
      duration,
      id: id || `${message}-${Date.now()}`, // Add timestamp to ensure unique keys
    }
  )
}

export const toastComoponent = ({
  Component,
  position = 'bottom-right',
  className = 'error-toast',
  rightIcon = <XIcon />,
  onRightIconClick = () => {},
  duration = 5000,
  id,
}) => {
  toast.custom(
    (t) => (
      <div
        className={`${className} ${
          t.visible ? 'animate-enter' : 'animate-leave'
        }`}
      >
        {Component}
        <div
          className="mouse-pointer toast-component-right-icon"
          onClick={() => {
            onRightIconClick()
            toast.remove(t.id)
          }}
        >
          {rightIcon}
        </div>
      </div>
    ),
    {
      position,
      duration,
      id: id || `${Date.now()}`, // Add timestamp to ensure unique keys
    }
  )
}
