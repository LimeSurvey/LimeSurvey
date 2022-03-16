export const pjaxMixins = {
    methods: {
        updatePjaxLinks: function (store) {
            this.$forceUpdate();
            store.commit('newToggleKey');
        },
    },
};