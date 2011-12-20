<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN">
<html>
    <head>
        <title>LimeSurvey <?php $clang->eT('HTML Editor'); ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="robots" content="noindex, nofollow" />
    </head>

    <body>
        <div class="maintitle">
            LimeSurvey <?php $clang->eT('HTML Editor'); ?>
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
        <form  onsubmit="self.close()">
            <input type="submit" value="<?php $clang->eT('Close Editor'); ?>" />
            <input type="hidden" name="checksessionbypost" value="<?php echo Yii::app()->session['checksessionpost']; ?>" />
        </form>
    </body>
</html>
