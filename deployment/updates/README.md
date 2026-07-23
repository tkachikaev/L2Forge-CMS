# KaevCMS Web Update Packages

The web updater accepts a ZIP archive with `kaevcms-update.json` at the archive root and logical payload files under `payload/core/` and `payload/public/`.

- `core/` is written to the KaevCMS application root.
- `public/` is written to Laravel's active public path. This supports both standard and split shared-hosting installations.
- `.env`, `storage`, SQLite runtime databases, uploads and the split-layout path configuration are protected and cannot be changed by an update package.

A cumulative package supports every installed version between `minimum_version` and `maximum_version`. Laravel runs every missing core migration in order, so intermediate releases do not need to be installed separately.

Build a package from an extracted target release:

```powershell
php deployment/updates/build-package.php `
    --root="C:\Releases\KaevCMS-0.33.0" `
    --output="C:\Releases\KaevCMS-update-to-0.33.0.zip" `
    --minimum=0.32.0 `
    --maximum=0.32.9 `
    --target=0.33.0 `
    --delete-file=deployment/updates/deletions.json `
    --previous-root="C:\Releases\KaevCMS-0.32.9" `
    --update-history
```

The package builder adds per-file SHA256 hashes. These hashes detect archive damage and tampering after the manifest was created. Public release authenticity will additionally require a release-signing key before automatic remote downloads are enabled.

Web Updater 1.0 excludes `vendor/` and rejects packages whose `composer.lock` differs from the installed release. Dependency changes require a full deployment until the updater gains a separate post-replacement bootstrap phase.

Before installation the owner receives a one-time maintenance bypass URL. If the request is interrupted, that URL restores access to the updater page, where the persisted database and file backup manifests can be used for manual rollback.

`deletions.json` хранит историю удалений по целевым версиям. `--previous-root` автоматически вычисляет пути, присутствовавшие в предыдущем релизе и отсутствующие в целевом. Пакет получает объединение всех удалений новее `minimum_version`, поэтому старые файлы корректно убираются при переходе через несколько версий.
