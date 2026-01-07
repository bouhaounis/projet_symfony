# 📋 Liste Complète des Fonctionnalités à Implémenter

## ✅ Phase 1 - Sécurité (TERMINÉE)
- [x] Relation User-Booking
- [x] Voter de sécurité
- [x] Filtrage par utilisateur

---

## 🔄 Phase 2 - Fonctionnalités Core

### 2.1 Pagination ⏳ EN COURS
- [ ] Pagination pour Events
- [ ] Pagination pour Bookings
- [ ] Pagination pour Payments
- [ ] Pagination pour Users (admin)

### 2.2 Filtres Avancés
- [ ] Filtres par date (début, fin)
- [ ] Filtres par prix (min, max)
- [ ] Filtres par statut
- [ ] Filtres combinés

### 2.3 Notifications Email
- [ ] Service EmailService
- [ ] Template email confirmation réservation
- [ ] Template email rappel événement
- [ ] Template email annulation
- [ ] Template email paiement réussi

### 2.4 Profil Utilisateur
- [ ] Page profil utilisateur
- [ ] Modifier email
- [ ] Changer mot de passe
- [ ] Historique des réservations

### 2.5 Calendrier
- [ ] Vue calendrier mensuelle
- [ ] Navigation mois précédent/suivant
- [ ] Affichage événements sur calendrier
- [ ] Clic sur événement → détails

### 2.6 Recherche Globale
- [ ] Barre de recherche unifiée
- [ ] Recherche dans Events
- [ ] Recherche dans Bookings
- [ ] Résultats groupés

---

## 🎨 Phase 3 - Expérience Utilisateur

### UX.1 Indicateurs de Progression Paiement
- [ ] Barre de progression visuelle
- [ ] Pourcentage payé
- [ ] Statut étape par étape

### UX.2 Prévisualisation Ticket
- [ ] Modal de prévisualisation
- [ ] Aperçu QR code
- [ ] Bouton télécharger après prévisualisation

### UX.3 Favoris/Wishlist
- [ ] Entité Favorite
- [ ] Ajouter aux favoris
- [ ] Page mes favoris
- [ ] Notification si événement favori bientôt

### UX.4 Historique avec Filtres
- [ ] Filtres par période
- [ ] Filtres par statut
- [ ] Filtres par événement
- [ ] Export CSV

### UX.5 Annulation avec Remboursement Auto
- [ ] Logique remboursement automatique
- [ ] Calcul remboursement
- [ ] Notification remboursement

---

## 💼 Phase 4 - Améliorations Métier

### Métier.1 Dates d'Expiration
- [ ] Champ expirationDate dans Event
- [ ] Vérification expiration
- [ ] Masquer événements expirés

### Métier.2 Heure pour Événements
- [ ] Champ timeEvent dans Event
- [ ] Affichage date + heure
- [ ] Filtres par heure

### Métier.3 Codes Promo/Réductions
- [ ] Entité PromoCode
- [ ] Application code promo
- [ ] Calcul réduction
- [ ] Limites d'utilisation

### Métier.4 Limites Réservation
- [ ] Configuration limite par user
- [ ] Vérification limite
- [ ] Message si limite atteinte

### Métier.5 File d'Attente
- [ ] Entité WaitingList
- [ ] Inscription file d'attente
- [ ] Notification si place disponible

### Métier.6 Avis/Commentaires
- [ ] Entité Review
- [ ] Système de notation (1-5 étoiles)
- [ ] Commentaires texte
- [ ] Affichage sur page événement

---

## 📊 Entités à Créer

1. **Favorite** - Favoris utilisateurs
2. **PromoCode** - Codes promotionnels
3. **WaitingList** - File d'attente
4. **Review** - Avis/Commentaires

## 🔧 Services à Créer

1. **EmailService** - Envoi d'emails
2. **PromoCodeService** - Gestion codes promo
3. **NotificationService** - Notifications diverses

## 📁 Controllers à Créer/Modifier

1. **UserProfileController** (NOUVEAU)
2. **FavoriteController** (NOUVEAU)
3. **CalendarController** (NOUVEAU)
4. **ReviewController** (NOUVEAU)

---

## ⏱️ Estimation

**Temps total estimé :** 8-10 heures de développement

**Priorité :**
1. 🔴 Haute : Pagination, Filtres, Email, Profil
2. 🟡 Moyenne : Calendrier, Favoris, Codes Promo
3. 🟢 Basse : File d'attente, Reviews (peut être Phase 2)


