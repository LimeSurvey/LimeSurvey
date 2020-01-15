import InlineEditorBase from '@ckeditor/ckeditor5-editor-inline/src/inlineeditor';

import EssentialsPlugin from '@ckeditor/ckeditor5-essentials/src/essentials';
import PasteFromOfficePlugin from '@ckeditor/ckeditor5-paste-from-office/src/pastefromoffice';
import FontPlugin from '@ckeditor/ckeditor5-font/src/font';
import ParagraphPlugin from '@ckeditor/ckeditor5-paragraph/src/paragraph';
import HeadingPlugin from '@ckeditor/ckeditor5-heading/src/heading';
import BoldPlugin from '@ckeditor/ckeditor5-basic-styles/src/bold';
import ItalicPlugin from '@ckeditor/ckeditor5-basic-styles/src/italic';
import UnderlinePlugin from '@ckeditor/ckeditor5-basic-styles/src/underline';
import BlockQuotePlugin from '@ckeditor/ckeditor5-block-quote/src/blockquote';
import AutoformatPlugin from '@ckeditor/ckeditor5-autoformat/src/autoformat';
import Alignment from '@ckeditor/ckeditor5-alignment/src/alignment';
import LinkPlugin from '@ckeditor/ckeditor5-link/src/link';
import ListPlugin from '@ckeditor/ckeditor5-list/src/list';
import MediaEmbed from '@ckeditor/ckeditor5-media-embed/src/mediaembed';
import ImagePlugin from '@ckeditor/ckeditor5-image/src/image';
import ImageCaptionPlugin from '@ckeditor/ckeditor5-image/src/imagecaption';
import ImageStylePlugin from '@ckeditor/ckeditor5-image/src/imagestyle';
import ImageToolbarPlugin from '@ckeditor/ckeditor5-image/src/imagetoolbar';
import ImageUploadPlugin from '@ckeditor/ckeditor5-image/src/imageupload';

import LsReplacements from './plugins/LsReplacements/LsReplacements';
import LsFileUploadPlugin from './plugins/LsImage/LsFileUploadPlugin';
import LsImageSelectPlugin from './plugins/LsImage/lsimageselect';
import LsImageSizePlugin from './plugins/LsImage/lsimagesize';

import './plugins/assets/styles.scss';

export default class LsCkEditorInline extends InlineEditorBase {}

LsCkEditorInline.builtinPlugins = [
        EssentialsPlugin,
        FontPlugin,
        PasteFromOfficePlugin,
        HeadingPlugin,
        BoldPlugin,
        ItalicPlugin,
        UnderlinePlugin,
        BlockQuotePlugin,
        AutoformatPlugin,
        Alignment,
        LinkPlugin,
        ListPlugin,
        ParagraphPlugin,
        MediaEmbed,
        ImagePlugin,
        ImageCaptionPlugin,
        ImageStylePlugin,
        ImageToolbarPlugin,
        ImageUploadPlugin,
        LsReplacements,
        LsFileUploadPlugin,
        LsImageSelectPlugin,
        LsImageSizePlugin,
    ];

LsCkEditorInline.defaultConfig = {
    toolbar: {
        items: [
            'heading',
            'fontSize',
            'fontColor',
            '|',
            'bold',
            'italic',
            'underline',
            'link',
            'alignment',
            '|',
            'bulletedList',
            'numberedList',
            'blockQuote',
            'undo',
            'redo',
            '|',
            'mediaEmbed',
            '|',
            'imageUpload',
            'selectImage',
            'expressions'
        ]
    },
    language: LS.data.language
};
