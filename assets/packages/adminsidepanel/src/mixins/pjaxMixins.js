export default {
    methods: {
        updatePjaxLinks: function (store) {
            this.$forceUpdate();
            store.commit('newToggleKey');
        },
    },
};