<?php
/**
 * @var $showtotals,
 * @var $row_head,
 * @var $total,
 * @var $q_table_id
 * @var $radix
 * @var $name 
 */
?>
            <?php if($showtotals):?>
                <tr class="total">
                    <?php echo $row_head; ?>
                    <?php echo $total; ?>
                </tr>
            <?php endif;?>
        </tbody>
    </table>
</div>

<?php if(!empty($q_table_id)): ?>
    <script type="text/javascript">
    <!--
        new multi_set('<?php echo $q_table_id; ?>','<?php echo $radix; ?>')
    // -->
    </script>
<?php else: ?>
    <script type="text/javascript">
    <!--
        $('#question<?php echo $name;?> .question').delegate('input[type=text]:visible:enabled','blur keyup',function(event){
            <?php echo $checkconditionFunction;?>($(this).val(), $(this).attr('name'), 'text');
            return true;
        })
    // -->
    </script>
<?php endif;?>
