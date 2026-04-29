{**
 * templates/about/siteEditorialTeam.tpl
 *
 * [WIZDAM EDITION] Publisher Root Level Template
 * Menampilkan Steering Committee / Core Administrators di level Site.
 *}
{strip}
{assign var="pageTitle" value="about.editorialTeam"}
{include file="common/header.tpl"}
{/strip}

<div id="publisherEditorialTeam">
    <h2>{$siteTitle|escape} - Publisher Board</h2>
    
    {if $publisherTeamDescription}
        <div class="publisher-description">
            <p>{$publisherTeamDescription|escape|nl2br}</p>
        </div>
    {/if}

    {if $publisherBoard|@count > 0}
        <div class="board-members" style="margin-top: 20px;">
            <h3>Steering Committee & Core Administrators</h3>
            <ul style="list-style-type: none; padding-left: 0;">
                {foreach from=$publisherBoard item=boardMember}
                    <li style="margin-bottom: 15px;">
                        <strong>{$boardMember->getFullName()|escape}</strong>
                        
                        {* Tampilkan afiliasi jika ada *}
                        {if $boardMember->getLocalizedAffiliation()}
                            <br/><em>{$boardMember->getLocalizedAffiliation()|escape}</em>
                        {/if}
                        
                        {* Tampilkan negara jika ada (menggunakan $countries array dari Controller) *}
                        {assign var=memberCountry value=$boardMember->getCountry()}
                        {if $memberCountry && isset($countries[$memberCountry])}
                            <br/><span>{$countries[$memberCountry]|escape}</span>
                        {/if}
                    </li>
                {/foreach}
            </ul>
        </div>
    {else}
        <p>Currently, no core board members are listed for this publisher.</p>
    {/if}
</div>

{include file="common/footer.tpl"}