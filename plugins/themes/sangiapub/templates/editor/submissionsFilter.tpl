{**
 * templates/manager/submissions/submissionsFilter.tpl
 * Modern submissions filter form for Wizdam OJS v7
 * Compatible with OJS v2.4.8.2 backend structure
 *}

<div class="filter-container" id="wizdamFilterContainer">
    <div class="filter-header">
        <h4>{translate key="editor.submissions.filterSubmissions"}</h4>
    </div>

    {* Debug info - remove in production *}
    {if $smarty.const.DEBUG}
        <div style="background: #f0f0f0; padding: 10px; margin-bottom: 10px; font-size: 12px;">
            <strong>Debug Info:</strong><br>
            dateSearchField: {$dateSearchField|default:"not set"}<br>
            dateFrom: {$dateFrom|default:"not set"}<br>
            dateTo: {$dateTo|default:"not set"}<br>
            Available date fields: {$dateFieldOptions|@print_r}
        </div>
    {/if}

    {* Active Filters Display *}
    <div class="active-filters" id="activeFilters"></div>

    <form id="filterForm" method="post" action="{url op="submissions" path=$pageToDisplay}">
        <input type="hidden" name="csrfToken" value="{$csrfToken|escape}" />
        {* Quick Assignment Filters *}
        <div class="filter-grid">
            <div class="form-group">
                <label for="filterEditor">{translate key="editor.submissions.assignedTo"}</label>
                <select id="filterEditor" name="filterEditor" class="form-control modern-select">
                    <option value="">{translate key="common.all"} {translate key="user.role.editors"}</option>
                    {html_options options=$editorOptions selected=$filterEditor}
                </select>
            </div>

            <div class="form-group">
                <label for="filterSection">{translate key="editor.submissions.inSection"}</label>
                <select id="filterSection" name="filterSection" class="form-control modern-select">
                    <option value="">{translate key="common.all"} {translate key="section.sections"}</option>
                    {html_options options=$sectionOptions selected=$filterSection}
                </select>
            </div>
        </div>

        {* Search Section *}
        <div class="search-section">
            <div class="search-row">
                <div class="form-group">
                    <label for="searchField">{translate key="common.searchIn"}</label>
                    <select id="searchField" name="searchField" class="form-control modern-select">
                        {html_options_translate options=$fieldOptions selected=$searchField}
                    </select>
                </div>

                <div class="form-group">
                    <label for="searchMatch">{translate key="common.match"}</label>
                    <select id="searchMatch" name="searchMatch" class="form-control modern-select">
                        <option value="contains"{if $searchMatch == 'contains'} selected="selected"{/if}>{translate key="form.contains"}</option>
                        <option value="is"{if $searchMatch == 'is'} selected="selected"{/if}>{translate key="form.is"}</option>
                        <option value="startsWith"{if $searchMatch == 'startsWith'} selected="selected"{/if}>{translate key="form.startsWith"}</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="searchText">{translate key="common.searchText"}</label>
                    <input type="text" id="searchText" name="search" class="form-control" 
                           value="{$search|escape}" placeholder="{translate key="common.enterSearchTerms"}">
                </div>
            </div>
        </div>

        {* Date Range Section *}
        <div class="date-section">
            <div class="date-row">
                <div class="form-group">
                    <label for="dateSearchField">{translate key="common.dateField"}</label>
                    <select id="dateSearchField" name="dateSearchField" class="form-control modern-select">
                        <option value="">{translate key="common.selectDateField"}</option>
                        {html_options_translate options=$dateFieldOptions selected=$dateSearchField}
                    </select>
                </div>

                <div class="connector-text">{translate key="common.between"}</div>

                <div class="form-group">
                    <label for="dateFrom">{translate key="common.from"}</label>
                    <div class="date-input-wrapper">
                        <input type="text" id="dateFrom" name="dateFrom" class="form-control date-input" 
                               value="{if $dateFrom}{$dateFrom|date_format:"%Y-%m-%d"}{/if}"
                               placeholder="{translate key="common.selectStartDate"}" readonly>
                        <span class="date-icon">📅</span>
                    </div>
                </div>

                <div class="connector-text">{translate key="common.and"}</div>

                <div class="form-group">
                    <label for="dateTo">{translate key="common.to"}</label>
                    <div class="date-input-wrapper">
                        <input type="text" id="dateTo" name="dateTo" class="form-control date-input" 
                               value="{if $dateTo}{$dateTo|date_format:"%Y-%m-%d"}{/if}"
                               placeholder="{translate key="common.selectEndDate"}" readonly>
                        <span class="date-icon">📅</span>
                    </div>
                </div>
            </div>
        </div>

        {* Action Buttons *}
        <div class="button-group">
            <button type="button" class="btn btn-secondary" onclick="clearAllFilters()">{translate key="common.clearAll"}</button>
            <button type="submit" class="btn btn-primary">{translate key="common.search"}</button>
        </div>

        {* Hidden fields for OJS v2.4.8.2 backend compatibility *}
        <input type="hidden" name="sort" value="{$sort|default:"id"}">
        <input type="hidden" name="sortDirection" value="{$sortDirection|default:"ASC"}">
        
        {* Date components *}
        <input type="hidden" name="dateFromDay" id="dateFromDay" value="">
        <input type="hidden" name="dateFromMonth" id="dateFromMonth" value="">
        <input type="hidden" name="dateFromYear" id="dateFromYear" value="">
        <input type="hidden" name="dateToDay" id="dateToDay" value="">
        <input type="hidden" name="dateToMonth" id="dateToMonth" value="">
        <input type="hidden" name="dateToYear" id="dateToYear" value="">
        <input type="hidden" name="dateToHour" value="23">
        <input type="hidden" name="dateToMinute" value="59">
        <input type="hidden" name="dateToSecond" value="59">
    </form>
</div>

{* Minimal configuration script *}
{literal}
<script>
window.wizdamFilterConfig = {
    debug: {/literal}{if $smarty.const.DEBUG}true{else}false{/if}{literal}
};
</script>
{/literal}