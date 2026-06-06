#!/bin/bash

# Script d'installation complète de l'application Medical Booking

echo "🔧 Installation de Medical Booking System..."

# 1. Installer les dépendances PHP
echo ""
echo "📦 Installation des dépendances PHP..."
composer install

# 2. Copier .env
echo ""
echo "⚙️  Configuration du .env..."
if [ ! -f .env ]; then
    cp .env.example .env
    echo "✅ Fichier .env créé"
else
    echo "✅ Fichier .env existe déjà"
fi

# 3. Générer la clé d'application
echo ""
echo "🔑 Génération de la clé d'application..."
php artisan key:generate

# 4. Créer la base de données (si MySQL est disponible)
echo ""
echo "🗄️  Configuration de la base de données..."
echo "Assurez-vous que MySQL est lancé et que la base 'medical_booking' existe"
echo "Créer la base avec: mysql -u root -p -e \"CREATE DATABASE medical_booking;\""
read -p "Appuyez sur Entrée quand la base est prête..."

# 5. Exécuter les migrations
echo ""
echo "🔄 Exécution des migrations..."
php artisan migrate

# 6. Remplir la base de données
echo ""
echo "📊 Remplissage de la base de données..."
php artisan db:seed

# 7. Créer le lien symbollique pour le stockage
echo ""
echo "🔗 Création du lien pour le stockage public..."
php artisan storage:link

# 8. Installer les dépendances npm
echo ""
echo "📦 Installation des dépendances JavaScript..."
npm install

# 9. Compiler les assets
echo ""
echo "🎨 Compilation des assets..."
npm run build

# 10. Afficher les informations de démarrage
echo ""
echo "✅ Installation terminée!"
echo ""
echo "🚀 Pour démarrer le serveur de développement, exécutez:"
echo "   php artisan serve"
echo ""
echo "📝 Compte administrateur par défaut:"
echo "   Email: admin@gmail.com"
echo "   Mot de passe: password"
echo ""
echo "🌐 L'application sera accessible à: http://localhost:8000"
