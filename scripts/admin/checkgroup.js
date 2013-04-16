(function($){
	$.fn.checkgroup = function(options){
		//merge settings
		settings=$.extend({
			groupSelector:null,
			groupName:'group_name',
			enabledOnly:false
		},options || {});
		
		var ctrl_box=this;

		
		//allow a group selector override option
		var grp_slctr = (settings.groupSelector==null) ? 'input[name='+settings.groupName+']' : settings.groupSelector;
		
		//grab only enabled checkboxes if required
		if(settings.enabledOnly)
		{
			grp_slctr += ':enabled';
		}

		//attach click event to the "check all" checkbox(s)
		ctrl_box.click(function(e){
			chk_val=(e.target.checked);
			$(grp_slctr).attr('checked',chk_val);
			//if there are other "select all" boxes, sync them
			ctrl_box.attr('checked',chk_val);
		});
		//attach click event to checkboxes in the "group"
		$(grp_slctr).click(function(){
			if(!this.checked)
			{
				ctrl_box.attr('checked',false);
			}
			else
			{
				//if # of chkbxes is equal to # of chkbxes that are checked
				if($(grp_slctr).size()==$(grp_slctr+':checked').size()){
					ctrl_box.attr('checked','checked');
				}
			}
		});
		//make this function chainable within jquery
		return this;
	};						
})(jQuery);	