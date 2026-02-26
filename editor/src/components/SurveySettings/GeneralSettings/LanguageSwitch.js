import { LanguageOptions } from 'helpers'
import { Select } from 'components/UIComponents'

export const LanguageSwitch = ({
  language,
  handleLanguageChange = () => {},
  label = t('Language'),
}) => {
  const languageOptionsAsArray = Object.keys(LanguageOptions).map((key) => {
    return {
      value: key,
      label: LanguageOptions[key].label,
    }
  })

  return (
    <Select
      labelText={label}
      options={languageOptionsAsArray}
      onChange={({ target: { value } }) => {
        handleLanguageChange(value)
      }}
      selectedOption={LanguageOptions[language] || {}}
    />
  )
}
