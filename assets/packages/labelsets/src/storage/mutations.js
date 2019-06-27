export default {
    setLanguages: (state, newData) => {
        state.languages = newData;
    },
    setLabels: (state, newData) => {
        state.labels = newData;
    },
    setActiveLanguage : (state, newValue) => {
        state.activeLanguage = newValue;
    },
    setLabelSetType: (state, newType) => {
        state.labelSetType = newType;
    }
};