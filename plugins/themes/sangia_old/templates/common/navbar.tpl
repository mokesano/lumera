{**
 * templates/common/navbar.tpl
 *
 * Copyright (c) 2013-2017 Simon Fraser University
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Navigation Bar
 *
 *}
<div id="header" role="banner">
	<div class="cross-nav cross-nav--wide">
    	{if $isUserLoggedIn}
    	{if $hasOtherJournals}
    	<div id="userJournal" class="auth flyout"><a class="register-link flyout-caption" href="{url journal="$currentJournal" page="user"}">My Journals</a></div>
        {/if}

    	<div id="userProfile" class="auth flyout"><a class="register-link flyout-caption" href="{url page="user" op="profile"}">My Profile</a></div>

    	<div id="userHome" class="auth flyout"><a class="register-link flyout-caption" href="{url page="user"}">User Home</a></div>

    	<div id="userLogOut" class="auth flyout"><a class="register-link flyout-caption" href="{url page="login" op="signOut"}">Log Out</a></div>

		{if $userSession->getSessionVar('signedInAs')}
    	<div id="userLogOutUser" class="auth flyout"><a class="register-link flyout-caption" href="{url page="login" op="signOutAsUser"}">Log Out as User</a></div>
		{/if}
    		{else}
    	<div id="userlogin" class="auth flyout"><a class="register-link flyout-caption" href="{url page="login"}">{translate key="navigation.login"}</a></div>		
		{if !$hideRegisterLink}
    	<div id="userRegister" class="auth flyout"><a class="register-link flyout-caption" href="{url page="user" op="register"}">{translate key="navigation.register"}</a></div>
			{/if}
    	{/if}{* $isUserLoggedIn *}
<div class="lang flyout">
  <button class="flyout-caption cur" aria-expanded="false">
    <span>English</span>
    <img class="flyout-caption-icon" src="//www.stipwunaraha.ac.id/static/images/classical/flyout-arrow.png" alt="">
  </button>
  <div class="flyout-content">
    <ul>
      <li>
        <a class="change-language-Deutsch" href="/language/Deutsch" rel="nofollow">Deutsch</a>
      </li>
    </ul>
  </div>
</div>

  </div>
  <a id="logo" href="/">
  <img src="{$baseUrl}/plugins/themes/sangia/img/classical/logo_high_res.png" alt="Logo Sangia Publishing Group" title="Sangia for Research & Development">
</a>

  <div class="panel-search">
    <form id="global-search" class="big-search lm-site-search {if $query}contains-new-search-link{/if}{if $subject}contains-new-search-link{/if}" action="{url page="search" op="search"}" method="get" role="search">
	{capture assign="queryFilter"}{call_hook name="Templates::Search::SearchResults::FilterInput" filterName="query" filterValue=$query}{/capture}
	{if empty($queryFilter)}
      <div class="search-field">
        <input id="query" class="text ui-autocomplete-input" name="query" type="text" autocomplete="off" value="{if $query}{$query|strip_unsafe_html}{else}{$subject|strip_unsafe_html}{/if}" placeholder="Search" title="Search">
        <ul id="ui-id-1" tabindex="0" class="ui-menu ui-widget ui-widget-content ui-autocomplete ui-front" style="display: none;"></ul></div>
        {if $query}
        <a id="global-search-new" {if $siteSearch}href="/search"{else}href="{url page="search" op="search"}"{/if}>New Search</a>
        {/if}
        {if $subject}
        <a id="global-search-new" {if $siteSearch}href="/search"{else}href="{url page="search" op="search"}"{/if}>New Search</a>
        {/if}
  {else}
		{$queryFilter}
	{/if}
      <input id="search" class="search-submit" type="submit" value="Submit"><img class="loading js-hidden" src="//www.stipwunaraha.ac.id/static/images/classical/loading_search.gif" alt="">
      <img class="search-submit-image" src="//www.stipwunaraha.ac.id/static/images/classical/search-submit.png" alt="Search">
      <div id="search-options" class="flyout">
        <button class="pillow-btn open-search-options" type="button" aria-expanded="false">
          <img src="//www.stipwunaraha.ac.id/static/images/classical/settings-active.png" alt="Search Options">
        </button>
        <div class="flyout-content">
          <ul>
            <li>
              <a id="advanced-search-link" href="/advanced-search">Advanced Search</a>
            </li>
            <li>
              <a id="search-help-link" href="/searchhelp">Search Help</a>
            </li>
          </ul>
        </div>
      </div>
    </form>
  </div>
  <button class="pillow-btn open-search" aria-expanded="false">Search</button>
  <button class="pillow-btn open-menu" aria-expanded="false">Menu</button>
  <div class="panel-menu">
  	<div class="cross-nav cross-nav--narrow">
    	{if $isUserLoggedIn}
    	{if $hasOtherJournals}
    	<div id="userJournal" class="auth flyout"><a class="register-link flyout-caption" href="{url journal="$currentJournal" page="user"}">My Journals</a></div>
        {/if}

    	<div id="userProfile" class="auth flyout"><a class="register-link flyout-caption" href="{url page="user" op="profile"}">My Profile</a></div>

    	<div id="userHome" class="auth flyout"><a class="register-link flyout-caption" href="{url page="user"}">User Home</a></div>

    	<div id="userLogOut" class="auth flyout"><a class="register-link flyout-caption" href="{url page="login" op="signOut"}">Log Out</a></div>

		{if $userSession->getSessionVar('signedInAs')}
    	<div id="userLogOutUser" class="auth flyout"><a class="register-link flyout-caption" href="{url page="login" op="signOutAsUser"}">Log Out as User</a></div>
		{/if}
    		{else}
    	<div id="userlogin" class="auth flyout"><a class="register-link flyout-caption" href="{url page="login"}">{translate key="navigation.login"}</a></div>		
		{if !$hideRegisterLink}
    	<div id="userRegister" class="auth flyout"><a class="register-link flyout-caption" href="{url page="user" op="register"}">{translate key="navigation.register"}</a></div>
			{/if}
    	{/if}{* $isUserLoggedIn *}
<div class="lang flyout">
  <button class="flyout-caption cur" aria-expanded="false">
    <span>English</span>
    <img class="flyout-caption-icon" src="/static/64ea173f12e65c09652ad2a9e33a74208684c1c5/images/sprites/springer-sprite/flyout-arrow.png" alt="">
  </button>
  <div class="flyout-content">
    <ul>
      <li>
        <a class="change-language-Deutsch" href="/language/Deutsch" rel="nofollow">Deutsch</a>
      </li>
    </ul>
  </div>
</div>

    </div>
    <ul id="global-nav" class="clearfix" role="navigation" aria-label="Global">
      <li id="home"><a href="{url page="$currentJournal"}" >{translate key="navigation.home"}</a></li>
      <li id="about"><a href="{url page="about"}" >{translate key="navigation.about"}</a></li>

		{if $currentJournal && $currentJournal->getSetting('publishingMode') != $smarty.const.PUBLISHING_MODE_NONE}
			<li id="archives"><a href="{url page="issue" op="archive"}" >{translate key="navigation.categories.browse"}</a></li>
      <li id="articles"><a href="{url page="search" op="titles"}" >Articles</a></li>
		{/if}

    <li id="articles"><a href="{url page="search" op="categories"}" >Journal A-Z</a></li>

		{if $enableAnnouncements}
			<li id="announcements"><a href="{url page="announcement"}" >{translate key="announcement.announcements"}</a></li>
		{/if}{* enableAnnouncements *}

    </ul>
  </div>
</div>