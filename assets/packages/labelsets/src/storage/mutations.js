export default {
    setLanguages: (state, newData) => {
        state.languages = newData;
    },
    setLabels: (state, newData) => {
        state.labels = newData;
    },
    setLabelsImmutable: (state, newData) => {
        if(state.labelsImmutable == null) {
            state.labelsImmutable = newData;
        }
    },
    unsetLabelsImmutable: (state) => {
        state.labelsImmutable = null;
    },
    setActiveLanguage : (state, newValue) => {
        state.activeLanguage = newValue;
    },
    setLabelSetType: (state, newType) => {
        state.labelSetType = newType;
    }
};