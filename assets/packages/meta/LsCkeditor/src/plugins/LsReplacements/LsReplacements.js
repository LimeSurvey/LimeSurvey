import Plugin from '@ckeditor/ckeditor5-core/src/plugin';

import LsReplacementsEditing from './LsReplacementsEditing.js';
import LsReplacementsUI from './LsReplacementsUI.js';

export default class LsReplacements extends Plugin {
    static get requires() {
        return [ LsReplacementsEditing, LsReplacementsUI ];
    }
}