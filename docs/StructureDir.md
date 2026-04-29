assets/
├── ico/
│   └── index.php
├── img/
│   ├── bg/
│   │   └── index.php
│   ├── classical/
│   │   └── index.php
│   ├── SDGs_icon_SVG/
│   ├── static/
│   │   ├── brand/
│   │   │   └── index.php
│   │   └── logos/
│   └── index.php
├── js/                      # JavaScript kustom Wizdam
│   ├── dev/
│   │   └── perfect-sync-editor.js
│   ├── wizdam/              # Koleksi JS Wizdam (25+ files)
│   │   ├── ArticleHighlight_v1.js
│   │   ├── ArticleHighlight_v2.js
│   │   ├── Wizdam-Article.js
│   │   ├── WizdamArticleStyle.js
│   │   ├── WizdamCitedby.js
│   │   ├── editor_scopus.js
│   │   ├── editorgender.js
│   │   ├── gabungan.js
│   │   ├── inlinePdf.js
│   │   ├── journal-stats.js
│   │   ├── journalVisitorMap.js
│   │   ├── lazyload.js
│   │   ├── pdf-extract.js
│   │   ├── pdfobject.js
│   │   ├── pkp.min.js
│   │   ├── priceupdate.js
│   │   ├── relatedItems.js
│   │   ├── sangia.js
│   │   ├── sangiasearch.js
│   │   ├── sangiastyle.js
│   │   ├── sintaScore.js
│   │   └── wizdam_search.js
│   ├── Wizdam-Article.js
│   ├── WizdamCitedby.js
│   ├── auth-forms.js
│   ├── editor_scopus.js
│   ├── editorgender.js
│   ├── journal-stats.js
│   ├── lazyload.js
│   ├── priceupdate.js
│   ├── sangiastyle.js
│   ├── sintaScore.js
│   ├── submission-progress.js
│   ├── wizdam_authForms.js
│   └── wizdam_search.js
└── static/
    ├── branded/
    ├── favicon/
    ├── images/
    └── styles/

classes/
├── admin/
│   └── form/
│       ├── AboutSiteForm.inc.php
│       ├── JournalSiteSettingsForm.inc.php
│       └── SiteSettingsForm.inc.php
│
├── announcement/
│   ├── Announcement.inc.php
│   ├── AnnouncementDAO.inc.php
│   ├── AnnouncementType.inc.php
│   └── AnnouncementTypeDAO.inc.php
│
├── article/                 # Manajemen artikel jurnal
│   ├── Article.inc.php
│   ├── ArticleComment.inc.php
│   ├── ArticleCommentDAO.inc.php
│   ├── ArticleDAO.inc.php
│   ├── ArticleFile.inc.php
│   ├── ArticleFileDAO.inc.php
│   ├── ArticleGalley.inc.php
│   ├── ArticleGalleyDAO.inc.php
│   ├── ArticleHTMLGalley.inc.php
│   ├── ArticleNote.inc.php
│   ├── ArticleNoteDAO.inc.php
│   ├── ArticleTombstoneManager.inc.php
│   ├── Author.inc.php
│   ├── AuthorDAO.inc.php
│   ├── PublishedArticle.inc.php
│   ├── PublishedArticleDAO.inc.php
│   ├── SuppFile.inc.php
│   ├── SuppFileDAO.inc.php
│   └── log/
│       ├── ArticleEmailLogDAO.inc.php
│       ├── ArticleEmailLogEntry.inc.php
│       ├── ArticleEventLogDAO.inc.php
│       ├── ArticleEventLogEntry.inc.php
│       └── ArticleLog.inc.php
│
├── author/
│   └── form/
│       └── submit/
│           ├── AuthorSubmitForm.inc.php
│           ├── AuthorSubmitStep1Form.inc.php
│           ├── AuthorSubmitStep2Form.inc.php
│           ├── AuthorSubmitStep3Form.inc.php
│           ├── AuthorSubmitStep4Form.inc.php
│           ├── AuthorSubmitStep5Form.inc.php
│           └── AuthorSubmitSuppFileForm.inc.php
│
├── comment/
│   └── form/
│       └── CommentForm.inc.php
│
├── controllers/
│   └── grid/
│       └── filter/
│           └── FilterGridHandler.inc.php
│
├── core/                    # Komponen inti aplikasi
│   ├── Application.inc.php
│   ├── PageRouter.inc.php
│   ├── Request.inc.php
│   └── WizdamWAF.inc.php   # Web Application Firewall Wizdam
│
├── file/                    # Manajemen file
│   ├── ArticleFileManager.inc.php
│   ├── IssueFileManager.inc.php
│   ├── JournalFileManager.inc.php
│   ├── PublicFileManager.inc.php
│   └── TemporaryFileManager.inc.php
│
├── gift/
│   ├── Gift.inc.php
│   └── GiftDAO.inc.php
│
├── handler/                 # Handler untuk request
│   ├── Handler.inc.php
│   └── validation/
│       ├── HandlerValidatorJournal.inc.php
│       └── HandlerValidatorSubmissionComment.inc.php
│
├── help/
│   ├── Help.inc.php
│   └── OJSHelpMappingFile.inc.php
│
├── i18n/                    # Internationalization
│   ├── AppLocale.inc.php
│   └── LanguageAction.inc.php
│
├── install/                 # Instalasi & upgrade
│   ├── Install.inc.php
│   ├── Upgrade.inc.php
│   └── form/
│       ├── InstallForm.inc.php
│       └── UpgradeForm.inc.php
│
├── issue/                   # Manajemen issue jurnal
│   ├── Issue.inc.php
│   ├── IssueAccess.inc.php
│   ├── IssueAction.inc.php
│   ├── IssueCover.inc.php
│   ├── IssueDAO.inc.php
│   ├── IssueDisplay.inc.php
│   ├── IssueFile.inc.php
│   ├── IssueFileDAO.inc.php
│   ├── IssueGalley.inc.php
│   ├── IssueGalleyDAO.inc.php
│   ├── IssuePubIdService.inc.php
│   ├── IssuePublication.inc.php
│   └── form/
│       ├── IssueForm.inc.php
│       └── IssueGalleyForm.inc.php
│
├── journal/                 # Manajemen jurnal
│   ├── Journal.inc.php
│   ├── JournalDAO.inc.php
│   ├── JournalSettingsDAO.inc.php
│   ├── JournalStatisticsDAO.inc.php
│   ├── Section.inc.php
│   ├── SectionDAO.inc.php
│   ├── SectionEditorsDAO.inc.php
│   └── categories/
│       ├── CategoryDAO.inc.php
│       └── CategoryForm.inc.php
│
├── mail/                    # Sistem email
│   ├── ArticleMailTemplate.inc.php
│   ├── EmailTemplateDAO.inc.php
│   └── MailTemplate.inc.php
│
├── manager/                 # Manajer jurnal
│   └── form/
│       ├── AnnouncementForm.inc.php
│       ├── AnnouncementTypeForm.inc.php
│       ├── EmailTemplateForm.inc.php
│       ├── GroupForm.inc.php
│       ├── LanguageSettingsForm.inc.php
│       ├── ReviewFormElementForm.inc.php
│       ├── ReviewFormForm.inc.php
│       ├── SectionForm.inc.php
│       ├── UserManagementForm.inc.php
│       └── setup/
│           ├── JournalSetupForm.inc.php
│           ├── JournalSetupStep1Form.inc.php
│           ├── JournalSetupStep2Form.inc.php
│           ├── JournalSetupStep3Form.inc.php
│           ├── JournalSetupStep4Form.inc.php
│           └── JournalSetupStep5Form.inc.php
│
├── note/
│   ├── Note.inc.php
│   └── NoteDAO.inc.php
│
├── notification/
│   ├── Notification.inc.php
│   ├── NotificationManager.inc.php
│   └── form/
│       └── NotificationSettingsForm.inc.php
│
├── oai/ojs/                 # OAI-PMH support
│   ├── JournalOAI.inc.php
│   └── OAIDAO.inc.php
│
├── payment/ojs/             # Sistem pembayaran
│   ├── OJSCompletedPayment.inc.php
│   ├── OJSCompletedPaymentDAO.inc.php
│   ├── OJSPaymentAction.inc.php
│   ├── OJSPaymentManager.inc.php
│   ├── OJSQueuedPayment.inc.php
│   └── form/
│       ├── PayMethodSettingsForm.inc.php
│       └── PaymentSettingsForm.inc.php
│
├── plugins/                 # Sistem plugin
│   ├── AuthPlugin.inc.php
│   ├── CitationPlugin.inc.php
│   ├── GatewayPlugin.inc.php
│   ├── ImplicitAuthPlugin.inc.php
│   ├── ImportExportPlugin.inc.php
│   ├── PaymethodPlugin.inc.php
│   ├── Plugin.inc.php
│   ├── PluginSettingsDAO.inc.php
│   ├── PubIdPlugin.inc.php
│   ├── PubIdPluginHelper.inc.php
│   ├── ReportPlugin.inc.php
│   └── ThemePlugin.inc.php
│
├── rt/ojs/                  # Research Tools
│   ├── JournalRT.inc.php
│   ├── JournalRTAdmin.inc.php
│   ├── RTDAO.inc.php
│   └── form/
│       ├── ContextForm.inc.php
│       ├── SearchForm.inc.php
│       └── VersionForm.inc.php
│
├── search/                  # Pencarian artikel
│   ├── ArticleSearch.inc.php
│   ├── ArticleSearchDAO.inc.php
│   └── ArticleSearchIndex.inc.php
│
├── sectionEditor/
│   └── form/
│       └── CreateReviewerForm.inc.php
│
├── security/                # Keamanan & otorisasi
│   ├── Hashing.inc.php
│   ├── Role.inc.php
│   ├── RoleDAO.inc.php
│   ├── Validation.inc.php
│   ├── authorization/
│   │   ├── OjsJournalAccessPolicy.inc.php
│   │   ├── OjsSubmissionAccessPolicy.inc.php
│   │   └── internal/
│   │       ├── CopyeditorSubmissionAssignmentPolicy.inc.php
│   │       ├── CopyeditorSubmissionRequiredPolicy.inc.php
│   │       ├── JournalPolicy.inc.php
│   │       ├── SectionEditorSubmissionRequiredPolicy.inc.php
│   │       ├── SectionSubmissionAssignmentPolicy.inc.php
│   │       └── SectionSubmissionRequiredPolicy.inc.php
│   └── form/
│       └── AuthSourceSettingsForm.inc.php
│
├── signoff/
│   └── SignoffDAO.inc.php
│
├── statistics/
│   ├── MetricsDAO.inc.php
│   └── StatisticsHelper.inc.php
│
├── submission/              # Workflow submission
│   ├── author/
│   │   ├── AuthorAction.inc.php
│   │   ├── AuthorSubmission.inc.php
│   │   └── AuthorSubmissionDAO.inc.php
│   ├── common/
│   │   └── Action.inc.php
│   ├── copyeditor/
│   │   ├── CopyeditorAction.inc.php
│   │   ├── CopyeditorSubmission.inc.php
│   │   └── CopyeditorSubmissionDAO.inc.php
│   ├── editAssignment/
│   │   ├── EditAssignment.inc.php
│   │   └── EditAssignmentDAO.inc.php
│   ├── editor/
│   │   ├── EditorAction.inc.php
│   │   ├── EditorSubmission.inc.php
│   │   └── EditorSubmissionDAO.inc.php
│   ├── form/
│   │   ├── ArticleGalleyForm.inc.php
│   │   ├── MetadataForm.inc.php
│   │   ├── ReviewFormResponseForm.inc.php
│   │   ├── SuppFileForm.inc.php
│   │   └── comment/
│   │       ├── CommentForm.inc.php
│   │       ├── CopyeditCommentForm.inc.php
│   │       ├── EditCommentForm.inc.php
│   │       ├── EditorDecisionCommentForm.inc.php
│   │       ├── LayoutCommentForm.inc.php
│   │       ├── PeerReviewCommentForm.inc.php
│   │       └── ProofreadCommentForm.inc.php
│   ├── layoutEditor/
│   │   ├── LayoutEditorAction.inc.php
│   │   ├── LayoutEditorSubmission.inc.php
│   │   └── LayoutEditorSubmissionDAO.inc.php
│   ├── proofreader/
│   │   ├── ProofreaderAction.inc.php
│   │   ├── ProofreaderSubmission.inc.php
│   │   └── ProofreaderSubmissionDAO.inc.php
│   ├── reviewAssignment/
│   │   ├── ReviewAssignment.inc.php
│   │   └── ReviewAssignmentDAO.inc.php
│   ├── reviewer/
│   │   ├── ReviewerAction.inc.php
│   │   ├── ReviewerSubmission.inc.php
│   │   └── ReviewerSubmissionDAO.inc.php
│   └── sectionEditor/
│       ├── SectionEditorAction.inc.php
│       ├── SectionEditorSubmission.inc.php
│       └── SectionEditorSubmissionDAO.inc.php
│
├── subscription/            # Berlangganan
│   ├── IndividualSubscription.inc.php
│   ├── IndividualSubscriptionDAO.inc.php
│   ├── InstitutionalSubscription.inc.php
│   ├── InstitutionalSubscriptionDAO.inc.php
│   ├── Subscription.inc.php
│   ├── SubscriptionAction.inc.php
│   ├── SubscriptionDAO.inc.php
│   ├── SubscriptionType.inc.php
│   ├── SubscriptionTypeDAO.inc.php
│   └── form/
│       ├── GiftIndividualSubscriptionForm.inc.php
│       ├── IndividualSubscriptionForm.inc.php
│       ├── InstitutionalSubscriptionForm.inc.php
│       ├── SubscriptionForm.inc.php
│       ├── SubscriptionPolicyForm.inc.php
│       ├── SubscriptionTypeForm.inc.php
│       ├── UserIndividualSubscriptionForm.inc.php
│       └── UserInstitutionalSubscriptionForm.inc.php
│
├── sword/
│   └── OJSSwordDeposit.inc.php
│
├── tasks/                   # Scheduled tasks
│   ├── OpenAccessNotification.inc.php
│   ├── ReviewReminder.inc.php
│   └── SubscriptionExpiryReminder.inc.php
│
├── template/
│   └── TemplateManager.inc.php
│
└── user/                    # Manajemen user
    ├── User.inc.php
    ├── UserAction.inc.php
    ├── UserDAO.inc.php
    ├── UserSettingsDAO.inc.php
    └── form/
        ├── ChangePasswordForm.inc.php
        ├── LoginChangePasswordForm.inc.php
        ├── ProfileForm.inc.php
        └── RegistrationForm.inc.php

controllers/
├── grid/
│   └── citation/
│       └── CitationGridHandler.inc.php
└── statistics/
    ├── ReportGeneratorHandler.inc.php
    └── form/
        └── ReportGeneratorForm.inc.php

js/
├── pages/
│   └── search/
│       └── SearchFormHandler.js
├── statistics/
│   └── ReportGeneratorFormHandler.js
├── inlinePdf.js
├── pdfobject.js
├── pkp.min.js
└── relatedItems.js

password_compat/
├── lib/
│   └── password.php
└── test/Unit/
    ├── PasswordGetInfoTest.php
    ├── PasswordHashTest.php
    ├── PasswordNeedsRehashTest.php
    └── PasswordVerifyTest.php

library/
├── autoload.php
├── composer/              # Autoloader Composer
│   ├── ClassLoader.php
│   ├── InstalledVersions.php
│   ├── autoload_classmap.php
│   ├── autoload_files.php
│   ├── autoload_namespaces.php
│   ├── autoload_psr4.php
│   ├── autoload_real.php
│   ├── autoload_static.php
│   ├── installed.php
│   └── platform_check.php
│
├── chillerlan/
│   ├── php-qrcode/src/   # QR Code generator (20+ files)
│   └── php-settings-container/src/
│
├── graham-campbell/
│   └── result-type/src/
│       ├── Error.php
│       ├── Result.php
│       └── Success.php
│
├── guzzlehttp/            # HTTP Client
│   ├── guzzle/src/       # Guzzle HTTP (30+ files)
│   ├── promises/src/     # Promises (15+ files)
│   └── psr7/src/         # PSR-7 (25+ files)
│
├── midtrans/midtrans-php/ # Payment Gateway
│   ├── Midtrans.php
│   ├── Midtrans/
│   │   ├── ApiRequestor.php
│   │   ├── Config.php
│   │   ├── CoreApi.php
│   │   ├── Notification.php
│   │   ├── Sanitizer.php
│   │   ├── Snap.php
│   │   ├── SnapApiRequestor.php
│   │   └── Transaction.php
│   ├── SnapBi/
│   │   ├── SnapBi.php
│   │   ├── SnapBiApiRequestor.php
│   │   └── SnapBiConfig.php
│   ├── examples/         # Contoh implementasi
│   └── tests/            # Unit tests
│
└── mpdf/mpdf/            # PDF Generator
    ├── data/
    │   ├── collations/   # 100+ file collation
    │   ├── font/
    │   ├── patterns/
    │   └── ...
    └── src/
        ├── Barcode/
        ├── Color/
        ├── Config/
        ├── Container/
        ├── Conversion/
        ├── Css/
        ├── Exception/
        ├── File/
        ├── Fonts/
        ├── Gif/
        ├── Http/
        ├── Image/
        ├── Language/
        ├── Log/
        ├── Output/
        ├── Pdf/
        ├── Shaper/
        ├── Tag/          # 80+ HTML tag handlers
        ├── Utils/
        └── Writer/

dbscripts/
└── xml/
    └── upgrade/

docs/
└── release-notes/

fonts/
├── bp/, dt_, europa/, freefont/, hd/, kievit/, museosans/, mw/
├── nexus/
│   ├── nexus-sans/
│   └── nexus-serif/
├── sagoe-sans/
├── sangia/
│   ├── elsevier/
│   └── nexus-sans/
├── sn/, source-code/
└── index.php (tiap subdirektori)

help/
├── ar_IQ/, ca_ES/, da_DK/, de_DE/, el_GR/, en_US/, es_ES/, fa_IR/
├── fr_CA/, fr_FR/, gl_ES/, it_IT/, ja_JP/, pt_BR/, pt_PT/
├── sv_SE/, tr_TR/, vi_VN/
│
Setiap bahasa memiliki struktur:
├── editorial/toc/, editorial/topic/
├── index/toc/, index/topic/
├── intro/toc/, intro/topic/
├── journal/toc/, journal/topic/
├── publishing/toc/, publishing/topic/
├── site/toc/, site/topic/
├── submission/toc/, submission/topic/
└── user/toc/, user/topic/

