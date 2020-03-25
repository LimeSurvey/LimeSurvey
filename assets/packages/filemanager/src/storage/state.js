export default {
    currentFolder : null,
    // Either "generalfiles", "global", or "survey"
    currentFolderType: null,
    // 0 = No survey folder selected
    currentSurveyId: 0,
    folderList: [],
    fileList: [],
    selectedFiles: [],
    inTransitFiles: [],
    debug: false,
    transitType: null,
    fileRepresentation: 'tablerep',
    uncollapsedFolders: [],
    renewIterator: 0
};
