export const getOrderOptions = () => {
  return [
    {
      label: t('Normal'),
      value: 'normal',
    },
    {
      label: t('Random'),
      value: 'random',
    },
    {
      label: t('Alphabetical'),
      value: 'alphabetical',
    },
    {
      label: t('Random A-Z/Z-A'),
      value: 'random_alphabetical',
    },
  ]
}
