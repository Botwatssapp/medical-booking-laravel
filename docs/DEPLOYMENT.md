# SantéConnect — procédure de déploiement

Ce document prépare un **vrai** déploiement. Il ne constitue pas une preuve que l’application est déjà déployée.

Cible : Laravel 12, PHP 8.2+, MariaDB 10.4+ (ou MySQL compatible), Vite.

Ne jamais exécuter `migrate:fresh`, `db:wipe` ou `migrate:refresh` sur une base métier.

---

## 1. Prérequis serveur

- PHP 8.2+ avec extensions : ctype, curl, dom, fileinfo, json, mbstring, openssl, pdo_mysql, tokenizer, xml
- Composer 2
- Node.js 18+ (si le build assets se fait sur le serveur)
- MariaDB 10.4+ (colonnes générées `STORED` testées en Phase 11.1)
- Cron (scheduler) et, si des jobs sont ajoutés plus tard, un process manager pour `queue:work`

---

## 2. Créer la base (manuellement)

```sql
CREATE DATABASE medical_booking
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
```

Créer un utilisateur dédié avec droits sur cette base uniquement. Ne pas réutiliser un compte root en production.

---

## 3. Configuration `.env` production

Copier `.env.example` vers `.env` sur le serveur, puis renseigner :

```text
APP_NAME=SantéConnect
APP_ENV=production
APP_DEBUG=false
APP_KEY=          # php artisan key:generate une seule fois si vide
APP_URL=https://your-real-domain.example

DB_CONNECTION=mysql
DB_HOST=...
DB_PORT=3306
DB_DATABASE=medical_booking
DB_USERNAME=...
DB_PASSWORD=...

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
CACHE_STORE=database
QUEUE_CONNECTION=database

MAIL_MAILER=smtp   # ou le driver du provider
MAIL_FROM_ADDRESS=...
MAIL_FROM_NAME=SantéConnect

LOG_CHANNEL=daily
LOG_LEVEL=error
```

Ne jamais committer `.env`. Si le dépôt a déjà suivi `.env` : `git rm --cached .env` avant le premier commit.

---

## 4. Ordre de déploiement

```text
1.  Sauvegarde base (si la base existe déjà)
2.  Déployer le code
3.  composer install --no-dev --optimize-autoloader
4.  Vérifier .env (APP_DEBUG=false, APP_URL HTTPS, DB correcte)
5.  php artisan migrate --force
    uniquement après connexion vérifiée à la bonne base
6.  php artisan storage:link
7.  npm ci && npm run build
    ou déployer public/build depuis la CI
8.  php artisan optimize
9.  Configurer le cron scheduler
10. Démarrer le serveur web (document root = public/)
```

Vérifier la connexion avant migrate :

```bash
php artisan db:show
php artisan migrate:status
```

---

## 5. Caches Laravel (production)

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan optimize
```

Après un changement de `.env` :

```bash
php artisan optimize:clear
php artisan optimize
```

---

## 6. Scheduler

`appointments:expire` tourne **chaque minute** (`routes/console.php`).

Cron Linux :

```cron
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

Sans ce cron, les rendez-vous passés ne passent pas automatiquement `pending → cancelled` / `accepted → missed`.

---

## 7. Queue

`QUEUE_CONNECTION=database`. Tables : `jobs`, `job_batches`, `failed_jobs`.

Les notifications actuelles sont **synchrones** (pas `ShouldQueue`). Un worker n’est pas requis aujourd’hui.

Si des jobs asynchrones sont ajoutés plus tard :

```bash
php artisan queue:work --tries=3
```

via Supervisor/systemd. Ne pas lancer un worker « pour la forme ».

---

## 8. Storage et permissions (Linux)

```bash
php artisan storage:link
```

Le serveur web doit pouvoir écrire :

```text
storage/
bootstrap/cache/
```

Principe :

```text
propriétaire = utilisateur du serveur web
source PHP : lecture seule
.env : lecture seule, hors document root
pas de 777 par défaut
```

Exemple (ajuster l’utilisateur, souvent `www-data`) :

```bash
chown -R www-data:www-data storage bootstrap/cache
find storage bootstrap/cache -type d -exec chmod 775 {} \;
find storage bootstrap/cache -type f -exec chmod 664 {} \;
```

Ne jamais exposer `storage/framework`, `storage/logs` ou `.env` via le vhost. Document root = `public/`.

---

## 9. HTTPS / cookies

Derrière HTTPS :

```text
SESSION_SECURE_COOKIE=true
```

Si un reverse proxy termine TLS, configurer les proxies de confiance Laravel. CSRF reste activé sur le groupe `web`.

---

## 10. Sauvegarde et rollback

### Avant migrate

Dump SQL de la base cible.

### Si l’application casse après deploy

1. Remettre le code précédent.
2. `php artisan optimize:clear && php artisan optimize`
3. **Ne pas** `migrate:rollback` à l’aveugle si des données ont déjà été écrites.
4. Inspecter les migrations appliquées.
5. Restaurer le dump si et seulement si l’état SQL est incohérent.

Les migrations Phase 11 (`doctors.user_id` unique, `occupying_availability_id`, `speciality_id` RESTRICT, index statut/date) sont compatibles MariaDB 10.4.32. Leur `down()` existe ; ne l’utiliser qu’après analyse.

---

## 11. Smoke test post-déploiement

Guest : `/`, login, register  
Patient : dashboard, médecins, RDV, notifications, profil  
Médecin : dashboard, RDV, disponibilités, profil  
Admin : dashboard, users, doctors, specialties, appointments, profil  

Workflow : validation médecin → créneau → booking → accept → notification → cancel/reject → expiration.

---

## 12. Composer / npm production

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

Alternative : builder `public/build` en CI et déployer le dossier. Ne pas versionner `node_modules` ni `vendor`.
