## 1. Pindah ke direktori repositori yang akan dibersihkan

```powershell
Set-Location -Path "C:\xampp\htdocs\[nama-repo]"
```

---

## 2. Hentikan pelacakan semua file `.json.gz` yang sudah ada di indeks Git

```powershell
git ls-files '*.json.gz' | ForEach-Object { git rm --cached $_ }
```

---

## 3. Hentikan pelacakan file cache lain jika ada (`.tpl.php`, sesuaikan bila perlu)

```powershell
git ls-files '*.tpl.php' | ForEach-Object { git rm --cached $_ }
```

---

## 4. Edit file `.gitignore` dan pastikan berisi baris berikut (tambahkan atau koreksi)

```gitignore
**/cache/*
!**/cache/.gitkeep
```

---

## 5. Buat file `.gitkeep` di folder `cache/t_cache`, `cache/t_compile`, `cache/t_config`, dan folder cache lain yang wajib ada

```powershell
$folderWajib = @(
    "cache/t_cache",
    "cache/t_compile",
    "cache/t_config",
    "cache/_db",
    "cache/HTML",
    "cache/URI"
)

foreach ($folder in $folderWajib) {
    New-Item -ItemType Directory -Force -Path $folder | Out-Null
    $gitkeepPath = Join-Path $folder ".gitkeep"
    if (-not (Test-Path $gitkeepPath)) {
        New-Item -ItemType File -Path $gitkeepPath -Force | Out-Null
    }
}
```

---

## 6. Tambahkan semua file `.gitkeep` ke staging (paksa)

```powershell
Get-ChildItem -Recurse -Filter ".gitkeep" | ForEach-Object { git add --force $_.FullName }
```

---

## 7. Tambahkan perubahan pada `.gitignore`

```powershell
git add .gitignore
```

---

## 8. Commit perubahan

```powershell
git commit -m "Hapus pelacakan file cache, pertahankan folder cache dengan .gitkeep"
```

---

## 9. Push ke GitHub

```powershell
git push
```

---

## 10. Verifikasi

```powershell
git status
git status --ignored
git ls-files '*.json.gz'
git ls-files '*.gitkeep'
```

- `git status` harus bersih.  
- `git status --ignored` harus menampilkan file `.json.gz` di bagian **Ignored files**.  
- `git ls-files '*.json.gz'` harus **kosong**.  
- `git ls-files '*.gitkeep'` harus menampilkan file `.gitkeep` yang baru dibuat.

Setelah push, **file cache tidak akan ada lagi di commit terbaru di GitHub**. Folder `cache/t_cache`, `cache/t_compile`, dan `cache/t_config` tetap muncul karena berisi `.gitkeep`.

Selesai.