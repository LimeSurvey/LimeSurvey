import { getTooltipMessages } from './options'

export const getQuestionGroupMenuItems = ({
  onDuplicate,
  onDelete,
  isSurveyActive,
  hasSurveyUpdatePermission,
}) => {
  if (!hasSurveyUpdatePermission) {
    return []
  }

  const disabled = {
    state: isSurveyActive,
    tooltip: getTooltipMessages().ACTIVE_DISABLED,
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
