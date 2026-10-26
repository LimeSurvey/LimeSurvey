class CustomI18nBackend {
  setLanguages
  languages

  constructor(_setLanguages, languages) {
    this.setLanguages = _setLanguages
    this.languages = languages || {}
  }

  init(services, backendOptions = {}) {
    this.options = backendOptions
    this.translationsService = backendOptions.translationsService

    if (
      !this.translationsService ||
      typeof this.translationsService.getTranslations !== 'function'
    ) {
      throw new Error(
        'translationsService.getTranslations must be a valid function'
      )
    }
  }

  read(language, namespace, callback) {
    this.translationsService
      .getTranslations(language)
      .then((data) => {
        this.setLanguages({
          ...this.languages,
          [language]: data[0]['languages'],
        })
        callback(null, data[0]['translations'])
      })
      .catch((error) => {
        callback(error, false)
      })
  }
}

export default CustomI18nBackend
