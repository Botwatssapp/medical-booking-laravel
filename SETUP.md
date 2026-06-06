# Application Gestion de Réservation Médicale - Laravel 12

## 📋 Vue d'ensemble

Une plateforme complète permettant aux patients de réserver des rendez-vous médicaux en ligne, aux médecins de gérer leurs disponibilités et leurs rendez-vous, et à l'administrateur de gérer toute la plateforme.

## 🚀 Installation et Configuration

### Prérequis
- PHP 8.3+
- MySQL 8.0+
- Node.js 18+
- Composer

### Étapes d'installation

1. **Cloner le projet et installer les dépendances**
```bash
cd medical-booking-laravel
composer install
npm install
```

2. **Configurer le fichier .env**
```bash
cp .env.example .env
php artisan key:generate
```

3. **Configurer la base de données dans .env**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=medical_booking
DB_USERNAME=root
DB_PASSWORD=
```

4. **Créer la base de données**
```bash
mysql -u root -p
CREATE DATABASE medical_booking;
EXIT;
```

5. **Exécuter les migrations**
```bash
php artisan migrate
```

6. **Remplir la base de données avec les données de test**
```bash
php artisan db:seed
```

7. **Lier le répertoire storage public**
```bash
php artisan storage:link
```

8. **Compiler les assets**
```bash
npm run build
```

9. **Démarrer le serveur de développement**
```bash
php artisan serve
```

Le site sera accessible à `http://localhost:8000`

## 👥 Comptes de test

### Admin
- **Email**: admin@gmail.com
- **Mot de passe**: password

### Patients (50 créés automatiquement)
- Voir les données générées par les factories

### Médecins (20 créés automatiquement)
- Voir les données générées par les factories

## 📁 Structure du Projet

```
app/
├── Models/
│   ├── User.php              # Modèle utilisateur (Patient, Médecin, Admin)
│   ├── Doctor.php            # Modèle médecin
│   ├── Appointment.php       # Modèle rendez-vous
│   ├── Availability.php      # Modèle disponibilité
│   └── Speciality.php        # Modèle spécialité
├── Http/
│   ├── Controllers/
│   │   ├── Admin/            # Contrôleurs admin
│   │   ├── Doctor/           # Contrôleurs médecin
│   │   └── Patient/          # Contrôleurs patient
│   ├── Requests/             # Form Requests
│   └── Middleware/           # Middleware d'authentification
├── Mail/
│   ├── AppointmentConfirmed.php
│   ├── AppointmentCancelled.php
│   └── AppointmentRefused.php
└── Providers/

database/
├── migrations/               # Migrations de base de données
├── factories/                # Factories pour les tests
└── seeders/                  # Seeders pour initialiser les données

resources/
├── views/
│   ├── admin/                # Vues administrateur
│   ├── doctor/               # Vues médecin
│   ├── patient/              # Vues patient
│   ├── layouts/              # Layouts Blade
│   └── auth/                 # Vues d'authentification
├── css/
└── js/

routes/
└── web.php                   # Routes web
```

## 🔐 Authentification

Le projet utilise **Laravel Breeze** pour l'authentification. Trois rôles sont disponibles:

- **Patient**: Peut consulter les médecins, réserver des rendez-vous, gérer ses rendez-vous
- **Médecin**: Peut gérer ses disponibilités, consulter ses rendez-vous, accepter/refuser des rendez-vous
- **Administrateur**: Accès complet à la gestion de la plateforme

## 🛣️ Routes principales

### Admin
- `GET /admin/dashboard` - Tableau de bord administrateur
- `GET /admin/users` - Gestion des utilisateurs
- `GET /admin/doctors` - Gestion des médecins
- `GET /admin/specialties` - Gestion des spécialités
- `GET /admin/appointments` - Suivi des rendez-vous

### Patient
- `GET /patient/dashboard` - Tableau de bord patient
- `GET /patient/doctors` - Liste des médecins
- `GET /patient/doctors/{doctor}` - Profil du médecin
- `GET /patient/appointments` - Mes rendez-vous
- `POST /patient/appointments` - Créer un rendez-vous
- `PATCH /patient/appointments/{appointment}` - Modifier un rendez-vous
- `DELETE /patient/appointments/{appointment}` - Annuler un rendez-vous
- `GET /patient/profile/edit` - Éditer mon profil

### Médecin
- `GET /doctor/dashboard` - Tableau de bord médecin
- `GET /doctor/availabilities` - Gestion des disponibilités
- `GET /doctor/appointments` - Mes rendez-vous
- `PATCH /doctor/appointments/{appointment}` - Accepter/Refuser un rendez-vous
- `GET /doctor/profile/edit` - Éditer mon profil

## 📊 Base de Données

### Tables principales

**users**
- id, name, email, password, role, timestamps, deleted_at

**specialities**
- id, name, description, timestamps, deleted_at

**doctors**
- id, user_id, speciality_id, phone, address, bio, photo, timestamps, deleted_at

**availabilities**
- id, doctor_id, date, start_time, end_time, is_available, timestamps

**appointments**
- id, patient_id, doctor_id, availability_id, appointment_date, status, notes, timestamps, deleted_at

## 🔄 Workflow Rendez-vous

1. **Création**: Patient crée une demande de rendez-vous (statut: pending)
2. **Attente**: Médecin reçoit la demande
3. **Acceptation/Refus**: Médecin accepte ou refuse le rendez-vous
4. **Confirmation**: Patient reçoit une notification
5. **Réalisation**: Rendez-vous marqué comme complété
6. **Annulation**: Patient ou médecin peut annuler (futurs seulement)

## ✨ Fonctionnalités

### Patient
✅ Inscription et connexion  
✅ Consulter la liste des médecins avec filtrage par spécialité  
✅ Rechercher un médecin  
✅ Consulter le profil détaillé d'un médecin  
✅ Réserver un rendez-vous  
✅ Modifier un rendez-vous futur  
✅ Annuler un rendez-vous futur  
✅ Consulter l'historique de ses rendez-vous  
✅ Recevoir des notifications de confirmation  

### Médecin
✅ Connexion  
✅ Gérer ses disponibilités (créer, modifier, supprimer)  
✅ Consulter ses rendez-vous  
✅ Accepter un rendez-vous en attente  
✅ Refuser un rendez-vous  
✅ Annuler un rendez-vous  
✅ Gérer son profil médical  

### Administrateur
✅ Gérer les comptes utilisateurs (créer, modifier, supprimer)  
✅ Gérer les médecins (créer, modifier, supprimer)  
✅ Gérer les spécialités médicales  
✅ Consulter tous les rendez-vous  
✅ Suivre le statut des rendez-vous  
✅ Tableau de bord avec statistiques  
✅ Nombre de patients par spécialité  
✅ Gestion des rendez-vous non réalisés  

## 📧 Notifications

Les notifications suivantes sont implémentées:

- `AppointmentConfirmed` - Confirmation de rendez-vous accepté
- `AppointmentRejected` - Notification de rejet
- `AppointmentCancelled` - Notification d'annulation

## 🧪 Tests et Données

### Générer de nouvelles données
```bash
php artisan db:seed
```

### Seeders disponibles
- `RolesSeeder` - Crée l'admin
- `SpecialtySeeder` - Crée 8 spécialités
- `UserSeeder` - Crée 50 patients + 1 admin
- `DoctorSeeder` - Crée 20 médecins
- `AvailabilitySeeder` - Crée des disponibilités pour 30 jours
- `AppointmentSeeder` - Crée 200 rendez-vous aléatoires

## 🎨 UI/UX

- **Tailwind CSS** pour le styling
- **Interface responsive** adaptée mobile/desktop
- **Gestion d'erreurs** avec messages clairs
- **Notifications** pour les actions importantes

## 🔒 Sécurité

- ✅ CSRF protection
- ✅ Authorization policies
- ✅ Middleware d'authentification
- ✅ Validation des formulaires (Form Requests)
- ✅ Soft deletes pour conservation des données
- ✅ Hash des mots de passe

## 📝 Validation

### Form Requests implémentés
- `StoreAppointmentRequest`
- `UpdateAppointmentRequest`
- `StoreDoctorRequest`
- `UpdateDoctorRequest`
- `StoreSpecialtyRequest`
- `UpdateSpecialtyRequest`

## 🔄 Commandes Artisan utiles

```bash
# Migrations
php artisan migrate               # Exécuter les migrations
php artisan migrate:rollback     # Annuler la dernière migration
php artisan migrate:fresh        # Réinitialiser + migrer
php artisan migrate:fresh --seed # Réinitialiser + migrer + seeder

# Seeders
php artisan db:seed              # Exécuter tous les seeders
php artisan db:seed --class=UserSeeder  # Exécuter un seeder spécifique

# Cache
php artisan cache:clear          # Vider le cache
php artisan config:clear         # Vider le cache de config

# Tinker (Console interactive)
php artisan tinker
```

## 🚨 Troubleshooting

**Erreur de connexion à la base de données**
- Vérifier que MySQL est lancé
- Vérifier les credentials dans .env
- Vérifier que la base de données existe

**Erreur "File not found" pour les images**
- Exécuter: `php artisan storage:link`
- Vérifier que le répertoire storage/public/doctors existe

**Erreur de permission**
- Exécuter: `chmod -R 775 storage bootstrap/cache`

## 📚 Ressources

- [Laravel 12 Documentation](https://laravel.com/docs)
- [Tailwind CSS](https://tailwindcss.com/)
- [Eloquent ORM](https://laravel.com/docs/eloquent)
- [Laravel Policies](https://laravel.com/docs/authorization#creating-policies)

## 📄 Licence

MIT License

## 👨‍💻 Support

Pour toute question ou bug, veuillez créer une issue dans le projet.
