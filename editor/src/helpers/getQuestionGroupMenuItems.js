import { getTooltipMessages } from './options'

export const getQuestionGroupMenuItems = ({
  onDuplicate,
  onDelete,
  isSurveyActive,
  hasSurveyUpdatePermission,
}) => {
  const disabled = {
    state: isSurveyActive || !hasSurveyUpdatePermission,
    tooltip: isSurveyActive
      ? getTooltipMessages().ACTIVE_DISABLED
      : getTooltipMessages().NO_PERMISSION,
  }

  return [
    {
      type: 'header',
      label: t('Group actions'),
    },
    {
      type: 'item',
      label: t('Duplicate group'),
      icon: 'ri-file-copy-line',
      onClick: onDuplicate,
      testId: 'duplicate-button',
      disabled,
    },
    {
      type: 'item',
      label: t('Delete group'),
      icon: 'ri-delete-bin-line',
      onClick: onDelete,
      className: 'text-danger',
      testId: 'delete-button',
      disabled,
    },
  ]
}
