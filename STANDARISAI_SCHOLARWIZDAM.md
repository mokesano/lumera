### 📜 PANDUAN STANDAR MODERNISASI KODE SCHOLARWIZDAM

#### 1. Strict Typing & Type Safety (Wajib di PHP 8+)
*   **Deklarasi Strict**: Selalu mulai file dengan `declare(strict_types=1);`.
*   **Type Hints**: Tambahkan type hint pada parameter dan return type fungsi (`int`, `string`, `bool`, `array`, `?string`, `?int`, `void`).
*   **Explicit Casting**: Lakukan casting eksplisit sebelum melempar variabel ke fungsi strict (seperti `explode`, `substr`, atau query DB):
    ```php
    // SALAH: Bisa fatal error di PHP 8 jika null
    explode(':', $row['installed_locales']); 
    
    // BENAR:
    explode(':', (string) ($row['installed_locales'] ?? ''));
    ```

#### 2. Modern Array & Syntax
*   **Short Array Syntax**: Ganti semua `array()` menjadi `[]`.
*   **Array Push**: Ganti `array_push($arr, $val)` menjadi `$arr[] = $val`.
*   **Null Coalescing**: Ganti ternary operator `isset($x) ? $x : $default` menjadi `$x ?? $default`.
*   **Strict Comparison**: Selalu gunakan `===` dan `!==` alih-alih `==` dan `!=` (terutama untuk string kosong `''` atau pengecekan `null`).
*   **Implode**: Gunakan `implode()` alih-alih alias lama `join()`.

#### 3. Database & DAO Operations (ADODB)
*   **Parameter Binding**: **SELALU** bungkus parameter query dalam array, bahkan jika hanya satu parameter.
    ```php
    // SALAH
    $this->retrieve('SELECT * FROM table WHERE id = ?', (int) $id);
    // BENAR
    $this->retrieve('SELECT * FROM table WHERE id = ?', [(int) $id]);
    ```
*   **Indexed Array for ADODB**: Jika melemparkan array konteks ke `retrieve()`, pastikan itu adalah *indexed array* (gunakan `array_values($context)`), bukan *associative array*, agar binding `?` tidak gagal.
*   **EOF Checking**: Gunakan `!$result->EOF` alih-alih `$result->RecordCount() > 0` untuk pengecekan yang lebih andal dan cepat.
*   **Memory Management**: **SELALU** panggil `$result->Close();` dan `unset($result);` segera setelah data diekstrak untuk mencegah memory leak.
*   **Batch Operations**: Hindari N+1 query di dalam loop. Gunakan satu query `DELETE` diikuti satu query `INSERT ... VALUES (), ()` untuk operasi massal.

#### 4. Mengatasi Error VS Code / Intelephense (P1013, P1114, P1119)
*   **Type Narrowing (P1013/P1119)**: Jika VS Code bingung tipe variabel (misal: return dari `DAORegistry` atau `$result->fields`), tambahkan PHPDoc tepat di atasnya:
    ```php
    /** @var JournalDAO $journalDao */
    $journalDao = DAORegistry::getDAO('JournalDAO');

    /** @var array|bool $fields */
    $fields = $result->fields;
    $value = $fields['column_name'] ?? null;
    ```
*   **Pass by Reference (P1114)**: Jangan pernah melempar literal (seperti `null`) atau return value fungsi langsung ke parameter yang di-pass by reference (`&`). Simpan dulu ke variabel:
    ```php
    // SALAH
    $this->convertToDB($value, null); 
    
    // BENAR
    $type = null;
    $this->convertToDB($value, $type);
    ```
*   **Liskov Substitution**: Jangan menambahkan type hint di method *child class* jika *parent class*-nya tidak memilikinya, agar tidak terjadi error signature mismatch.

#### 5. Object-Oriented & Legacy OJS Compatibility
*   **Constructor Shim**: Pertahankan constructor lama untuk backward compatibility, tapi pastikan memanggil `self::__construct()`:
    ```php
    public function ClassName() {
        trigger_error("...", E_USER_DEPRECATED);
        self::__construct();
    }
    ```
*   **Instanceof over is_a()**: Gunakan `instanceof` untuk mengecek tipe objek (lebih cepat dan aman di PHP 8):
    ```php
    if ($galley instanceof ArticleHTMLGalley) { ... }
    ```
*   **DataObject Settings**: Saat menangani setting multi-locale, pastikan struktur array-nya benar: `$data[$locale] = $value;`.

#### 6. Handler & Routing Best Practices
*   **Deprecated Request Methods**: Ganti semua pemanggilan `Request::getJournal()`, `Request::url()`, dll., dengan instansiasi `$request`:
    ```php
    $router = $request->getRouter();
    $url = $router->url($request, null, 'page', 'op');
    ```
*   **Early Return**: Selalu tambahkan `return;` tepat setelah `$request->redirect(...)` untuk menghentikan eksekusi kode di bawahnya.
*   **Micro-Payloads**: Gabungkan beberapa `$templateMgr->assign()` menjadi satu blok array agar kode lebih rapi:
    ```php
    $templateMgr->assign([
        'var1' => $val1,
        'var2' => $val2
    ]);
    ```
*   **Variable Initialization**: Inisialisasi variabel (seperti `$publicFileManager`) di *scope* terluar fungsi jika akan digunakan di dalam blok `if/else` untuk menghindari warning "Possible undefined variable".

#### 7. Template & UI/UX Fixes
*   **Breadcrumb Reset**: Jika memuat konten dinamis (seperti Current Issue) di homepage, reset `$templateMgr->assign('pageHierarchy', ...)` agar tidak menimpa breadcrumb utama.
*   **Sanitasi Template Variables**: Untuk mencegah error PHP 8 `Trying to access array offset on value of type null` di Smarty, pastikan variabel yang dilempar ke template bukan `null`, melainkan `[]` atau `''` (string kosong) sebagai fallback.