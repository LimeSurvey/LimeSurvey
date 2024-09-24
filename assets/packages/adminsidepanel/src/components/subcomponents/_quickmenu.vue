<script>
import ajaxMethods from '../../mixins/runAjax.js';

export default {
    mixins: [ajaxMethods],
    props: {
        'menuEntries' : {type: [Array,Object]},
        'activeMenuIndex': {type: String},
        loading: {type: Boolean, default: false}
    },
    data(){
        return { };
    },
    computed: {
        loadingState: {
            get() { return this.loading; },
            set(newState) { this.$emit('changeLoadingState', newState); }
        },
        sortedMenues(){
            return LS.ld.orderBy(this.$store.state.collapsedmenus,(a)=>{return parseInt((a.ordering || 999999)) }, ['asc']);
        }
    },
    methods:{
        sortedMenuEntries(entries) {
            const self = this;
            let orderedArray = LS.ld.orderBy(entries,(a)=>{return parseInt((a.ordering || 999999)) }, ['asc']);            
            return orderedArray;
        },
        setActiveMenuIndex(menuItem){
            let activeMenuIndex = menuItem.id;
            this.$store.commit('lastMenuItemOpen', menuItem)
        },
        compileEntryClasses(menuItem){
            let classes = "";
            if(this.$store.state.lastMenuItemOpen == menuItem.id){
                classes+=' btn-primary ';
            } else {
                classes+=' btn-outline-secondary ';
            }
            if(!menuItem.link_external){
                classes+=' pjax ';
            }
            return classes;
        },
        reConvertHTML(string) {
            var chars = ["'","©","Û","®","ž","Ü","Ÿ","Ý","$","Þ","%","¡","ß","¢","à","£","á","À","¤","â","Á","¥","ã","Â","¦","ä","Ã","§","å","Ä","¨","æ","Å","©","ç","Æ","ª","è","Ç","«","é","È","¬","ê","É","­","ë","Ê","®","ì","Ë","¯","í","Ì","°","î","Í","±","ï","Î","²","ð","Ï","³","ñ","Ð","´","ò","Ñ","µ","ó","Õ","¶","ô","Ö","·","õ","Ø","¸","ö","Ù","¹","÷","Ú","º","ø","Û","»","ù","Ü","@","¼","ú","Ý","½","û","Þ","€","¾","ü","ß","¿","ý","à","‚","À","þ","á","ƒ","Á","ÿ","å","„","Â","æ","…","Ã","ç","†","Ä","è","‡","Å","é","ˆ","Æ","ê","‰","Ç","ë","Š","È","ì","‹","É","í","Œ","Ê","î","Ë","ï","Ž","Ì","ð","Í","ñ","Î","ò","‘","Ï","ó","’","Ð","ô","“","Ñ","õ","”","Ò","ö","•","Ó","ø","–","Ô","ù","—","Õ","ú","˜","Ö","û","™","×","ý","š","Ø","þ","›","Ù","ÿ","œ","Ú"]; 
            var codes = ["&#039;","&copy;","&#219;","&reg;","&#158;","&#220;","&#159;","&#221;","&#36;","&#222;","&#37;","&#161;","&#223;","&#162;","&#224;","&#163;","&#225;","&Agrave;","&#164;","&#226;","&Aacute;","&#165;","&#227;","&Acirc;","&#166;","&#228;","&Atilde;","&#167;","&#229;","&Auml;","&#168;","&#230;","&Aring;","&#169;","&#231;","&AElig;","&#170;","&#232;","&Ccedil;","&#171;","&#233;","&Egrave;","&#172;","&#234;","&Eacute;","&#173;","&#235;","&Ecirc;","&#174;","&#236;","&Euml;","&#175;","&#237;","&Igrave;","&#176;","&#238;","&Iacute;","&#177;","&#239;","&Icirc;","&#178;","&#240;","&Iuml;","&#179;","&#241;","&ETH;","&#180;","&#242;","&Ntilde;","&#181;","&#243;","&Otilde;","&#182;","&#244;","&Ouml;","&#183;","&#245;","&Oslash;","&#184;","&#246;","&Ugrave;","&#185;","&#247;","&Uacute;","&#186;","&#248;","&Ucirc;","&#187;","&#249;","&Uuml;","&#64;","&#188;","&#250;","&Yacute;","&#189;","&#251;","&THORN;","&#128;","&#190;","&#252","&szlig;","&#191;","&#253;","&agrave;","&#130;","&#192;","&#254;","&aacute;","&#131;","&#193;","&#255;","&aring;","&#132;","&#194;","&aelig;","&#133;","&#195;","&ccedil;","&#134;","&#196;","&egrave;","&#135;","&#197;","&eacute;","&#136;","&#198;","&ecirc;","&#137;","&#199;","&euml;","&#138;","&#200;","&igrave;","&#139;","&#201;","&iacute;","&#140;","&#202;","&icirc;","&#203;","&iuml;","&#142;","&#204;","&eth;","&#205;","&ntilde;","&#206;","&ograve;","&#145;","&#207;","&oacute;","&#146;","&#208;","&ocirc;","&#147;","&#209;","&otilde;","&#148;","&#210;","&ouml;","&#149;","&#211;","&oslash;","&#150;","&#212;","&ugrave;","&#151;","&#213;","&uacute;","&#152;","&#214;","&ucirc;","&#153;","&#215;","&yacute;","&#154;","&#216;","&thorn;","&#155;","&#217;","&yuml;","&#156;","&#218;"];
            LS.ld.each(codes, (code, i) => {
                string = string.replace(code, chars[i]);
            });
            return string;
        }
    },
    created(){
        const self = this;
        this.$store.dispatch('getCollapsedmenus')
        .then(
            (result) => {},
            this.$log.error
        )
        .finally(
            (result) => { this.loadingState = false }
        );
    },
    mounted(){
    }
}
</script>
<template>
    <div class='ls-flex-column fill'>
        <div class="ls-space margin top-10" v-show="!loadingState"  v-for="menu in sortedMenues" :title="menu.title" v-bind:key="menu.title" >
            <div class="btn-group-vertical ls-space padding right-10">
                <a v-for="(menuItem) in sortedMenuEntries(menu.entries)" 
                @click="setActiveMenuIndex(menuItem)"
                v-bind:key="menuItem.id" 
                :href="menuItem.link" :title="reConvertHTML(menuItem.menu_description)" 
                :target="menuItem.link_external ? '_blank' : '_self'"
                data-bs-toggle="tooltip"
                class="btn btn-icon"
                :class="compileEntryClasses(menuItem)"
                >
                    <template v-if="menuItem.menu_icon_type == 'fontawesome'">
                        <i class="quickmenuIcon fa" :class="'fa-'+menuItem.menu_icon"></i>
                    </template>
                    <template v-else-if="menuItem.menu_icon_type == 'image'">
                        <img width="32px" :src="menuItem.menu_icon" />
                    </template>
                    <template v-else-if="menuItem.menu_icon_type == 'iconclass'">
                        <i class="quickmenuIcon"  :class="menuItem.menu_icon" ></i>
                    </template>
                    <template v-else-if="menuItem.menu_icon_type == 'remix'">
                        <i class="quickmenuIcon"  :class="menuItem.menu_icon" ></i>
                    </template>
                </a>
            </div>
        </div>
        <loader-widget v-if="loadingState" id="quickmenuLoadingIcon" extra-class="loader-quickmenu"/>
    </div>
</template>
<style lang="scss">
    .loader-adminpanel.loader-quickmenu .contain-pulse{
        width: 2em;
    }
</style>
