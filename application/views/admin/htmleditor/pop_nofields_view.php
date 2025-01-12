<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN">
<html>
    <head>
        <title>GititSurvey <?php eT('HTML editor'); ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="robots" content="noindex, nofollow" />
        <?php
            App()->getClientScript()->registerPackage('jqueryui');            
            App()->getClientScript()->registerCssFile(Yii::app()->getConfig('publicstyleurl') . 'jquery-ui.css');
        ?>
    </head>

    <body>
        <div class="maintitle">
            GititSurvey <?php eT('HTML editor'); ?>
        </div>
        <hr />

        <table>
            <tr>
                <td align="center">
                    <br />
                    <span style="color:red;"><strong></strong></span>
                    <br />
                </td>
            </tr>
        </table>
        <form onsubmit="self.close()">
            <input type="submit" value="<?php eT('Close editor'); ?>" />
        </form>
    </body>
</html>
