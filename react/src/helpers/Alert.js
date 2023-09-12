import Swal from 'sweetalert2'
import withReactContent from 'sweetalert2-react-content'

export const SwalAlert = withReactContent(Swal)

export const ConfirmAlert = ({
  title = 'Are you sure?',
  text = "You won't be able to revert this!",
  showCancelButton = true,
  confirmButtonText = 'Yes, delete it',
  icon,
  html = '',
  width = 400,
  confirmButtonClass = '',
  containerClass = '',
  showCloseButton = true,
  closeButtonClass,
}) => {
  return SwalAlert.fire({
    showCloseButton,
    title,
    text,
    icon: icon,
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

export const HtmlPopup = ({
  title,
  icon,
  html,
  showCloseButton,
  showCancelButton,
  showConfirmButton,
  focusConfirm,
  confirmButtonText,
  confirmButtonAriaLabel,
  cancelButtonText,
  cancelButtonAriaLabel,
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
  })
}

export const Toast = (title) => {
  return SwalAlert.fire({
    toast: true,
    position: 'top-right',
    title,
    icon: 'error',
    timer: 3000,
    timerProgressBar: true,
    showConfirmButton: false,
  })
}
