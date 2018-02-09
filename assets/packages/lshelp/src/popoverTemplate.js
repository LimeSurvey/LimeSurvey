/* globals jQuery */

const popoverTemplate = function(replacements){
    
    const defaultReplacements = {
        more: 'More on this:',
        icon: 'fa-question'
    };
    
    const oReplacements = jQuery.extend({},defaultReplacements,replacements);
    
    const basicTemplate = () => {
        return `
<div class="popover" role="tooltip">
    <div class="arrow"></div>
    <h3 class="popover-title"><i class="fa ${oReplacements.icon}"></i> </h3>
    <div class="popover-content">
    </div>
</div>
`;
    };

    const moreTemplate = (link) => {
        return `
<div class="lshelp-popover-footer">
    ${oReplacements.more} 
    <a href="${link.href}" title="${link.title}" target="_blank">${link.text}</a>
</div>
`;
    };
    return {
        basicTemplate : basicTemplate,
        moreTemplate : moreTemplate
    }   ;
};

export default popoverTemplate;
