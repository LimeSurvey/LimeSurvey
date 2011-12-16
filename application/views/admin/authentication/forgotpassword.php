			<form class="form44" name="forgotpassword" id="forgotpassword" method="post" action="<?php echo $this->createUrl("admin/authentication/forgotpassword");?>" >
				<p><strong><?php echo $clang->gT('You have to enter user name and email.');?></strong></p>

				<ul>
						<li><label for="user"><?php echo $clang->gT('Username');?></label><input name="user" id="user" type="text" size="60" maxlength="60" value="" /></li>
						<li><label for="email"><?php echo $clang->gT('Email');?></label><input name="email" id="email" type="text" size="60" maxlength="60" value="" /></li>
						<p><input type="hidden" name="action" value="forgotpass" />
						<input class="action" type="submit" value="<?php echo $clang->gT('Check Data');?>" />
						<p><a href="<?php echo $this->createUrl("admin");?>"><?php echo $clang->gT('Main Admin Screen');?></a>
			</form>
            <p>&nbsp;</p>