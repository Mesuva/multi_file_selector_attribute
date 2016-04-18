<?php defined('C5_EXECUTE') or die("Access Denied."); ?>

<?php
$typeList = array();
$typeList['file'] = t('Any file type');
$typeList['image'] = t('Image file types');
$typeList['video'] = t('Video file types');
$typeList['text'] = t('Text file types');
$typeList['audio'] = t('Audio file types');
$typeList['doc'] = t('Document file types');
$typeList['app'] = t('Application file types');
?>

<fieldset class="ccm-attribute ccm-attribute-multipage">
    <legend><?php echo t('Restrictions')?></legend>
    <div class="form-group">
        <label><?php echo t("Selectable File Types")?></label>
        <select class="form-control" name="akType" id="akType">
            <?php if (is_array($typeList)) {
                foreach ($typeList as $type=>$label) { ?>
                    <option value="<?php echo $type ?>" <?php if ($type == $akType) { ?> selected <?php } ?>>
                        <?php echo $label; ?>
                    </option>
                    <?php
                }
            }
            ?>
        </select>
    </div>
</fieldset>