import Vue from 'vue';
export default {
    setVisibility: (state, newState) => {
        state.visible = newState;
    },
    clean: (state) => {
        state.permissions = [];
        state.topbar_left_buttons = [];
        state.topbar_right_buttons = [];
        state.topbarextended_left_buttons = [];
        state.topbarextended_right_buttons = [];
    },
    setPermissions: (state, newState) => {
        Vue.set(state, 'permissions', newState);
    },
    setTopBarRight: (state, newState) => {
        Vue.set(state, 'topbar_right_buttons', newState);
    },
    setTopBarLeft: (state, newState) => {
        Vue.set(state, 'topbar_left_buttons', newState);
    },
    setTopBarExtendedRight: (state, newState) => {
        Vue.set(state, 'topbarextended_right_buttons', newState);
    },
    setTopBarExtendedLeft: (state, newState) => {
        Vue.set(state, 'topbarextended_left_buttons', newState);
    },
    setQid: (state, newState) => {
        state.qid = newState;
    },
    setGid: (state, newState) => {
        state.gid = newState;
    },
    setType: (state, newState) => {
        state.type = newState;
    },
    setSid: (state, newState) => {
        state.sid = newState;
    },
    setShowSaveButton: (state, newState) => {
        state.showSaveButton = newState;
    },
    setCloseButtonUrl: (state, newState) => {
        state.closeButtonUrl = newState;
    },
};
