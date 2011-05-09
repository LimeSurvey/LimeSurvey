<div id="hide_<?php echo $id; ?>" style="display:none;"/>
		<script	type="text/javascript">
		 	var elem = $('#hide_<?php echo $id; ?>').parent();
		 	if(elem.is("li")){
				elem.css('display','none');
			}else{
				elem = elem.parent();
				if(elem.is("li")){
					elem.css('display','none');
				}else{
					elem = elem.parent();
					if(elem.is("li")){
						elem.css('display','none');
					}
				}			
			}
			
			
		</script>