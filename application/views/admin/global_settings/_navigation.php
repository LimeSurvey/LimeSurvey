<?php
/**
 * This view display the navigation settings, such as the welcome page parameters
 *
 * @var int $boxes
 *
 */
?>
<?php
    $startpageValue='';
    $startpagePlaceHolder='';
?>

<h4><?php eT("Home page button configuration");?></h4>
<table class="table">
    <thead>
        <tr>
            <th>
                <?php eT('Title');?>
            </th>
            <th>
                <?php eT('URL');?>
            </th>
            <th>
                <?php eT('Description');?>
            </th>
            <th>
                <?php eT('Image');?>
            </th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($boxes as $box):?>
            <tr class="">
                <td>
                    <input type="text" name="box-title-<?php echo $box->id;?>" id="box-title-<?php echo $box->id;?>" value="<?php echo $box->title;?>"/>
                </td>
                <td>
                    <label>
                        <?php echo Yii::app()->getBaseUrl(true);?>/
                        <?php
                            if(Yii::app()->urlManager->getUrlFormat()=='path')
                            {
                                echo '/index.php/';
                            }
                            else
                            {
                                 echo '/index.php?r=';
                            }
                        ?>
                    </label>
                    <input type="text" name="box-url-<?php echo $box->id;?>" id="box-url-<?php echo $box->id;?>" value="<?php echo $box->url;?>"/>
                </td>
                <td>
                    <input type="text" name="box-desc-<?php echo $box->id;?>" id="box-desc-<?php echo $box->id;?>" value="<?php echo $box->desc;?>"/>
                </td>
                <td>
                    <input type="text" name="box-img-<?php echo $box->id;?>" id="box-img-<?php echo $box->id;?>" value="<?php echo $box->img;?>"/>
                </td>
        <?php endforeach;?>
    </tbody>
</table>