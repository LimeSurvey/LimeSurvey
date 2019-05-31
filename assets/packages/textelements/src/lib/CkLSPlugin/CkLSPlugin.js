import * as LsIcon from './LsIcon.svg';

class LsPlaceholders extends Plugin {
    init() {
        const editor = this.editor;

        editor.ui.componentFactory.add( 'lsplaceholder', locale => {
            const view = new ButtonView(locale);
            const LsIconObject = new IconView();

            icon.set({
                content: LsIcon
            });

            view.set({
                label: window.TextEditData.i10N['LimeSurvey Placeholder'] || 'LimeSurvey Placeholder',
                iconView: LsIconObject
            });

            view.on('execute', () => {
                
            });
        });
    }
}