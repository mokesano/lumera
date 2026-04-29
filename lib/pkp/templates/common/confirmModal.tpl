{**
 * templates/common/confirmModal.tpl
 *
 * [WIZDAM EDITION] Modern Template for Confirmation Modal
 * Replaces hardcoded PHP string concatenation.
 *}

<script type="text/javascript">
    $(function() {ldelim}
        // Binding tombol pemicu dengan fungsi Modal OJS
        var triggerSelector = '{$triggerButton|escape:"javascript"}';
        
        $(triggerSelector).click(function() {ldelim}
            modalConfirm(
                '{$confirmUrl|escape:"javascript"}', 
                '{$confirmActOnType|escape:"javascript"}', 
                '{$confirmActOnId|escape:"javascript"}', 
                '{$confirmBody|escape:"javascript"}', 
                ['{$confirmButton|escape:"javascript"}', '{$cancelButton|escape:"javascript"}'], 
                '{$triggerButton|escape:"javascript"}'
            );
            return false;
        {rdelim});
    {rdelim});
</script>

<div id="{$confirmId|escape}" style="display:none">
    <div class="confirm-dialog-content">
        <p>{$confirmBody}</p>
    </div>
</div>