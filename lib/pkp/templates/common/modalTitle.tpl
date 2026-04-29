{**
 * templates/common/modalTitle.tpl
 *
 * [WIZDAM EDITION] Custom Title Bar for Modals
 *}

<script type="text/javascript">
    // Fix untuk menimpa title bar bawaan jQuery UI yang kaku
    $(function() {ldelim}
        var containerId = '{$modalSelector|escape:"javascript"}';
        
        // Hapus titlebar default jQuery UI
        $(containerId).last().parent().prev('.ui-dialog-titlebar').remove();
        
        // Bind tombol close custom kita
        $('a.wizdam-close-modal').click(function() {ldelim}
            $(this).closest('.ui-dialog').find('.ui-dialog-content').dialog('close');
            return false;
        {rdelim});
    {rdelim});
</script>

<div class="pkp_controllers_modal_titleBar wizdam-modal-header">
    <div class="wizdam-modal-title-group">
        {if $modalIcon}
            <span class="icon {$modalIcon|escape}"></span>
        {/if}
        <h3 class="text">{$modalTitle|escape}</h3>
    </div>
    
    {if $modalCanClose}
        <a class="wizdam-close-modal close ui-corner-all" href="#" role="button">
            <span class="ui-icon ui-icon-closethick">X</span>
        </a>
    {/if}
    
    <span style="clear:both"></span>
</div>