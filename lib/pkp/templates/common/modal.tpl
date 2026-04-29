{**
 * templates/common/modal.tpl
 *
 * [WIZDAM EDITION] General AJAX Modal Trigger
 *}

<script type="text/javascript">
    $(function() {ldelim}
        var buttonSelector = '{$modalButtonSelector|escape:"javascript"}';
        
        $(buttonSelector).click(function() {ldelim}
            // Panggil fungsi modal legacy OJS dengan parameter dari PHP
            modal(
                '{$modalUrl|escape:"javascript"}', 
                '{$modalActOnType|escape:"javascript"}', 
                '{$modalActOnId|escape:"javascript"}', 
                ['{translate|escape:"javascript" key="common.ok"}', '{translate|escape:"javascript" key="common.cancel"}'], 
                buttonSelector,
                '{$modalTitle|escape:"javascript"}'
            );
            return false;
        {rdelim});
    {rdelim});
</script>