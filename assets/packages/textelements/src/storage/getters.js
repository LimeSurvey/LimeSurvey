import isEmpty from 'lodash/isEmpty';
export default {
    fullyLoaded: (state) => {
        return !isEmpty(state.welcome);
    },
};