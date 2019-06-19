import ClassicEditorBase from '@ckeditor/ckeditor5-editor-classic/src/classiceditor';

import EssentialsPlugin from '@ckeditor/ckeditor5-essentials/src/essentials';
import AutoformatPlugin from '@ckeditor/ckeditor5-autoformat/src/autoformat';
import BoldPlugin from '@ckeditor/ckeditor5-basic-styles/src/bold';
import ItalicPlugin from '@ckeditor/ckeditor5-basic-styles/src/italic';
import BlockQuotePlugin from '@ckeditor/ckeditor5-block-quote/src/blockquote';
import HeadingPlugin from '@ckeditor/ckeditor5-heading/src/heading';
import Alignment from '@ckeditor/ckeditor5-alignment/src/alignment';
import LinkPlugin from '@ckeditor/ckeditor5-link/src/link';
import ListPlugin from '@ckeditor/ckeditor5-list/src/list';
import TablePlugin from '@ckeditor/ckeditor5-table/src/table';
import ParagraphPlugin from '@ckeditor/ckeditor5-paragraph/src/paragraph';
import ClipboardPlugin from '@ckeditor/ckeditor5-clipboard/src/clipboard';
import PasteFromOffice from '@ckeditor/ckeditor5-paste-from-office/src/pastefromoffice';
import LsReplacements from './plugins/LsReplacements/LsReplacements';
import ImagePlugin from '@ckeditor/ckeditor5-image/src/image';
import ImageCaptionPlugin from '@ckeditor/ckeditor5-image/src/imagecaption';
import ImageStylePlugin from '@ckeditor/ckeditor5-image/src/imagestyle';
import ImageToolbarPlugin from '@ckeditor/ckeditor5-image/src/imagetoolbar';
import ImageUploadPlugin from '@ckeditor/ckeditor5-image/src/imageupload';
import LsFileUploadPlugin from './plugins/LsImage/LsFileUploadPlugin';
// import ImageSelectPlugin from './plugins/LsImage/lsimageselect';

export default class ClassicEditor extends ClassicEditorBase {}

ClassicEditor.builtinPlugins = [
    EssentialsPlugin,
    AutoformatPlugin,
    BoldPlugin,
    ItalicPlugin,
    BlockQuotePlugin,
    HeadingPlugin,
    Alignment,
    LinkPlugin,
    ListPlugin,
    TablePlugin,
    ParagraphPlugin,
    ClipboardPlugin,
    PasteFromOffice,
    LsReplacements,
    LsFileUploadPlugin,
    ImagePlugin,
    ImageCaptionPlugin,
    ImageStylePlugin,
    ImageToolbarPlugin,
    ImageUploadPlugin,
];

ClassicEditor.defaultConfig = {
    toolbar: {
        items: [
            'heading',
            '|',
            'bold',
            'italic',
            'link',
            'alignment',
            '|',
            'bulletedList',
            'numberedList',
            'blockQuote',
            'undo',
            'redo',
            'insertTable',
            '|',
            'imageUpload',
            'expressions'
        ]
    },
    image: {
        toolbar: [
            'imageStyle:full',
            'imageStyle:side',
            '|',
            'imageTextAlternative'
        ]
    },
    language: LS.data.language
};