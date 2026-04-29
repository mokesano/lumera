{**
 * templates/about/journalSponsorship.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * About the Journal / Journal Sponsorship.
 *
 *}
{strip}
{assign var="pageTitle" value="about.journalSponsorship"}
{include file="common/header-ABOUT.tpl"}
{/strip}

<section class="collection">
{if not (empty($sponsorNote) && empty($sponsors))}
<section class="collection" id="sponsors" class="block">

    <h3>{translate key="manager.setup.sponsors"}</h3>
  
  <section class="article">
    <section class="article-body">
      {if $sponsorNote}<p>{$sponsorNote|nl2br}</p>{/if}

      {if $sponsors}
      <ul>
        {foreach from=$sponsors item=sponsor}
          {if $sponsor.url}
            <li><a href="{$sponsor.url|escape}">{$sponsor.institution|escape}</a></li>
            {else}
            <li>{$sponsor.institution|escape}</li>
          {/if}
        {/foreach}
      </ul>
      {/if}

    </section>
  </section>
</section>
{/if}

{if !empty($contributorNote) || (!empty($contributors) && !empty($contributors[0].name))}
<section id="contributors" class="collection block">

    <h3>Academic Affiliation Support</h3>

    <section class="article">

      <section class="article-body">

        {if $contributorNote}<p>{$contributorNote|nl2br}</p>{/if}

        {if $contributors}
        <ul>
          {foreach from=$contributors item=contributor}
            {if $contributor.name}
              {if $contributor.url}
              <li><a href="{$contributor.url|escape}">{$contributor.name|escape}</a></li>
              {else}
              <li>{$contributor.name|escape}</li>
              {/if}
            {/if}
          {/foreach}
        </ul>
        {/if}

    </section>
  </section>
</section>
{/if}

{if not(empty($publisherNote) && empty($publisherInstitution))}
<section id="publisher" class="collection block">
  
  <h3>{translate key="common.publisher"}</h3>
  
  <section class="article">
  
    <section class="article-body">
      
      {if $publisherNote}<p>{$publisherNote|nl2br}</p>{/if}
      <p><a href="{$publisherUrl}" target="_blank">{$publisherInstitution|escape}</a></p>
    
    </section>
  </section>
</section>
{/if}

</section>

{include file="common/footer.tpl"}

