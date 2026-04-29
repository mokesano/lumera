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

    <h2>Academic Affiliation Support</h2>

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
  
  <h2>{translate key="common.publisher"}</h2>
  
  <section class="article">
    <section class="article-body">
      <p>{if $publisherInstitution == "Sekolah Tinggi Ilmu Pertanian Wuna"}<a>Sangia Publishing</a> <span class="italic">collaborated with</span> <a href="{$publisherUrl}" target="_blank">{$publisherInstitution|escape}</a>{else}<a href="{$publisherUrl}" target="_blank">{$publisherInstitution|escape}</a>{/if}</p>
      {if $publisherNote}<p>{$publisherNote|nl2br}</p>{/if}
    </section>
  </section>
</section>
{/if}

</section>

        </div>
    </div>
</div>

<div class="live-area-wrapper">
	<div class="row">
	    <div role="main" class="column">
	        <iframe class="lazyload" src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15919.707735119626!2d122.5556084!3d-4.0353334!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x59d6a213a880ac1a!2sSangia%20News%20%26%20Media!5e0!3m2!1sid!2sid!4v1658598581283!5m2!1sid!2sid" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" width="100%" height="300"></iframe>
        </div>
    </div>

{include file="common/footer.tpl"}

