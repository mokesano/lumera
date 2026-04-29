{**
 * templates/manager/categories/categories.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display list of categories in journal management.
 *
 *}
{strip}
{assign var="pageTitle" value="admin.categories"}
{assign var="pageId" value="admin.categories"}
{assign var="pageDisplayed" value="site"}
{include file="common/header.tpl"}
{/strip}

<script type="text/javascript">
{literal}
$(document).ready(function() { setupTableDND("#dragTable", "moveCategory"); });
{/literal}
</script>

<form action="{url op="setCategoriesEnabled"}" method="post">
	<p class="alert-text">{translate key="admin.categories.enable.description"}</p>
	<input type="radio" id="categoriesEnabledOff" {if !$categoriesEnabled}checked="checked" {/if}name="categoriesEnabled" value="0"/>&nbsp;<label for="categoriesEnabledOff">{translate key="admin.categories.disableCategories"}</label><br/>
	<input type="radio" id="categoriesEnabledOn" {if $categoriesEnabled}checked="checked" {/if}name="categoriesEnabled" value="1"/>&nbsp;<label for="categoriesEnabledOn">{translate key="admin.categories.enableCategories"}</label><br/>
	<input type="submit" value="{translate key="common.record"}" class="button defaultButton"/>
</form>

<div id="categories" class="categories-list u-mt-16">
    <table width="100%" class="listing" id="dragTable">
    	<tr>
    		<td colspan="2" class="headseparator">&nbsp;</td>
    	</tr>
    	<tr class="heading" valign="center">
    		<td width="75%">{translate key="admin.categories.name"}</td>
    		<td width="25%">{translate key="common.action"}</td>
    	</tr>
    	<tr>
    		<td colspan="2" class="headseparator">&nbsp;</td>
    	</tr>
        {iterate from=categories item=category key=categoryId}
        	<tr valign="top" id="category-{$categoryId|escape}" class="data">
        		<td class="drag">
        			{$category|escape}
        		</td>
        		<td>
        			<a href="{url op="editCategory" path=$categoryId}" class="action">{translate key="common.edit"}</a>&nbsp;|&nbsp;<a href="{url op="deleteCategory" path=$categoryId}" onclick="return confirm('{translate|escape:"jsparam" key="admin.categories.confirmDelete"}')" class="action">{translate key="common.delete"}</a>&nbsp;|&nbsp;<a href="{url op="moveCategory" d=u id=$categoryId}">&uarr;</a>&nbsp;<a href="{url op="moveCategory" d=d id=$categoryId}">&darr;</a>
        		</td>
        	</tr>
        {/iterate}
    </table>
</div>

<a href="{url op="createCategory"}" class="button u-hide">{translate key="admin.categories.create"}</a>
<input type="button" value="{translate key="admin.categories.create"}" class="button" onclick="document.location.href='{url op="createCategory"}'">

{if $categories->wasEmpty()}
    <div class="container cleared container-type-title" data-container-type="title">
        <div class="border-top-1 border-gray-medium"></div>
        <div class="c-empty-state-card__container u-flexbox u-justify-content-center u-align-items-center">
            <div class="c-empty-state-card__img u-flexbox u-justify-content-center u-align-items-center"><svg width="42" height="42" viewBox="0 0 42 42" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="New-File-Dash--Streamline-Core 1"><g id="New-File-Dash--Streamline-Core.svg"><path id="Vector" d="M19.5 1.5H27L37.5 12V24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_2" d="M31.5 40.5H34.5C35.2956 40.5 36.0588 40.1838 36.6213 39.6213C37.1838 39.0588 37.5 38.2956 37.5 37.5V34.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_3" d="M18 40.5H24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_4" d="M10.5 1.5H7.5C6.70434 1.5 5.94129 1.81607 5.37867 2.37868C4.81608 2.94129 4.5 3.70434 4.5 4.5V7.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_5" d="M4.5 18V24" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector_6" d="M4.5 34.5V37.5C4.5 38.2956 4.81608 39.0588 5.37867 39.6213C5.94129 40.1838 6.70434 40.5 7.5 40.5H10.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><path id="Vector 2529" d="M25.5 1.5V13.5H37.5" stroke="#536179" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path></g></g></svg>
            </div>
            <div class="c-empty-state-card__text search-tips">
                <h2 class="c-empty-state-card__text--title headline-5">{translate key="admin.categories.noneCreated"}</h2>
                <div class="c-empty-state-card__text--description">The system currently contains no journal categories for organizing publications. Use the <a href="{url op="createCategory"}">Create Category</a> function to establish subject classifications that will help readers discover relevant content and improve the organization of your journal collections across different academic disciplines.</div>
            </div>
        </div>
    </div>
    <div class="u-hide">
    	<tr>
    		<td colspan="2" class="nodata"></td>
    	</tr>
    	<tr>
    		<td colspan="2" class="endseparator">&nbsp;</td>
    	</tr>
    </div>
{else}
	<div id="colspan" class="colspan u-mb-0" >	    
	    <section class="u-display-flex u-justify-content-center u-mt-24 u-mb-24">
	        <div class="c-pagination">View {page_info iterator=$categories}</div>
        </section>
        {if $categories->getPageCount() > 1}
	    <section class="u-display-flex u-justify-content-center">
	        <div class="c-pagination">{page_links anchor="categories" name="categories" iterator=$categories}
	       </div>
	    </section>
	    {/if}
	</div>
{/if}

{include file="common/footer.tpl"}

