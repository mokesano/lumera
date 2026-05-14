# 📚 Lumera — Editorial Workflow

**A modern, open-source journal management and publishing platform built on OJS 2.x by Sangia Publishing House (SPH). Designed for multi-lingual, secure, and scalable scholarly publishing.**

---

<p align="center">
  <img src="https://img.shields.io/badge/version-2.4.8--5-brightgreen?style=for-the-badge" alt="Version">
  <a href="https://github.com/codelumera/lumera-library/releases/tag/lumera-2_4_8-5"><img src="https://img.shields.io/badge/tag-lumera--2__4__8--5-blue?style=for-the-badge" alt="Git Tag"></a>
  <img src="https://img.shields.io/badge/PHP-^7.4-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP Version">
  <img src="https://img.shields.io/badge/license-GPL%20v3.0-blue?style=for-the-badge" alt="License">
  <img src="https://img.shields.io/badge/release%20date-May%2023%2C%202019-lightgrey?style=for-the-badge" alt="Release Date">
</p>

<br>

<p align="center">
  <em>📄 Submission → 👀 Peer Review → 🏭 Production → 📡 Publication</em>
</p>

---

## 📖 About Lumera

Lumera is a full-featured journal management system developed and maintained by **Sangia Publishing House (SPH)**. It is based on the widely adopted OJS 2.x codebase and enhanced with security hardening, performance optimizations, and user-friendly features tailored for scholarly publishing in diverse environments.

Visit the official site for more information and other open research systems: [wizdam.sangia.org](http://wizdam.sangia.org/)

---

## ✨ Key Features

| Feature | Description |
| :--- | :--- |
| 🔄 **Complete Workflow** | Submission, review, editing, production, and publishing lifecycle |
| 👥 **Multi-role Support** | Journal managers, editors, reviewers, authors, and readers with granular permissions |
| 🌍 **Localization** | Fully translatable; supports UTF-8 and multiple languages |
| 📊 **Usage Statistics** | Geolocation-aware usage stats with legacy GeoLite City integration |
| ⏰ **Scheduled Tasks** | Automated reminders and maintenance via cron/Windows Task Scheduler |
| 🔒 **Security Hardening** | Bcrypt hashing, force SSL login, file permission hardening, and recommended patches |
| 🧩 **Plugin Architecture** | Extendable with plugins for various functionalities |
| 📦 **Easy Installation** | Web-based installer and CLI installation option |
| 📚 **Documentation** | Comprehensive docs covering upgrade, FAQ, and patches |

---

## 🚀 Quick Start

### System Requirements

| Software | Recommended |
| :--- | :--- |
| **PHP** | 7.4 or newer (including PHP 8.x) |
| **Database** | MySQL ≥ 5.1 or PostgreSQL ≥ 8.0 |
| **Web Server** | Apache ≥ 1.3.2x / 2.0.4x or Microsoft IIS 6 |
| **Operating System** | Linux, BSD, Solaris, Mac OS X, Windows (any that supports the above) |

> ⚠️ **Note:** SPH does not test every combination; contributions from users on other platforms are welcome.

### Installation Steps

```bash
# 1. Extract Lumera to your web directory
tar -xzf lumera-2.4.8-5.tar.gz -C /var/www/html/

# 2. Make necessary directories writeable
chmod -R 755 config.inc.php public cache cache/t_cache cache/t_config cache/t_compile cache/_db

# 3. Create a non-web-accessible upload directory
mkdir /var/www/lumera-files
chmod 755 /var/www/lumera-files

# 4. Apply recommended patches (if any)
cd /var/www/html/lumera
# patch -p1 --dry-run < PATCH_FILE   # test first
# patch -p1 < PATCH_FILE             # apply

# 5. Run the web installer or CLI installer
php tools/install.php
# Then open http://yourdomain.com/lumera and follow on-screen instructions
```

For detailed instructions, refer to the full documentation inside the `docs/` folder.

---

## 🔧 Configuration Recommendations

For a secure production deployment:

- **Database**: Dedicate a database to Lumera; use unique credentials and automated backups.
- **Hashing**: Set `encryption = bcrypt` in `config.inc.php`.
- **SSL**: Enable `force_ssl_login = On`.
- **Files Directory**: Store uploaded files outside web root; restrict permissions.
- **Scheduled Tasks**: Enable `scheduled_tasks = On` and set up a cron job:
  ```bash
  0 * * * * php /path/to/lumera/tools/runScheduledTasks.php
  ```

---

## 🌍 Localization

To add a new language:

1. Translate XML files in `locale/`, `help/`, `registry/locale/`, and plugin-specific locale directories.
2. Add the locale entry to `registry/locales.xml`.
3. Install via Site Administration.

Full UTF-8 support requires PHP `mbstring` and MySQL ≥ 5.0 or PostgreSQL ≥ 7.1.

---

## 📦 Upgrading

See the `docs/UPGRADE` document for instructions on migrating from earlier OJS 1.x/2.x releases.

---

## 🧪 Third-party Libraries

A full list is maintained in `lib/pkp/lib/libraries.txt`.

- **GeoLite City**: Optional geolocation for usage stats. Download from [geoip-legacy](https://github.com/mokesano/geoip-legacy/blob/main/data/GeoLiteCity.dat) and place it into `plugins/generic/usageStats/`. Separate license agreement required.

---

## 🤝 Contributing

We welcome contributions! Please refer to the SPH development wiki for guidelines and recommended patches list:

- [Lumera Recommended Patches](http://wizdam.sangia.org/wiki/index.php/Lumera_Recommended_Patches)

For contributing translations or code, feel free to open issues or pull requests.

---

## 🐛 Bug Reports & Support

- **Forum**: [forum.wizdam.sangia.org](http://forum.wizdam.sangia.org/)
- **Issue Tracker**: [GitHub Issues](https://github.com/codelumera/lumera-library/issues)
- **Email**: [support.lumera@gmail.com](mailto:support.lumera@gmail.com)

---

## 📄 License

Lumera is released under the **GNU General Public License v3.0**. See the file `docs/COPYING` for full terms.

Third parties are free to modify and redistribute Lumera under the same license. SPH encourages patches and improvements.

---

<p align="center">
  <br>
  <creator>Built with ❤️ by <strong>Sangia Publishing House</strong> for the global scholarly community</creator>
  <br><br>
  <a href="https://github.com/mokesano/lumera/stargazers">
    <img src="https://img.shields.io/github/stars/mokesano/lumera?style=social" alt="GitHub Stars">
  </a>
  <a href="https://github.com/mokesano/lumera/network/members">
    <img src="https://img.shields.io/github/forks/mokesano/lumera?style=social" alt="GitHub Forks">
  </a>
  <br><br>
  <copyright>© 2019 Sangia Publishing House. Licensed under GPL v3.0.</copyright>
</p>