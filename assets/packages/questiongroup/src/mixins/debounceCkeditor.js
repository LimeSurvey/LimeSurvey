const convertTime = time => {
    const [amt, t = 'ms'] = String(time).split(/(ms|s)/i)
    const types = {
        ms: 1,
        s: 1000
    }

    return Number(amt) * types[t]
}

const debounce = (fn, wait = '300ms') => {
    let timeout = null
    const timer = convertTime(wait)

    const debounced = (...args) => {
        const later = () => {
            timeout = null

            fn(...args)
        }

        clearTimeout(timeout)
        timeout = setTimeout(later, timer)

        if (!timeout) {
            fn(...args)
        }
    }

    debounced.cancel = () => {
        clearTimeout(timeout)
        timeout = null
    }

    return debounced
}

export default {
    name: 'debounce',
    install(Vue, {lock} = {}) {
        Vue.directive('debounce', {
            bind(el, {
                value,
                arg,
                modifiers
            }) {
                const fn = debounce(target => {
                    value(target.value)
                }, arg)
                const isUnlocked = (!modifiers.lock && !lock) || modifiers.unlock

                el.onkeyup = ({
                    keyCode,
                    target
                }) => {
                    if (keyCode === 13 && isUnlocked) {
                        fn.cancel()
                        value(target.value)
                    }

                    if (keyCode !== 13) {
                        fn(target)
                    }
                }
            }
        })
    }
}
