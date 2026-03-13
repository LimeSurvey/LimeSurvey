import { ComponentModal, ConfirmModal } from 'components'
import { ColumnsManagement } from './ColumnsManagement'

export const ResponseModals = ({
  showResponsesDeleteModal,
  setShowResponsesDeleteModal,
  showAttachmentsDeleteModal,
  setShowAttachmentsDeleteModal,
  showColumnManagementModal,
  setShowColumnManagementModal,
  showQuestionComponent,
  setShowQuestionComponent,
  showSurveyDetails,
  setShowSurveyDetails,
  handleOnColumnsManagementConfirm = () => {},
  onAttachmentsDeleteConfirm = () => {},
  onResponsesDeleteConfirm = () => {},
  QuestionComponent,
  SurveyDetailsComponent,
  table,
  handleOnHide,
  isBulkAction,
  selectedRowsIds = [],
}) => {
  const handleOnDeleteConfirm = () => {
    onResponsesDeleteConfirm()
    setShowResponsesDeleteModal(false)
  }

  const handleAttachmentsDeleteConfirm = () => {
    onAttachmentsDeleteConfirm()
    setShowAttachmentsDeleteModal(false)
  }

  const deleteModalTitle = isBulkAction
    ? t('Delete responses')
    : t('Delete response')

  const deleteModalDescription = isBulkAction
    ? t(
        'The selected {{count}} responses will be deleted. Do you want to proceed?',
        { count: selectedRowsIds?.length }
      )
    : `${t('Are you sure you want to delete this response? This action cannot be reverted.')}`

  const confirmModalButtonText = isBulkAction
    ? t('Delete')
    : t('Delete response')

  return (
    <>
      <ConfirmModal
        title={deleteModalTitle}
        description={deleteModalDescription}
        show={showResponsesDeleteModal}
        onConfirm={handleOnDeleteConfirm}
        onHide={() => {
          setShowResponsesDeleteModal(false)
          handleOnHide()
        }}
        modalBodyClassname="responses-confirm-modal-body"
        confirmButtonText={confirmModalButtonText}
      />
      <ConfirmModal
        title={t('Delete attachments')}
        description={t(
          'Are you sure you want to delete the attachments for the selected responses?'
        )}
        show={showAttachmentsDeleteModal}
        onConfirm={handleAttachmentsDeleteConfirm}
        onHide={() => {
          setShowAttachmentsDeleteModal(false)
          handleOnHide()
        }}
        modalBodyClassname="responses-confirm-modal-body"
      />
      <ComponentModal
        show={showColumnManagementModal}
        onHide={() => {
          setShowColumnManagementModal(false)
          handleOnHide()
        }}
        headerClassname="position-absolute end-0"
        Component={
          <ColumnsManagement
            table={table}
            onHide={() => {
              setShowColumnManagementModal(false)
              handleOnHide()
            }}
            handleOnColumnsManagementConfirm={handleOnColumnsManagementConfirm}
          />
        }
        componentClassname="column-manager"
      />
      <ComponentModal
        show={showQuestionComponent}
        onHide={() => {
          setShowQuestionComponent(false)
          handleOnHide()
        }}
        headerClassname="position-absolute end-0"
        Component={QuestionComponent}
        componentClassname="question-component-modal"
      />
      <ComponentModal
        show={showSurveyDetails}
        onHide={() => {
          setShowSurveyDetails(false)
          handleOnHide()
        }}
        headerClassname="position-absolute end-0"
        Component={SurveyDetailsComponent}
        componentClassname="responses-component-modal"
        modalClassname={'responses-component-details-modal'}
      />
    </>
  )
}
