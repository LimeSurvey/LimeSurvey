import { SurveyImportService } from '../surveyImport.service'

describe('SurveyImportService', () => {
  const auth = {
    csrfToken: 'csrf-token',
    csrfTokenName: 'YII_CSRF_TOKEN',
    surveyImportUrl: '/index.php/admin/survey/sa/import',
  }

  let service

  beforeEach(() => {
    service = new SurveyImportService(auth)
  })

  test('imports a survey through the legacy endpoint', async () => {
    const summary = { surveys: 1, questions: 3 }
    const file = new File(['survey'], 'survey.lss')

    jest.spyOn(service.restClient, 'post').mockResolvedValue({
      success: true,
      summary,
    })

    await expect(
      service.importSurvey({
        convertResourceLinks: true,
        file,
        groupStrategy: 'default',
      })
    ).resolves.toEqual(summary)

    const [url, formData, headers, skipGlobalErrorHandler] =
      service.restClient.post.mock.calls[0]

    expect(url).toBe(auth.surveyImportUrl)
    expect(formData.get('the_file')).toEqual(file)
    expect(formData.get('surveysgroup')).toBe('default')
    expect(formData.get('translinksfields')).toBe('1')
    expect(formData.get(auth.csrfTokenName)).toBe(auth.csrfToken)
    expect(headers).toEqual({ 'X-Requested-With': 'XMLHttpRequest' })
    expect(skipGlobalErrorHandler).toBe(true)
  })

  test('surfaces an error returned by the legacy endpoint', async () => {
    jest.spyOn(service.restClient, 'post').mockResolvedValue({
      success: false,
      error: 'Invalid survey file.',
    })

    await expect(
      service.importSurvey({
        convertResourceLinks: false,
        file: new File(['invalid'], 'survey.lss'),
        groupStrategy: 'default',
      })
    ).rejects.toThrow('Invalid survey file.')
  })
})
