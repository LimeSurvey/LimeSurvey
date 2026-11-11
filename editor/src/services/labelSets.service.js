import { RestClient } from './restClient.service'

export class LabelSetService {
  constructor(auth, baseUrl) {
    this.auth = auth
    this.baseurl = baseUrl
    this.restClient = new RestClient(baseUrl, auth.restHeaders)
  }

  /**
   * @param {object} options
   * @param {string[]} options.languages Only list sets that contain all of these languages
   * @param {boolean} options.updatable Only list sets the user may overwrite
   */
  getLabelSets = async ({ languages = [], updatable = false } = {}) => {
    const params = {}
    if (languages.length) {
      params.languages = languages.join(',')
    }
    if (updatable) {
      params.updatable = '1'
    }

    const data = await this.restClient.get(
      'label-sets',
      {},
      undefined,
      true,
      params
    )
    return data?.labelSets ?? []
  }

  getLabelSet = async (lid) => {
    const data = await this.restClient.get(
      `label-sets/${lid}`,
      {},
      undefined,
      true
    )
    return data?.labelSet
  }

  /**
   * Create a label set, or overwrite one when lid is given.
   *
   * @param {object} labelSet
   * @param {number} [labelSet.lid]
   * @param {string} [labelSet.name] Only used when creating
   * @param {string[]} labelSet.languages
   * @param {{code: string, assessmentValue: number, l10ns: object}[]} labelSet.labels
   */
  saveLabelSet = async ({ lid, name, languages, labels }) => {
    const data = lid
      ? await this.restClient.put(
          `label-sets/${lid}`,
          { languages, labels },
          {},
          true
        )
      : await this.restClient.post(
          'label-sets',
          { name, languages, labels },
          {},
          true
        )
    return data?.labelSet
  }
}
