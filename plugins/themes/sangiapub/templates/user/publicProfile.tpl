{**
 * templates/user/publicProfile.tpl
 *
 * Copyright (c) 2013-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Public user profile display.
 *
 *}
{strip}
{assign var="pageTitle" value="user.profile.publicProfile"}
{url|assign:"url" op="profile"}{include file="common/header-PROF27.tpl"}
{/strip}

<div class="live-area-wrapper">
    <div class="u-row row">
        <div class="column small-12">
            <h2 class="profiles" data-user-id="{$user->getId()|string_format:"%012d"|escape}" itemprop="name" itemtype="http://schema.org/Person"><span class="fullname u-hide">{$user->getFullName()|escape}</span>{if $user->getSalutation()}<span class="text degree prefix">{$user->getSalutation()|escape}</span> {/if}{if $user->getFirstName() !== $user->getLastName()}<span class="text given-name">{$user->getFirstName()|escape}</span>{/if}{if $user->getMiddleName()}<span class="text middle-name">{$user->getMiddleName()|escape}</span>{/if}<span class="text surname">{$user->getLastName()|escape}</span>{if $user->getSuffix()}
                <span class="degree suffix">, {$user->getSuffix()|escape}</span>{/if}
            </h2>
        </div>
    </div>
</div>

<section class="live-area-wrapper">
    <div class="profile row person-name">
        <aside class="columns medium-2"></aside>
        <section class="column medium-10 cms-person">
            <h3 data-user-id="{$user->getId()|string_format:"%012d"|escape}" class="u-js-hide">{$user->getFullName()|escape}</h3>
            <h2 data-user-id="{$user->getId()|string_format:"%012d"|escape}" class="user-profile" itemprop="name" itemtype="http://schema.org/Person"><span class="fullname u-hide">{$user->getFullName()|escape}</span>{if $user->getSalutation()}<span class="text degree">{$user->getSalutation()|escape}</span> {/if}{if $user->getFirstName() !== $user->getLastName()}<span class="text given-name">{$user->getFirstName()|escape}</span>{/if}{if $user->getMiddleName()}<span class="text middle-name">{$user->getMiddleName()|escape}</span>{/if}<span class="text surname">{$user->getLastName()|escape}</span>{if $user->getSuffix()}<span class="last-degree suffix">, {$user->getSuffix()|escape}</span>{/if}</h2>
        </section>
    </div>
    <div class="profile row">
		<section class="column medium-2">
			{assign var="profileImage" value=$user->getSetting('profileImage')}
			<div class="avatar bJniPh islvFm" size="150">
			    <span style="box-sizing: border-box; display: inline-block; overflow: hidden; width: 100%; height: 100%; background: rgba(0, 0, 0, 0) none repeat scroll 0% 0%; opacity: 1; border: 0px none; margin: 0px; padding: 0px; position: relative; max-width: 150px;">
				    <span style="box-sizing: border-box; display: block; width: 100%; height: 100%; background: rgba(0, 0, 0, 0) none repeat scroll 0% 0%; opacity: 1; border: 0px none; margin: 0px; padding: 0px; max-width: 150px;">
				        <img style="display: block; max-width: 100%; width: initial; height: initial; background: rgba(0, 0, 0, 0) none repeat scroll 0% 0%; opacity: 1; border: 0px none; margin: 0px; padding: 0px; width:100%" alt="" aria-hidden="true" src="//assets.sangia.org/img/128x150-image.png" class="lazyload"/>
				    </span>
				    <picture>
				    {if $profileImage}
				    <source srcset="{$sitePublicFilesDir}/{$profileImage.uploadName}?as=webp" type="image/webp">
					<img loading="lazy" editorid="{$user->getId()|string_format:"%012d"}" height="auto" width="150" title="{$user->getFullName()|escape}" src="{$sitePublicFilesDir}/{$profileImage.uploadName}" style="position: absolute; inset: 0px; box-sizing: border-box; padding: 0px; border: medium none; margin: auto; display: block; width: 0px; height: 0px; min-width: 100%; max-width: 100%; min-height: 100%; max-height: 100%; object-fit: cover;" class="lazyload avatar editor sangia-author" />
					{else}
					<source {if $user->getGender() == "M"}srcset="//assets.sangia.org/static/images/contactPersonM.png?as=webp"{elseif $user->getGender() == "F"}srcset="//assets.sangia.org/static/images/contactPersonF.png?as=webp"{elseif $user->getGender() == "O"}srcset="//scholar.google.co.id/citations/images/avatar_scholar_128.png?as=webp"{elseif $user->getGender() == ""}srcset="//assets.sangia.org/static/images/default_203.jpg?as=webp"{/if} type="image/webp">
					<img class="lazyload avatar editor sangia-author" loading="lazy" editorid="{$user->getId()|string_format:"%012d"|escape}" title="{$user->getFullName()|escape}" {if $user->getGender() == "M"}src="//assets.sangia.org/static/images/contactPersonM.png"{elseif $user->getGender() == "F"}src="//assets.sangia.org/static/images/contactPersonF.png"{elseif $user->getGender() == "O"}src="//scholar.google.co.id/citations/images/avatar_scholar_128.png"{elseif $user->getGender() == ""}src="//assets.sangia.org/static/images/default_203.jpg"{/if} width="150" height="auto" style="position: absolute; inset: 0px; box-sizing: border-box; padding: 0px; border: medium none; margin: auto; display: block; width: 0px; height: 0px; min-width: 100%; max-width: 100%; min-height: 100%; max-height: 100%; object-fit: cover;" />
				    {/if}
				    </picture>
                </span>
			</div>		
		</section>
		<section class="column medium-7 cms-person">
			<div class="overview description">
			    {* Tampilkan membership name *}
			    {if $userMembership}
                <section class="paragraph" itemprop="creator" itemtype="http://schema.org/Person"><span class="editors" itemprop="name">{$userMembership|escape}</span>
                </section>
                {/if}

				{if $user->getLocalizedAffiliation()}
                {assign var="affiliations" value=$user->getLocalizedAffiliation()|explode:"\n"}
                {assign var="affiliationCount" value=$affiliations|@count}
                {foreach from=$affiliations item=affiliation key=index}
                    {if $affiliation|trim != ''}
    				<div class="affiliation bLswwL">
        				<div class="iggNhe dlPwne"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 106 128" height="1em" width="1em"><path d="M84 98h10v10H12V98h10V52h14v46h10V52h14v46h10V52h14v46zM12 36.86l41-20.84 41 20.84V42H12v-5.14zM104 52V30.74L53 4.8 2 30.74V52h10v36H2v30h102V88H94V52h10z"></path></svg></div>
    				    <p class="paragraph" itemprop="affiliation" itemtype="http://schema.org/Affiliation">{$affiliation|escape}{if $index == $affiliationCount - 1 && $user->getCountry()}, {$user->getCountryName()|escape}{/if}</p>
    				</div>
    				{/if}
                {/foreach}
                {/if}
    
    			{if $user->getLocalizedGossip()}
                <div class="sc-1mur6on-9 bLswwL GooSSiP">
                    <div class="sc-1mur6on-10 sc-1mur6on-11 iggNhe lkBdsj">
                        <svg focusable="false" viewBox="0 0 24 24" class="Q89XVe xSP5ic pOf0gc NMm5M" width="1em" height="1em"><path d="M21 13H3v-2h18v2zM3 18h12v-2H3v2zM21 6H3v2h18V6z"></path></svg>
                    </div>
            		<p class="sc-1q3g1nv-0 sc-1mur6on-13 eTETae fziRAl" itemprop="affiliation" itemtype="http://schema.org/Affiliation">{$user->getLocalizedGossip()|escape}</p>
                </div>
                {/if}
                				
				{if $user->getInterestString()}
				<div class="sc-1mur6on-9 bLswwL">
				    <div class="sc-1mur6on-10 sc-1mur6on-11 iggNhe lkBdsj"><svg width="1em" height="1em" viewBox="0 0 14 18" xmlns="http://www.w3.org/2000/svg" class="sc-1mur6on-14 bPLcdE"><path id="Shape" fill="currentColor" fill-rule="nonzero" d="M4.98913043,4.69565217 L6.75,4.69565217 L6.75,8.09706522 L5.86956522,7.45728261 L4.98913043,8.09706522 L4.98913043,4.69565217 Z M9.39130435,4.10869565 L9.39130435,5.57608696 L12.0326087,5.57608696 L12.0326087,16.1413043 L2.70586957,16.1413043 C2.02206522,16.1413043 1.4673913,15.5866304 1.4673913,14.9028261 L1.4673913,5.2826087 C1.8225,5.4675 2.21869565,5.57608696 2.64130435,5.57608696 L3.52173913,5.57608696 L3.52173913,10.9790217 L5.86956522,9.27097826 L8.2173913,10.9790217 L8.2173913,3.22826087 L3.52173913,3.22826087 L3.52173913,4.10869565 L2.64130435,4.10869565 C1.99271739,4.10869565 1.4673913,3.51586957 1.4673913,2.78804348 C1.4673913,2.06021739 1.99271739,1.4673913 2.64130435,1.4673913 L12.6195652,1.4673913 L12.6195652,0 L2.64130435,0 C1.18565217,0 0,1.25021739 0,2.78804348 L0,14.9028261 C0,16.3936957 1.215,17.6086957 2.70586957,17.6086957 L13.5,17.6086957 L13.5,4.10869565 L9.39130435,4.10869565 Z"></path></svg>
				    </div>
				    <p class="sc-1q3g1nv-0 sc-1mur6on-13 fziRAl" itemprop="interest" itemtype="http://schema.org/Interest">{$user->getInterestString()|escape}</p>
				</div>
				{/if}
				
				<div class="scwizdam-1mur6wiz-7 bLswwL">
                    <div class="iggNhe dlPwne sc-1mur6on-10 sc-1mur6on-11 iggNhe lkBdsj"><svg version="1.0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1280.000000 965.000000" preserveAspectRatio="xMidYMid meet" width="1em" height="1em"><g transform="translate(0.000000,965.000000) scale(0.100000,-0.100000)" fill="#000000" stroke="none"><path d="M0 4825 l0 -4825 6400 0 6400 0 0 4825 0 4825 -6400 0 -6400 0 0 -4825z m11349 3931 c-285 -293 -467 -476 -3437 -3458 -1091 -1095 -1146 -1149 -1215 -1183 -183 -90 -434 -80 -596 25 -50 31 -528 512 -2980 2995 -707 715 -1357 1373 -1445 1463 l-161 162 4919 0 c2706 0 4917 -2 4915 -4z m-8835 -2278 c884 -894 1608 -1630 1608 -1633 0 -6 -3137 -3220 -3206 -3284 l-26 -24 0 3287 c0 1808 4 3286 8 3284 5 -1 732 -735 1616 -1630z m9396 -1684 c0 -1802 -4 -3244 -9 -3242 -4 2 -727 739 -1605 1637 l-1597 1635 773 775 c1968 1975 2433 2441 2435 2441 2 0 3 -1461 3 -3246z m-6380 -1346 c278 -203 590 -298 942 -285 237 8 439 58 631 156 176 90 240 144 616 516 l354 352 1609 -1646 1608 -1646 -4882 -3 c-2685 -1 -4883 0 -4886 2 -2 3 43 52 100 110 56 58 785 803 1618 1656 l1515 1550 350 -353 c199 -201 382 -377 425 -409z"></path></g></svg>
                    </div>
                    <p class="paragraph">{translate key="user.email"}: {$user->getEmail()|escape}</p>
                </div>
                
                {if $user->getData('phone')}
                <div class="scwizdam-1mur6wiz-7 bLswwL">
                    <div class="iggNhe dlPwne sc-1mur6on-10 sc-1mur6on-11 iggNhe lkBdsj"><svg version="1.0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1280.000000 1280.000000" preserveAspectRatio="xMidYMid meet" width="1em" height="1em"><g transform="translate(0.000000,1280.000000) scale(0.100000,-0.100000)" fill="#000000" stroke="none"><path d="M6145 12794 c-216 -13 -391 -28 -530 -45 -995 -122 -1927 -467 -2760 -1022 -907 -604 -1648 -1433 -2146 -2402 -395 -769 -615 -1549 -690 -2450 -17 -193 -17 -757 0 -950 75 -901 295 -1681 690 -2450 610 -1188 1578 -2156 2766 -2766 769 -395 1549 -615 2450 -690 193 -17 757 -17 950 0 901 75 1681 295 2450 690 1187 610 2156 1579 2766 2766 395 769 615 1549 690 2450 17 193 17 757 0 950 -75 901 -295 1681 -690 2450 -610 1188 -1578 2156 -2766 2766 -753 387 -1531 610 -2390 684 -164 15 -666 27 -790 19z m739 -779 c1098 -94 2121 -499 3001 -1188 401 -314 804 -738 1106 -1162 598 -840 944 -1793 1030 -2840 16 -193 16 -657 0 -850 -114 -1385 -693 -2628 -1672 -3591 -960 -942 -2172 -1494 -3524 -1605 -193 -16 -657 -16 -850 0 -1352 111 -2561 661 -3523 1605 -979 960 -1561 2210 -1673 3591 -15 193 -15 657 0 850 112 1384 693 2629 1678 3596 872 856 1985 1403 3183 1563 416 55 832 66 1244 31z"></path><path d="M5060 10738 c-55 -15 -679 -379 -716 -418 -85 -87 -103 -206 -47 -315 67 -129 1153 -2002 1181 -2035 57 -68 178 -105 267 -81 46 12 662 365 715 409 71 59 108 190 78 277 -8 22 -276 495 -596 1050 -471 817 -591 1018 -628 1052 -68 64 -164 87 -254 61z"></path><path d="M3945 9869 c-444 -268 -654 -492 -755 -805 -51 -159 -63 -256 -62 -489 2 -581 148 -1249 437 -2005 660 -1719 1900 -3438 3021 -4187 344 -229 566 -316 844 -330 257 -12 511 63 877 260 113 61 151 86 147 97 -6 17 -1182 2058 -1197 2077 -7 10 -18 8 -47 -7 -131 -67 -310 -98 -455 -80 -282 37 -555 190 -841 475 -264 262 -461 538 -645 905 -404 805 -456 1555 -134 1933 33 39 144 132 166 139 8 3 -217 401 -590 1049 -332 574 -606 1047 -610 1051 -3 4 -73 -33 -156 -83z"></path><path d="M8220 5330 c-55 -7 -46 -3 -413 -214 -164 -94 -314 -186 -333 -204 -76 -74 -104 -192 -66 -287 32 -82 1166 -2037 1203 -2075 82 -84 203 -104 314 -51 86 41 614 347 655 380 56 45 90 120 90 201 0 37 -7 83 -15 103 -33 80 -1170 2035 -1203 2069 -61 64 -141 91 -232 78z"></path></g></svg>
                    </div>
                    <p class="paragraph">{translate key="user.phone"}: {$user->getPhone()|escape}</p>
                </div>
                {/if}
				
				<div class="editor-person eTETae">
					{if $user->getData('orcid')}
					<p class="sc-1q3g1nv-0 sc-1mur6on-13 fziRAl">
						<img src="//assets.sangia.org/img/orcid_16x16.svg" style="height:16px" alt="orcid" class="lazyload" />
						<span class="orcid">{translate key="user.orcid"} </span><a title="Go to Orcid profile of {$user->getFullName()|escape}" href="{$user->getData('orcid')|escape}" target="_blank">{$user->getData('orcid')|escape}</a>
					</p>{/if}
					{if $user->getData('sintaId')}
					<p class="sc-1q3g1nv-0 sc-1mur6on-13 fziRAl">
						<img src="{$baseUrl}/assets/ico/brand_sinta.png" style="height:16px" alt="sinta" class="lazyload" referrerpolicy="strict-origin-when-cross-origin" />
						<span class="sinta">Sinta: </span><a title="Go to Sinta profile of {$user->getFullName()|escape}" href="https://sinta.kemdiktisaintek.go.id/authors/profile/{$user->getData('sintaId')|escape}" target="_blank" class="sintaid">Science and Technology Index</a><span class="u-ml-4 articleCount">Profile by Kemendikti Saintek Indonesia</span>
					</p>{/if}
					{if $user->getData('scopusId')}
					<p class="sc-1q3g1nv-0 sc-1mur6on-13 fziRAl">
					    <img src="{$baseUrl}/assets/ico/scopus.ico" alt="Scopus" width="16" height="16" style="vertical-align: middle; margin-right: 3px;" class="brand" referrerpolicy="strict-origin-when-cross-origin" />
						<span class="scopus">Scopus</span> ID <a title="Go to Scopus profile of {$user->getFullName()|escape}" href="https://www.scopus.com/authid/detail.uri?authorId={$user->getData('scopusId')|escape}" target="_blank" class="scopusid">{$user->getData('scopusId')|escape}</a>
					</p>{/if}
					{if $user->getData('dimensionId')}
					<p class="sc-1q3g1nv-0 sc-1mur6on-13 fziRAl">
					    <img src="{$baseUrl}/assets/ico/dimension.ico" alt="Dimension" width="16" height="16" style="vertical-align: middle; margin-right: 3px;" class="brand" referrerpolicy="strict-origin-when-cross-origin" />
						<span class="dimension">Dimension</span> ID <a title="Go to Dimension profile of {$user->getFullName()|escape}" href="https://app.dimensions.ai/details/entities/publication/author/{$user->getData('dimensionId')|escape}" target="_blank" class="scopusid">{$user->getData('dimensionId')|escape}</a>
					</p>{/if}
					{if $user->getData('researcherId')}
					<p class="sc-1q3g1nv-0 sc-1mur6on-13 fziRAl">
					    <img src="{$baseUrl}/assets/ico/wos.svg" alt="Web of Science Researcher (Publons)" width="16" height="16" style="vertical-align: middle; margin-right: 3px;" class="brand" referrerpolicy="strict-origin-when-cross-origin" />
						<span class="researcher">ResearcherId</span> <a title="Go to Web of Science Researcher profile of {$user->getFullName()|escape}" href="https://www.webofscience.com/wos/author/record/{$user->getData('researcherId')|escape}" target="_blank" class="scopusid">{$user->getData('researcherId')|escape}</a>
					</p>{/if}
				</div>
				
				{if $user->getData('mailingAddress')}
				<div class="sc-1mur6on-15 fTcNcY anchor">
				    <a href="mailto:{$user->getEmail()|escape}" class="sc-kvjqii-0 fYuYRT sc-1mur6on-16 bneccj"><span class="sc-kvjqii-1 hjlxNa"><svg aria-hidden="true" focusable="false" viewBox="0 0 15 15" height="2em" width="2em"><path d="M.156 13.469L6.094 7.53.156 1.594 1.25.5l7.031 7.031-7.031 7.032z" fill="currentColor" fill-rule="nonzero"></path></svg></span>
				        <span class="anchor-text">Contact mail <span class="fullname u-hide">{$user->getFullName()|escape}</span>{if $user->getSalutation()}<span class="text degree">{$user->getSalutation()|escape}</span> {/if}{if $user->getFirstName() !== $user->getLastName()}<span class="text given-name">{$user->getFirstName()|escape}</span>{/if}{if $user->getMiddleName()|escape}<span class="text middle-name">{$user->getMiddleName()|escape}</span>{/if}<span class="text surname">{$user->getLastName()|escape}</span>{if $user->getSuffix()}<span class="text degree">, {$user->getSuffix()|escape}</span>{/if}</span>
				    </a>
				</div>
				{/if}
			</div>	    
		</section>
		
		<aside class="column medium-3">
			<section class="box">
				<section class="description" title="Phone number and email addresses editor/reviewer are not displayed. See more about Privacy Statements.">
				    <p class="headline-524909129 __info">Available Contact{if $user->getData('phone')|escape}s{/if}</p>
				    <p class=" "><span class="dlPwne"><svg version="1.0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1280.000000 965.000000" preserveAspectRatio="xMidYMid meet" width="1em" height=".7em"><g transform="translate(0.000000,965.000000) scale(0.100000,-0.100000)" fill="#000000" stroke="none"><path d="M0 4825 l0 -4825 6400 0 6400 0 0 4825 0 4825 -6400 0 -6400 0 0 -4825z m11349 3931 c-285 -293 -467 -476 -3437 -3458 -1091 -1095 -1146 -1149 -1215 -1183 -183 -90 -434 -80 -596 25 -50 31 -528 512 -2980 2995 -707 715 -1357 1373 -1445 1463 l-161 162 4919 0 c2706 0 4917 -2 4915 -4z m-8835 -2278 c884 -894 1608 -1630 1608 -1633 0 -6 -3137 -3220 -3206 -3284 l-26 -24 0 3287 c0 1808 4 3286 8 3284 5 -1 732 -735 1616 -1630z m9396 -1684 c0 -1802 -4 -3244 -9 -3242 -4 2 -727 739 -1605 1637 l-1597 1635 773 775 c1968 1975 2433 2441 2435 2441 2 0 3 -1461 3 -3246z m-6380 -1346 c278 -203 590 -298 942 -285 237 8 439 58 631 156 176 90 240 144 616 516 l354 352 1609 -1646 1608 -1646 -4882 -3 c-2685 -1 -4883 0 -4886 2 -2 3 43 52 100 110 56 58 785 803 1618 1656 l1515 1550 350 -353 c199 -201 382 -377 425 -409z"></path></g></svg> {translate key="user.email"} Addressed</span>
					{if $user->getData('phone')}<br /><span class="dlPwne"><svg version="1.0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1280.000000 1280.000000" preserveAspectRatio="xMidYMid meet" width="1em" height=".9em"><g transform="translate(0.000000,1280.000000) scale(0.100000,-0.100000)" fill="#000000" stroke="none"><path d="M6145 12794 c-216 -13 -391 -28 -530 -45 -995 -122 -1927 -467 -2760 -1022 -907 -604 -1648 -1433 -2146 -2402 -395 -769 -615 -1549 -690 -2450 -17 -193 -17 -757 0 -950 75 -901 295 -1681 690 -2450 610 -1188 1578 -2156 2766 -2766 769 -395 1549 -615 2450 -690 193 -17 757 -17 950 0 901 75 1681 295 2450 690 1187 610 2156 1579 2766 2766 395 769 615 1549 690 2450 17 193 17 757 0 950 -75 901 -295 1681 -690 2450 -610 1188 -1578 2156 -2766 2766 -753 387 -1531 610 -2390 684 -164 15 -666 27 -790 19z m739 -779 c1098 -94 2121 -499 3001 -1188 401 -314 804 -738 1106 -1162 598 -840 944 -1793 1030 -2840 16 -193 16 -657 0 -850 -114 -1385 -693 -2628 -1672 -3591 -960 -942 -2172 -1494 -3524 -1605 -193 -16 -657 -16 -850 0 -1352 111 -2561 661 -3523 1605 -979 960 -1561 2210 -1673 3591 -15 193 -15 657 0 850 112 1384 693 2629 1678 3596 872 856 1985 1403 3183 1563 416 55 832 66 1244 31z"></path><path d="M5060 10738 c-55 -15 -679 -379 -716 -418 -85 -87 -103 -206 -47 -315 67 -129 1153 -2002 1181 -2035 57 -68 178 -105 267 -81 46 12 662 365 715 409 71 59 108 190 78 277 -8 22 -276 495 -596 1050 -471 817 -591 1018 -628 1052 -68 64 -164 87 -254 61z"></path><path d="M3945 9869 c-444 -268 -654 -492 -755 -805 -51 -159 -63 -256 -62 -489 2 -581 148 -1249 437 -2005 660 -1719 1900 -3438 3021 -4187 344 -229 566 -316 844 -330 257 -12 511 63 877 260 113 61 151 86 147 97 -6 17 -1182 2058 -1197 2077 -7 10 -18 8 -47 -7 -131 -67 -310 -98 -455 -80 -282 37 -555 190 -841 475 -264 262 -461 538 -645 905 -404 805 -456 1555 -134 1933 33 39 144 132 166 139 8 3 -217 401 -590 1049 -332 574 -606 1047 -610 1051 -3 4 -73 -33 -156 -83z"></path><path d="M8220 5330 c-55 -7 -46 -3 -413 -214 -164 -94 -314 -186 -333 -204 -76 -74 -104 -192 -66 -287 32 -82 1166 -2037 1203 -2075 82 -84 203 -104 314 -51 86 41 614 347 655 380 56 45 90 120 90 201 0 37 -7 83 -15 103 -33 80 -1170 2035 -1203 2069 -61 64 -141 91 -232 78z"></path></g></svg> Phone Number</span>{/if}</p>
				</section>
			</section>
		</aside>
		
	</div>
</section>

{if $user->getData('scopusId')}
<div class="live-area">
    <div class="profile row graph">
        <!-- Elemen untuk grafik artikel -->
        <aside class="medium-12">
			<section class="graph_box">
				<section class="description" id="scopus-graph"></section>
			</section>
		</aside>
	</div>	
</div>
{/if}

{if $user->getLocalizedBiography() || $user->getLocalizedSignature() || $user->getData('mailingAddress') || $user->getGoogleScholar() || $user->getUrl()}
<div class="live-area-wrapper">
    <div class="profile row">
        <aside class="column medium-2 null">
            {if $user->getLocalizedBiography() || $user->getLocalizedSignature() || $user->getData('mailingAddress')}
    		<header class="anchored">
    		    <h3 class=" ">{translate key="user.biography"}</h3>
    		</header>
    		{/if}
    		{if $user->getLocalizedBiography() || $user->getLocalizedSignature() || $user->getData('mailingAddress')}
    		<section class="ads">
        		<!-- Sangia_Publishing_ads -->
                <ins class="adsbygoogle"
                     style="display:block"
                     data-ad-client="ca-pub-8416265824412721"
                     data-ad-slot="2864083864"
                     data-ad-format="auto"
                     data-full-width-responsive="true"></ins>
                <script>
                {literal}
                     (adsbygoogle = window.adsbygoogle || []).push({});
                {/literal}
                </script>
    		</section>
    		{/if}
		</aside>
    	<div class="column medium-10">
    		<div class="person-detail" itemprop="description">{$user->getLocalizedBiography()|strip_unsafe_html|nl2br}<a aria-label="Go to Editor's personal profile of {$user->getFullName()|escape}" {if $user->getUrl()}href="{$user->getUrl()|escape}"{elseif $user->getGoogleScholar()}href="{$user->getGoogleScholar()|escape}"{/if} target="_blank">{if $userMembership} {$userMembership|escape}{/if} Personal Website</a>
    		</div>
    		{if $user->getLocalizedSignature()}
    		<div class="Signature u-mt-16" itemprop="signature">
    		    <p>{$user->getLocalizedSignature()|strip_unsafe_html|nl2br}</p>
    		</div>
    		{/if}
    		{if $user->getData('mailingAddress')}
    		<div class="Address u-mt-16" itemprop="address"><p>{$user->getData('mailingAddress')|strip_unsafe_html|nl2br}</p>
    		</div>
    		{/if}
    	</div>
    </div>
</div>    
{/if}

{if $user->getData('scopusId')}
<div class="live-area-wrapper">
    <div class="profile row">
        <div class="column medium-12">
            <!-- Elemen untuk daftar artikel -->
            <div class="articles-list" id="scopus-articles">
                <div data-test="title" class="anchored">
                    <h3 class="anchored"><span class="content-break">Article author in Scopus</span></h3>
                </div>
                <div class="scopus-article-detail" itemprop="scopus"></div>
            </div>
        </div>
    </div>
</div>
{/if}

<section class="live-area-wrapper">
    <div class="profile row">
        <div class="column medium-12">
        
<h4>{translate key="user.profile"}</h4>

<p><a href="{url page="user" op="profile"}">{translate key="user.editMyProfile"}</a></p>

<div id="profile" class="page">
	<div class="profile-header">
		<div class="profile-avatar">
			{* Get profile image data *}
			{assign var="profileImage" value=$user->getSetting('profileImage')}
			
			{* Display profile image if available *}
			{if $profileImage && $profileImage.uploadName}
				<img src="{$sitePublicFilesDir}/{$profileImage.uploadName}" alt="{$user->getFullName()|escape}" class="profile-photo" />
			{else}
				<span class="profile-initials">{$user->getFirstName()|escape|substr:0:1}{$user->getLastName()|escape|substr:0:1}</span>
			{/if}
		</div>
		<div class="profile-main-info">
			<h2>{$user->getFullName()|escape}</h2>
			{if $user->getLocalizedAffiliation()}
				<p class="affiliation">{$user->getLocalizedAffiliation()|escape}</p>
			{/if}
		</div>
	</div>

	<div class="profile-sections">
		
		<div class="section u-mb-48">
			<h3 class="section-title">Personal Information</h3>
			<div class="field-list">
				{if $user->getSalutation()}
				<div class="field-item">
					<span class="field-label">{translate key="user.salutation"}:</span>
					<span class="field-value">{$user->getSalutation()|escape}</span>
				</div>
				{/if}
				
				<div class="field-item">
					<span class="field-label">{translate key="user.username"}:</span>
					<span class="field-value">{$user->getUsername()|escape}</span>
				</div>
				
				<div class="field-item">
					<span class="field-label">{translate key="user.firstName"}:</span>
					<span class="field-value">{$user->getFirstName()|escape}</span>
				</div>
				
				{if $user->getMiddleName()}
				<div class="field-item">
					<span class="field-label">{translate key="user.middleName"}:</span>
					<span class="field-value">{$user->getMiddleName()|escape}</span>
				</div>
				{/if}
				
				<div class="field-item">
					<span class="field-label">{translate key="user.lastName"}:</span>
					<span class="field-value">{$user->getLastName()|escape}</span>
				</div>
				
				{if $user->getSuffix()}
				<div class="field-item">
					<span class="field-label">{translate key="user.suffix"}:</span>
					<span class="field-value">{$user->getSuffix()|escape}</span>
				</div>
				{/if}
				
				{if $user->getGender()}
				<div class="field-item">
					<span class="field-label">{translate key="user.gender"}:</span>
					<span class="field-value">
						{if $user->getGender() == "M"}{translate key="user.masculine"}
						{elseif $user->getGender() == "F"}{translate key="user.feminine"}
						{elseif $user->getGender() == "O"}{translate key="user.other"}
						{/if}
					</span>
				</div>
				{/if}
			</div>
		</div>

		<div class="section u-mb-48">
			<h3 class="section-title">Contact Information</h3>
			<div class="field-list">
				<div class="field-item">
					<span class="field-label">{translate key="user.email"}:</span>
					<span class="field-value">
						{$user->getEmail()|escape}
						{assign var=emailString value=$user->getFullName()|concat:" <":$user->getEmail():">"}
						{url|assign:"url" page="user" op="email" to=$emailString|to_array redirectUrl=$currentUrl}
						<a href="{$url}" class="email-action">{icon name="mail"}</a>
					</span>
				</div>
				
				{if $user->getUrl()}
				<div class="field-item">
					<span class="field-label">{translate key="user.url"}:</span>
					<span class="field-value">
						<a href="{$user->getUrl()|escape:"quotes"}" target="_blank">{$user->getUrl()|escape}</a>
					</span>
				</div>
				{/if}
				
				{if $user->getPhone()}
				<div class="field-item">
					<span class="field-label">{translate key="user.phone"}:</span>
					<span class="field-value">{$user->getPhone()|escape}</span>
				</div>
				{/if}
				
				{if $user->getFax()}
				<div class="field-item">
					<span class="field-label">{translate key="user.fax"}:</span>
					<span class="field-value">{$user->getFax()|escape}</span>
				</div>
				{/if}
				
				{if $user->getMailingAddress()}
				<div class="field-item">
					<span class="field-label">{translate key="common.mailingAddress"}:</span>
					<span class="field-value">{$user->getMailingAddress()|strip_unsafe_html|nl2br}</span>
				</div>
				{/if}
			</div>
		</div>

		{if $user->getLocalizedAffiliation()}
		<div class="section u-mb-48">
			<h3 class="section-title">Affiliation</h3>
			<div class="field-list">
				{assign var="affiliations" value=$user->getLocalizedAffiliation()|explode:"\n"}
				
				{if count($affiliations) > 1}
					{foreach from=$affiliations item=affiliation name=affiliationLoop}
						{if $affiliation|trim}
							<div class="field-item">
								<span class="field-label">Affiliation {$smarty.foreach.affiliationLoop.iteration}:</span>
								<span class="field-value">
									{$affiliation|trim|escape}{if $smarty.foreach.affiliationLoop.last && $user->getCountry()}, {$user->getCountryName()|escape}{/if}
								</span>
							</div>
						{/if}
					{/foreach}
				{else}
					<div class="field-item">
						<span class="field-label">{translate key="user.affiliation"}:</span>
						<span class="field-value">
							{$user->getLocalizedAffiliation()|escape}{if $user->getCountry()}, {$user->getCountryName()|escape}{/if}
						</span>
					</div>
				{/if}
			</div>
		</div>
		{/if}
		{if $user->getLocalizedSignature() || $userInterests}
		<div class="section u-mb-48">
			<h3 class="section-title">Academic Information</h3>
			<div class="field-list">
				{if $user->getLocalizedSignature()}
				<div class="field-item">
					<span class="field-label">{translate key="user.signature"}:</span>
					<span class="field-value">{$user->getLocalizedSignature()|escape|nl2br}</span>
				</div>
				{/if}
				
				{if $userInterests}
				<div class="field-item">
					<span class="field-label">{translate key="user.interests"}:</span>
					<span class="field-value">{$userInterests|escape}</span>
				</div>
				{/if}
			</div>
		</div>
		{/if}

		{if $user->getLocales()}
		<div class="section u-mb-48">
			<h3 class="section-title">{translate key="user.workingLanguages"}</h3>
			<div class="languages-container">
				{foreach name=workingLanguages from=$user->getLocales() item=localeKey}
					<span class="language-tag">{$localeNames.$localeKey|escape}</span>
				{/foreach}
				{foreach name=workingLanguages from=$user->getLocales() item=localeKey}{$localeNames.$localeKey|escape}{if !$smarty.foreach.workingLanguages.last}; {/if}{foreachelse}&mdash;{/foreach}
			</div>
		</div>
		{/if}

		{if $user->getLocalizedBiography()}
		<div class="section u-mb-48 biography-section">
			<h3 class="section-title">{translate key="user.biography"}</h3>
			<div class="biography-content">
				{$user->getLocalizedBiography()|strip_unsafe_html|nl2br}
			</div>
		</div>
		{/if}

		{if $user->getLocalizedGossip()}
		<div class="section u-mb-48">
			<h3 class="section-title">Additional Information</h3>
			<div class="field-list">
				<div class="field-item">
					<span class="field-label">{translate key="user.gossip"}:</span>
					<span class="field-value">{$user->getLocalizedGossip()|escape}</span>
				</div>
			</div>
		</div>
		{/if}

		<div class="section u-mb-48 system-section">
			<h3 class="section-title">Account Information</h3>
			<div class="system-info">
				<div class="system-item">
					<span class="system-label">{translate key="user.dateRegistered"}:</span>
					<span class="system-value">{$user->getDateRegistered()|date_format:$datetimeFormatLong}</span>
				</div>
				<div class="system-item">
					<span class="system-label">{translate key="user.dateLastLogin"}:</span>
					<span class="system-value">
						{if $user->getDateLastLogin()}
							{$user->getDateLastLogin()|date_format:$datetimeFormatLong}
						{else}
							<em>Never</em>
						{/if}
					</span>
				</div>
			</div>
		</div>
	</div>
</div>

        </div>
    </div>
</section>

{include file="common/footer-parts/footer-profile.tpl"}
