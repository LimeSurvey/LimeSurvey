import { UserSettingsService } from './userSettings.service'

const createService = (get) => {
  const service = new UserSettingsService({ restHeaders: {} }, 'http://api')
  service.restClient = { get }
  return service
}

describe('UserSettingsService.getUserSettingValue', () => {
  test('returns an empty string value as found', async () => {
    const get = jest.fn().mockResolvedValue('')
    const result =
      await createService(get).getUserSettingValue('answeroptionprefix')
    expect(result).toEqual({ found: true, value: '' })
    expect(get).toHaveBeenCalledWith(
      'http://api/user-setting/answeroptionprefix',
      {},
      undefined,
      true
    )
  })

  test('returns a configured value', async () => {
    const get = jest.fn().mockResolvedValue('ANS')
    expect(await createService(get).getUserSettingValue('x')).toEqual({
      found: true,
      value: 'ANS',
    })
  })

  test('maps 404 to not found', async () => {
    const get = jest
      .fn()
      .mockRejectedValue(Object.assign(new Error('nf'), { httpStatus: 404 }))
    expect(await createService(get).getUserSettingValue('x')).toEqual({
      found: false,
    })
  })

  test('rethrows server and network errors instead of treating them as unset', async () => {
    const get = jest
      .fn()
      .mockRejectedValue(Object.assign(new Error('boom'), { httpStatus: 500 }))
    await expect(createService(get).getUserSettingValue('x')).rejects.toThrow(
      'boom'
    )
    const net = jest.fn().mockRejectedValue(new Error('NO_RESPONSE'))
    await expect(createService(net).getUserSettingValue('x')).rejects.toThrow()
  })
})
