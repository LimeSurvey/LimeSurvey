import InlineEditorBase from '@ckeditor/ckeditor5-editor-inline/src/inlineeditor';

import EssentialsPlugin from '@ckeditor/ckeditor5-essentials/src/essentials';
import BoldPlugin from '@ckeditor/ckeditor5-basic-styles/src/bold';
import ItalicPlugin from '@ckeditor/ckeditor5-basic-styles/src/italic';
import BlockQuotePlugin from '@ckeditor/ckeditor5-block-quote/src/blockquote';
import HeadingPlugin from '@ckeditor/ckeditor5-heading/src/heading';
import Alignment from '@ckeditor/ckeditor5-alignment/src/alignment';
import LinkPlugin from '@ckeditor/ckeditor5-link/src/link';
import ListPlugin from '@ckeditor/ckeditor5-list/src/list';
import ParagraphPlugin from '@ckeditor/ckeditor5-paragraph/src/paragraph';
import PasteFromOffice from '@ckeditor/ckeditor5-paste-from-office/src/pastefromoffice';
import LsReplacements from './plugins/LsReplacements/LsReplacements';
import ImagePlugin from '@ckeditor/ckeditor5-image/src/image';
import ImageStylePlugin from '@ckeditor/ckeditor5-image/src/imagestyle';
import ImageUploadPlugin from '@ckeditor/ckeditor5-image/src/imageupload';
import LsFileUploadPlugin from './plugins/LsImage/LsFileUploadPlugin';
import LsImageSelectPlugin from './plugins/LsImage/lsimageselect';
import LsImageSizePlugin from './plugins/LsImage/lsimagesize';

import './plugins/assets/styles.scss';

export default class LsCkEditorInline extends InlineEditorBase {}

LsCkEditorInline.builtinPlugins = [
    EssentialsPlugin,
    BoldPlugin,
    ItalicPlugin,
    BlockQuotePlugin,
    HeadingPlugin,
    Alignment,
    LinkPlugin,
    ListPlugin,
    ParagraphPlugin,
    PasteFromOffice,
    LsReplacements,
    ImagePlugin,
    ImageStylePlugin,
    ImageUploadPlugin,
    LsFileUploadPlugin,
    LsImageSelectPlugin,
    LsImageSizePlugin
];

LsCkEditorInline.defaultConfig = {
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
            '|',
            'imageUpload',
            'selectImage',
            'expressions'
        ]
    },
    language: LS.data.language
};
