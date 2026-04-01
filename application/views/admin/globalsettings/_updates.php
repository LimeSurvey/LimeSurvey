<?php
/**
 * This view generates the Updates tab inside global settings.
 */
?>
<?php $minimumUpdateStability = Yii::app()->getConfig('minimum_update_stability'); ?>
<div class="container">
    <div class="mb-3">
        <label class="form-label" for="minimum_update_stability">
            <?php eT("Minimum stability for update notifications:"); ?>
        </label>
        <div>
            <select class="form-select" id="minimum_update_stability" name="minimum_update_stability">
                <option value="alpha" <?php echo ($minimumUpdateStability === 'alpha') ? 'selected' : ''; ?>>
                    <?php eT("Alpha"); ?>
                </option>
                <option value="beta" <?php echo ($minimumUpdateStability === 'beta') ? 'selected' : ''; ?>>
                    <?php eT("Beta"); ?>
                </option>
                <option value="rc" <?php echo ($minimumUpdateStability === 'rc') ? 'selected' : ''; ?>>
                    <?php eT("Release Candidate"); ?>
                </option>
                <option value="stable" <?php echo ($minimumUpdateStability === 'stable') ? 'selected' : ''; ?>>
                    <?php eT("Stable"); ?>
                </option>
            </select>
        </div>
        <div class="form-text">
            <?php eT("Choose the minimum stability level for which update notifications should be shown. 'Alpha' will show all updates, 'Stable' will only show stable releases."); ?>
        </div>
    </div>
</div>
