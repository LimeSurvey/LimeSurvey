import { PublishSettings } from 'components/PublishSettings/PublishSettings'
import { Button, TooltipContainer } from 'components'
import { getSiteUrl, getTooltipMessages, STATES } from 'helpers'
import { useAppState } from 'hooks'

export const ActionButton = ({
  className = '',
  survey,
  operationsLength = 0,
  triggerPublish,
  showShareActionButton,
  showExportResponsesButton,
  showExportStatisticsButton,
  showPublishSettings = true,
}) => {
  const [isSurveyActive] = useAppState(STATES.IS_SURVEY_ACTIVE, false)
  if (showShareActionButton) {
    return (
      <TooltipContainer
        placement="bottom"
        showTip={!isSurveyActive}
        tip={getTooltipMessages().SURVEY_NOT_ACTIVE_NO_RESULTS}
      >
        <Button
          variant="success"
          className={className}
          href={'#/responses/' + survey.sid}
          rel="noreferrer"
          disabled={!isSurveyActive}
        >
          <span className="m-0 text-white">{t('Results')}</span>
        </Button>
      </TooltipContainer>
    )
  }

  if (showExportResponsesButton) {
    return (
      <Button
        variant="success"
        className="text-white me-2"
        href={getSiteUrl(
          '/admin/export/sa/exportresults/surveyid/' + survey.sid
        )}
        target="_blank"
        rel="noreferrer"
      >
        {t('Export results')}
      </Button>
    )
  }

  if (showExportStatisticsButton) {
    return (
      <Button
        variant="success"
        className="text-white me-2"
        href={getSiteUrl('/admin/statistics/sa/index/surveyid/' + survey.sid)}
        target="_blank"
        rel="noreferrer"
      >
        {t('Export statistics')}
      </Button>
    )
  }

  if (showPublishSettings) {
    return (
      <PublishSettings
        className={className}
        survey={survey}
        operationsLength={operationsLength}
        triggerPublish={triggerPublish}
      />
    )
  }
}
