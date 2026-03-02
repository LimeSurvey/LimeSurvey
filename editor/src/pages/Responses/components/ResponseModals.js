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
}) => {
  const handleOnDeleteConfirm = () => {
    onResponsesDeleteConfirm()
    setShowResponsesDeleteModal(false)
  }

  const handleAttachmentsDeleteConfirm = () => {
    onAttachmentsDeleteConfirm()
    setShowAttachmentsDeleteModal(false)
  }

  return (
    <>
      <ConfirmModal
        title={t('Delete responses')}
        description={t('Do you really want to delete this response?')}
        show={showResponsesDeleteModal}
        onConfirm={handleOnDeleteConfirm}
        onHide={() => {
          setShowResponsesDeleteModal(false)
          handleOnHide()
        }}
        modalBodyClassname="responses-confirm-modal-body"
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
