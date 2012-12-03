<?php if (isset($secerror))
    {?>
    <span class='error'><?php echo $secerror;?></span><br />
    <?php
} ?>
<div id="wrapper">
    <p id="tokenmessage">
        <?php $clang->eT("This is a controlled survey. You need a valid token to participate.");?><br />
        <?php $clang->eT("If you have been issued a token, please enter it in the box below and click continue."); ?>
    </p>
    <script type='text/javascript'>var focus_element='#token';</script>
    <?php echo CHtml::form(array("/survey/index/sid/{$surveyid}"), 'post', array('id'=>'tokenform'));?>
        <ul>
            <li>
                <label for='token'><? $clang->eT("Token");?></label><input class='text <?php echo $kpclass?>' id='token' type='text' name='token' />";

                <input type='hidden' name='sid' value='<?php echo $surveyid;?>' id='sid' />
                <input type='hidden' name='lang' value='<?php echo $templang;?>' id='lang' />
                <?php
                    if ($newtest)
                    { ?>
                    <input type='hidden' name='newtest' value='Y' id='newtest' />
                    <?php
                    }

                    // If this is a direct Reload previous answers URL, then add hidden fields
                    if (isset($loadall) && isset($scid) && isset($loadname) && isset($loadpass))
                    {?>
                    <input type='hidden' name='loadall' value='<?php echo htmlspecialchars($loadall);?>' id='loadall' />
                    <input type='hidden' name='scid' value='<?php echo $scid;?>' id='scid' />
                    <input type='hidden' name='loadname' value='<?php echo htmlspecialchars($loadname);?>' id='loadname' />
                    <input type='hidden' name='loadpass' value='<?php echo htmlspecialchars($loadpass);?>' id='loadpass' />
                    <?php
                    }
                ?>
            </li>
            <?php
                if ($bCaptchaEnabled)
                {?>
                <li>
                    <label for='captchaimage'><?php $clang->eT("Security question");?></label>
                    <img id='captchaimage' src='<?php echo Yii::app()->getController()->createUrl('/verification/image/sid/'.$surveyid)?>' alt='captcha' />
                    <input type='text' size='5' maxlength='3' name='loadsecurity' value='' />
                </li>
                <?php
            }?>
            <li>
                <input class='submit' type='submit' value='<?php $clang->eT("Continue");?>' />
            </li>
        </ul>
    </form></div>";