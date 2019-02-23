const fs = require('fs');
const path = require('path');
const cwd = process.cwd();

const minimalContent = `<?php

class {filename} extends QuestionBaseDataSet {}`;

const applyDataSetFile = function(folderDirent) {
    const filesList = fs.readdirSync(path.join(cwd, folderDirent));
    let sQuestionTypes = '';
    filesList.forEach((file, idn) => {
        if(/^NameSpace.*$/.test(file)) {
            fs.unlinkSync(path.join(cwd, folderDirent, file));
        } else if(/^LoadQuestionTypes.*$/.test(file)) {
            fs.unlinkSync(path.join(cwd, folderDirent, file));
        } else {
            console.log(`${file}`)
            let filecontent = fs.readFileSync(path.join(cwd, folderDirent, file), {encoding: 'utf8'});
            let toWrite = filecontent.replace(/namespace QuestionTypes;\n/, '');
            fs.writeFileSync(path.join(cwd, folderDirent, file), toWrite);
        }
    });
    return sQuestionTypes;
}


fs.readdir(path.normalize(cwd) ,(err, files) => {
    if(err) throw err;
    let toWrite = '';
    files.forEach((file, i) => {
        //console.log(file);
        if(fs.statSync(path.join(cwd,file)).isDirectory()) {
            //toWrite += applyDataSetFile(file);
            toWrite += `Yii::import('questiontypes.${file.toLowerCase()}.*');
`
        }
    });
    let filecontent = fs.readFileSync(path.join(cwd, 'LoadQuestionTypes.php'), {encoding: 'utf8'});
    fs.writeFileSync(path.join(cwd, 'LoadQuestionTypes.php'), filecontent+"\n"+toWrite);
});