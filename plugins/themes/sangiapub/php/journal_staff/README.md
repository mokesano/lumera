# Contextual Membership Display for OJS 2.4.8.2

## Overview
Sistem untuk menampilkan membership name yang kontekstual berdasarkan asal halaman pengguna (Editorial Team atau Display Membership). Sistem menggunakan field `context` di database untuk membedakan groups secara akurat tanpa hardcode manual.

## Features
- ✅ **Contextual membership display** - Role sesuai halaman asal
- ✅ **Database-driven filtering** - Menggunakan field `context` OJS
- ✅ **Support multiple membership** per user dengan prioritas
- ✅ **Navigation menu integration** - Otomatis populate menu displayMembership
- ✅ **Error handling dan fallback** - Robust dan tidak break halaman
- ✅ **Modular architecture** - Mudah maintenance dan upgrade
- ✅ **Universal compatibility** - Bekerja di semua jurnal dengan struktur OJS standard

## Database Structure
Sistem menggunakan field `context` di tabel `groups` untuk membedakan:
- **`context = 1`**: Groups untuk Editorial Team (editorialTeam.tpl, editorialTeamBoard.tpl)
- **`context = 2`**: Groups untuk Display Membership (displayMembership.tpl)

## File Structure
```
/plugins/themes/[nama-tema]/
├── php/
│   └── journal_staff/
│       └── MembershipHandler.php    # Main handler class
├── templates/
│   ├── editorialTeamBio.tpl         # Modified: membership display
│   ├── editorialTeam.tpl            # Modified: context links
│   ├── editorialTeamBoard.tpl       # Modified: context links
│   ├── displayMembership.tpl        # Modified: context links
│   └── [navigation-template].tpl    # Modified: menu groups
└── README.md                        # This documentation
```

## Installation

### 1. Create Directory Structure
```bash
mkdir -p /plugins/themes/[nama-tema]/php/journal_staff/
```
*Ganti `[nama-tema]` dengan nama theme aktual Anda*

### 2. Upload Handler File
Upload `MembershipHandler.php` ke folder:
```
/plugins/themes/[nama-tema]/php/journal_staff/MembershipHandler.php
```

### 3. Modify Templates

#### A. editorialTeamBio.tpl
Tambahkan kode di bagian yang ingin menampilkan membership:

```smarty
{php}
foreach ((array)$this->template_dir as $dir) {
    if (preg_match('/plugins\/themes\/([^\/]+)/', $dir, $matches) && 
        file_exists($membershipFile = 'plugins/themes/' . $matches[1] . '/php/journal_staff/MembershipHandler.php')) {
        include_once($membershipFile);
        $this->assign('userMembership', MembershipHandler::getUserMembership($this));
        break;
    }
}
{/php}

{if $userMembership}
<div class="membership-role">
    <strong>Role:</strong> {$userMembership|escape}
</div>
{/if}
```

#### B. editorialTeam.tpl & editorialTeamBoard.tpl
Ganti link yang mengarah ke profile dengan menambahkan parameter `from="editorial"`:

```smarty
{* SEBELUM (contoh): *}
<a href="{url page="about" op="editorialTeamBio" path=$editor->getId() anchor=$editor->getFullName()}">
    {$editor->getFullName()|escape}
</a>

{* SESUDAH: *}
<a href="{url page="about" op="editorialTeamBio" path=$editor->getId() from="editorial" anchor=$editor->getFullName()}">
    {$editor->getFullName()|escape}
</a>
```

#### C. displayMembership.tpl
Ganti link yang mengarah ke profile dengan menambahkan parameter `from="membership"`:

```smarty
{* SEBELUM (contoh): *}
<a href="{url page="about" op="editorialTeamBio" path=$member->getId() anchor=$member->getFullName()}">
    {$member->getFullName()|escape}
</a>

{* SESUDAH: *}
<a href="{url page="about" op="editorialTeamBio" path=$member->getId() from="membership" anchor=$member->getFullName()}">
    {$member->getFullName()|escape}
</a>
```

#### D. Navigation Template (header.tpl atau sejenisnya)
Tambahkan kode untuk populate navigation menu:

```smarty
{php}
foreach ((array)$this->template_dir as $dir) {
    if (preg_match('/plugins\/themes\/([^\/]+)/', $dir, $matches) && 
        file_exists($membershipFile = 'plugins/themes/' . $matches[1] . '/php/journal_staff/MembershipHandler.php')) {
        include_once($membershipFile);
        MembershipHandler::setupNavigationGroups($this);
        break;
    }
}
{/php}

{if $hasPeopleGroups}
    {foreach from=$peopleGroups item=peopleGroup}
        <li class="c-header-expander__item">
            <a class="c-header-expander__link" 
               href="{url page="about" op="displayMembership" path=$peopleGroup.id}" 
               data-track="click" 
               data-track-label="link" 
               data-test="explore-nav-item">
                {$peopleGroup.title|escape}
            </a>
        </li>
    {/foreach}
{/if}
```

## How It Works

### 1. Context Parameter System
URL menggunakan parameter `from` untuk menentukan konteks:
- `editorialTeamBio/123?from=editorial` → tampilkan membership editorial (context=1)
- `editorialTeamBio/123?from=membership` → tampilkan membership non-editorial (context=2)
- `editorialTeamBio/123` → tampilkan semua membership (fallback)

### 2. Database Context Filtering
Sistem menggunakan field `context` di tabel `groups`:
```sql
-- Editorial groups (context=1)
SELECT * FROM groups WHERE context = 1 AND about_displayed = 1

-- Membership groups (context=2)  
SELECT * FROM groups WHERE context = 2 AND about_displayed = 1
```

### 3. Priority System
- Groups dengan `seq` (sequence) terendah mendapat prioritas tertinggi
- Hanya groups dengan `about_displayed = 1` yang diproses
- User membership ditampilkan berdasarkan group dengan sequence terkecil

### 4. Navigation Integration
Navigation menu otomatis menampilkan **hanya groups dengan `context = 2`** yang memiliki members.

## URL Examples
```
# Dari Editorial Team - tampil membership editorial
/about/editorialTeamBio/123?from=editorial#JohnDoe

# Dari Display Membership - tampil membership non-editorial
/about/editorialTeamBio/123?from=membership#JohnDoe

# Akses langsung - tampil berdasarkan prioritas sequence
/about/editorialTeamBio/123#JohnDoe
```

## Database Queries

### User Membership Query
```sql
SELECT g.group_id, g.seq, gs.setting_value as title
FROM groups g 
LEFT JOIN group_settings gs ON (g.group_id = gs.group_id AND gs.setting_name = 'title' AND gs.locale = ?) 
INNER JOIN group_memberships gm ON (g.group_id = gm.group_id)
WHERE g.assoc_type = 256 AND g.assoc_id = ? AND g.about_displayed = 1 
AND gm.user_id = ? AND g.context = ?
ORDER BY g.seq LIMIT 1
```

### Navigation Groups Query
```sql
SELECT DISTINCT g.group_id, g.seq, gs.setting_value as title
FROM groups g 
LEFT JOIN group_settings gs ON (g.group_id = gs.group_id AND gs.setting_name = 'title' AND gs.locale = ?) 
INNER JOIN group_memberships gm ON (g.group_id = gm.group_id)
WHERE g.assoc_type = 256 AND g.assoc_id = ? AND g.about_displayed = 1 AND g.context = 2
ORDER BY g.seq
```

## Configuration

### Debug Mode
Untuk debugging, tambahkan di template:
```php
{php}
// Debug: lihat context groups
$journal = Request::getJournal();
$result = DBConnection::getConn()->Execute(
    "SELECT group_id, seq, context FROM groups WHERE assoc_type = 256 AND assoc_id = ?",
    array($journal->getId())
);
while (!$result->EOF) {
    echo "Group {$result->fields['group_id']}: seq={$result->fields['seq']}, context={$result->fields['context']}<br>";
    $result->MoveNext();
}
{/php}
```

### Customization
Jika ingin mengubah default membership names, edit method `getDefaultMembership()` di `MembershipHandler.php`:

```php
private static function getDefaultMembership($context = null) {
    switch ($context) {
        case 'membership':
            return 'Anggota';  // Custom name
        case 'editorial':
        default:
            return 'Tim Editorial';  // Custom name
    }
}
```

## Backup & Recovery

### Before Installation
```bash
# Backup original templates
cp editorialTeamBio.tpl editorialTeamBio.tpl.backup
cp editorialTeam.tpl editorialTeam.tpl.backup
cp editorialTeamBoard.tpl editorialTeamBoard.tpl.backup
cp displayMembership.tpl displayMembership.tpl.backup
cp header.tpl header.tpl.backup
```

### Recovery
```bash
# Restore original templates
cp editorialTeamBio.tpl.backup editorialTeamBio.tpl
cp editorialTeam.tpl.backup editorialTeam.tpl
cp editorialTeamBoard.tpl.backup editorialTeamBoard.tpl
cp displayMembership.tpl.backup displayMembership.tpl
cp header.tpl.backup header.tpl

# Remove handler file
rm -f /plugins/themes/[nama-tema]/php/journal_staff/MembershipHandler.php
```

## Upgrade Considerations

### OJS Version Upgrade
1. **Backup semua file custom** sebelum upgrade
2. **Test compatibility** di staging environment
3. **Verify database structure** - pastikan field `context` masih ada
4. **Re-apply template modifications** jika diperlukan

### Theme Update
1. **Backup handler file** sebelum update theme
2. **Re-apply template modifications** setelah theme update
3. **Test functionality** setelah update

## Troubleshooting

### Membership Tidak Muncul
```bash
# 1. Check file handler ada
ls -la /plugins/themes/[nama-tema]/php/journal_staff/MembershipHandler.php

# 2. Check PHP error log
tail -f /path/to/php/error.log

# 3. Verify user memiliki membership
SELECT * FROM group_memberships WHERE user_id = [USER_ID];

# 4. Check groups context
SELECT group_id, context, about_displayed FROM groups WHERE assoc_type = 256;
```

### Navigation Menu Kosong
```sql
-- Verify groups dengan context=2 punya members
SELECT g.group_id, COUNT(gm.membership_id) as member_count
FROM groups g 
LEFT JOIN group_memberships gm ON g.group_id = gm.group_id
WHERE g.context = 2 AND g.about_displayed = 1
GROUP BY g.group_id;
```

### Wrong Membership Displayed
1. **Check parameter `from`** di URL
2. **Verify group context** di database
3. **Check sequence order** - group dengan seq terendah akan diprioritaskan

### Error "Cannot redeclare"
```bash
# Hapus file lama dan upload yang baru
rm /plugins/themes/[nama-tema]/php/journal_staff/MembershipHandler.php
# Upload file MembershipHandler.php yang baru
```

## Performance Notes
- **Handler di-load on-demand** - hanya saat template membutuhkan
- **Database queries optimized** dengan LIMIT dan proper indexing
- **Minimal memory usage** - tidak ada iterator loops
- **Consider caching** untuk high-traffic sites (optional)

## Security Notes
- **Input parameter di-sanitize** otomatis oleh OJS Request class
- **SQL injection prevention** dengan prepared statements
- **Error handling** mencegah information disclosure
- **File path validation** untuk prevent directory traversal

## Support & Maintenance
- **Compatible**: OJS 2.4.8.2
- **Tested**: PHP 5.6+ dan PHP 7.x
- **Author**: Custom Development
- **Version**: 1.3
- **Date**: 2025-05-28
- **License**: Same as OJS (GPL v2)

## Changelog

### v1.3 (2025-05-28) - Final Release
- **Added**: Context-based filtering menggunakan database field `context`
- **Fixed**: Eliminasi hardcode keyword detection
- **Improved**: Universal compatibility untuk semua jurnal
- **Added**: Navigation menu integration
- **Fixed**: Memory exhaustion dan iterator issues
- **Added**: Comprehensive documentation

### v1.2 (2025-05-28)
- **Added**: Direct database queries untuk menghindari iterator issues
- **Fixed**: Memory exhaustion problems
- **Improved**: Error handling

### v1.1 (2025-05-28)
- **Added**: Navigation menu support
- **Added**: Multiple context handling

### v1.0 (2025-05-28)
- **Initial release**: Basic contextual membership display
- **Added**: Editorial vs membership context detection

---

## Quick Start

Untuk implementasi cepat:

1. **Upload** `MembershipHandler.php` ke `/plugins/themes/[tema]/php/journal_staff/`
2. **Tambahkan** 4 baris kode di `editorialTeamBio.tpl`
3. **Update** parameter `from` di link templates
4. **Test** dengan mengklik profile dari berbagai halaman

**Done!** 🎉

Sistem akan otomatis menampilkan membership yang sesuai konteks tanpa konfigurasi tambahan.