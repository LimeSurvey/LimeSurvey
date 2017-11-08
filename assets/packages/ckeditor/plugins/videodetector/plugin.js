/*
*   Plugin developed by Netbroad, C.B.
*
*   LICENCE: GPL, LGPL, MPL
*   NON-COMMERCIAL PLUGIN.
*
*   Website: netbroad.eu
*   Twitter: @netbroadcb
*   Facebook: Netbroad
*   LinkedIn: Netbroad
*
*/

CKEDITOR.plugins.add( 'videodetector', {
    icons: 'videodetector',
    init: function( editor ) {

        var pluginDirectory = this.path;
        editor.addContentsCss(pluginDirectory + '/videodetector.css');

        editor.addCommand('videodetector', new CKEDITOR.dialogCommand('videoDialog'));
        editor.ui.addButton( 'VideoDetector', {
            label: 'Insert a Youtube, Vimeo or Dailymotion video',
            command: 'videodetector',
            icon: CKEDITOR.plugins.getPath('videodetector') + '/icons/icon_black.png'
        });

        CKEDITOR.dialog.add('videoDialog', this.path + 'dialogs/videoDialog.js');

    }
});
