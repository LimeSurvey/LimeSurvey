<script>
import Menuicon from './_menuicon.vue';

export default {
    name: 'submenu',
    components : {
        Menuicon
    },
    props: {
        'menu' : {type: [Object,Array], required: true},
    },
    computed: {
        sortedMenuEntries() {
            return LS.ld.orderBy(this.menu.entries,(a)=>{return parseInt((a.ordering || 999999)) }, ['asc']);
        },
    },
    methods:{
        getHref(menuItem) {
            let menuItemPartial = menuItem.partial.split('/').pop();
            return LS.createUrl(window.GlobalSideMenuData.baseLinkUrl, {partial: menuItemPartial});
        },
        setActiveMenuItemIndex(menuItem){
            let activeMenuIndex = menuItem.id;
            this.$store.commit('setLastMenuItemOpen', menuItem.partial.split('/').pop());
            this.$log.log('Opened Menuitem', menuItem.partial.split('/').pop());
        },
        debugOut(obj){
            return JSON.stringify(obj);
        },
        getLinkClass(menuItem){
            let classes = "ls-flex-row nowrap ";
            classes += (this.$store.state.lastMenuItemOpen==menuItem.partial.split('/').pop() ? 'selected ' : ' ' );
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
        //first load old settings from localStorage
        
    },
    mounted(){
        const self = this;
        this.updatePjaxLinks();
        this.redoTooltips();
    }
}
</script>
<template>
    <ul class="list-group subpanel col-12" :class="'level-'+(menu.level)">        
        <a v-for="(menuItem) in sortedMenuEntries" 
            v-bind:key="menuItem.id" 
            v-on:click.stop="setActiveMenuItemIndex(menuItem)"  
            :href="getHref(menuItem)" 
            :target="menuItem.link_external == true ? '_blank' : ''"
            :id="'sidemenu_'+menuItem.name" 
            class="list-group-item"
            :class="getLinkClass(menuItem)" >

            <div class="col-12" :class="menuItem.menu_class" 
            v-bind:title="reConvertHTML(menuItem.menu_description)"  
            data-bs-toggle="tooltip" >
                <div class="ls-space padding all-0" v-bind:class="$store.state.lastMenuItemOpen == menuItem.id ? 'col-md-10' : 'col-12' ">
                    <menuicon :icon-type="menuItem.menu_icon_type" :icon="menuItem.menu_icon"></menuicon>
                    <span v-html="menuItem.menu_title"></span>
                    <i class=" ri-external-link-fill" v-if="menuItem.link_external == true">&nbsp;</i>
                </div>
                <div class="col-md-2 text-center ls-space padding all-0 background white" v-show="$store.state.lastMenuItemOpen == menuItem.id">
                    <i class="ri-arrow-right-s-line">&nbsp;</i>
                </div>
                
            </div>
        </a>
    </ul>
</template>
<style lang="scss">

</style>
