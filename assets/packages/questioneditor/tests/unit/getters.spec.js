import getters from '../../src/storage/getters'

test('hasIndividualAnsweroptionCodes is true for empty answer options', () => {
    const state = {
        currentQuestionAnswerOptions: {}
    };
    const result = getters.hasIndividualAnsweroptionCodes(state);
    expect(result).toBe(true);
});

test('hasIndividualAnsweroptionCodes is true for empty answer options with scale id', () => {
    const state = {
        currentQuestionAnswerOptions: {"0": []}
    };
    const result = getters.hasIndividualAnsweroptionCodes(state);
    expect(result).toBe(true);
});

test('hasIndividualAnsweroptionCodes is true for one answer options', () => {
    const state = {
        currentQuestionAnswerOptions: {
            // NB: 0 = scale id
            "0": [
                {
                    aid: "1",
                    assessment_value: 0,
                    code: "AO01",
                    en: {
                        aid: "1",
                        answer: "Some answer",
                        id: "1",
                        language: "en"
                    },
                    qid: "1",
                    scale_id: "0",
                    sortorder: 1
                }
            ]
        }
    };
    const result = getters.hasIndividualAnsweroptionCodes(state);
    expect(result).toBe(true);
});

test('hasIndividualAnsweroptionCodes is true for three different answer options', () => {
    const state = {
        currentQuestionAnswerOptions: {
            // NB: 0 = scale id
            "0": [
                {
                    code: "SQ00",
                },
                {
                    code: "SQ01",
                },
                {
                    code: "SQ02",
                }
            ]
        }
    };
    const result = getters.hasIndividualAnsweroptionCodes(state);
    expect(result).toBe(true);
});

test('hasIndividualAnsweroptionCodes is true for three different answer options in object', () => {
    // This works since hasIndividualAnsweroptionCodes converts object into array.
    // Real bug is why PHP sends array as object with int indexes.
    const state = {
        currentQuestionAnswerOptions: {
            // NB: 0 = scale id
            "0": {
                "0": {
                    code: "SQ00",
                },
                "1": {
                    code: "SQ01",
                },
                "2": {
                    code: "SQ02",
                }
            }
        }
    };
    const result = getters.hasIndividualAnsweroptionCodes(state);
    expect(result).toBe(true);
});


test('hasIndividualAnsweroptionCodes is false for two same answer options', () => {
    const state = {
        currentQuestionAnswerOptions: {
            // NB: 0 = scale id
            "0": [
                {
                    code: "SQ01",
                },
                {
                    code: "SQ01",
                }
            ]
        }
    };
    const result = getters.hasIndividualAnsweroptionCodes(state);
    expect(result).toBe(false);
});
