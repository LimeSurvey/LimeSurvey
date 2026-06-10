import { URLS } from 'helpers'
import classNames from 'classnames'

import { Button, TooltipContainer } from 'components'
import { ArrowBackIcon } from 'components/icons'

export const ButtonBackToClassicEditor = ({ surveyId, className = '' }) => {
  const classicEditorUrl = URLS.SURVEY_OVERVIEW + '?surveyid=' + surveyId

  return (
    <TooltipContainer
      placement="bottom"
      showTip={true}
      tip={t('Go back to classic editor')}
    >
      <Button
        href={classicEditorUrl}
        variant="light"
        className={classNames('align-items-center', className)}
      >
        <ArrowBackIcon className="me-1" />
        {t('Back to classic editor')}
      </Button>
    </TooltipContainer>
  )
}
