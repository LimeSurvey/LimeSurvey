import ajax from '../mixins/runAjax';
import {LOG} from '../mixins/logSystem';

export default {
    bootstrapComponent:(context) => {
        return Promise.all([
            context.dispatch('getCurrentParameters'),
            context.dispatch('getCurrentParameters')
        ]);
    },
    getCurrentParameters: (context) => {
        return new Promise((resolve, reject) => {
            ajax.methods.$_get(window.PanelIntegrationData.getParametersUrl).then(
                (result) => {
                    const dataSet = [];
                    LS.ld.forEach(result.data.rows, function (row, i) {
                        let rowArray = {
                            'id': row.id,
                            'parameter': row.parameter,
                            'targetQuestionText': row.questionTitle,
                            'targetSubQuestionText': row.subquestionTitle,
                            'sid': row.sid,
                            'qid': row.targetqid || '',
                            'sqid': row.targetsqid || ''
                        };
                        dataSet.push(rowArray);
                    });
                    context.commit('setRowdata', dataSet);
                    resolve();
                },
                (error) => {
                    LOG.error(error);
                    reject(error);
                }
            );
        });
    },
    getCurrentQuestionlist: (context) => {
        return new Promise((resolve, reject) => {
            context.commit('setQuestionlist', window.PanelIntegrationData.questionList);
            resolve();
        });
    }
}