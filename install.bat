@echo off
REM Script d'installation complète de l'application Medical Booking pour Windows

echo.
echo 🔧 Installation de Medical Booking System...

REM 1. Installer les dépendances PHP
echo.
echo 📦 Installation des dépendances PHP...
call composer install

REM 2. Copier .env
echo.
echo ⚙️  Configuration du .env...
if not exist ".env" (
    copy .env.example .env
    echo ✅ Fichier .env créé
) else (
    echo ✅ Fichier .env existe déjà
)

REM 3. Générer la clé d'application
echo.
echo 🔑 Génération de la clé d'application...
call php artisan key:generate

REM 4. Information sur la base de données
echo.
echo 🗄️  Configuration de la base de données...
echo Assurez-vous que MySQL est lancé et que la base 'medical_booking' existe
echo Créer la base avec: mysql -u root -p -e "CREATE DATABASE medical_booking;"
pause

REM 5. Exécuter les migrations
echo.
echo 🔄 Exécution des migrations...
call php artisan migrate

REM 6. Remplir la base de données
echo.
echo 📊 Remplissage de la base de données...
call php artisan db:seed

REM 7. Créer le lien symbollique pour le stockage
echo.
echo 🔗 Création du lien pour le stockage public...
call php artisan storage:link

REM 8. Installer les dépendances npm
echo.
echo 📦 Installation des dépendances JavaScript...
call npm install

REM 9. Compiler les assets
echo.
echo 🎨 Compilation des assets...
call npm run build

REM 10. Afficher les informations de démarrage
echo.
echo ✅ Installation terminée!
echo.
echo 🚀 Pour démarrer le serveur de développement, exécutez:
echo    php artisan serve
echo.
echo 📝 Compte administrateur par défaut:
echo    Email: admin@gmail.com
echo    Mot de passe: password
echo.
echo 🌐 L'application sera accessible à: http://localhost:8000
echo.
pause
