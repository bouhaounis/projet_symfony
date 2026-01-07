# 🎯 Récapitulatif Complet des Fonctionnalités

## ✅ Phase 1 - Sécurité (TERMINÉE)

---

## 🔄 Phase 2 & 3 - Fonctionnalités à Implémenter

### 📦 Nouvelles Entités Nécessaires

1. **Favorite** - Favoris utilisateurs
   - `user_id` (ManyToOne User)
   - `event_id` (ManyToOne Event)
   - `createdAt` (DateTime)

2. **PromoCode** - Codes promotionnels
   - `code` (string, unique)
   - `discount` (float, pourcentage)
   - `maxUses` (int)
   - `currentUses` (int)
   - `validFrom` (DateTime)
   - `validUntil` (DateTime)
   - `isActive` (boolean)

3. **WaitingList** - File d'attente
   - `user_id` (ManyToOne User)
   - `event_id` (ManyToOne Event)
   - `createdAt` (DateTime)
   - `notified` (boolean)

4. **Review** - Avis/Commentaires
   - `user_id` (ManyToOne User)
   - `event_id` (ManyToOne Event)
   - `rating` (int, 1-5)
   - `comment` (text)
   - `createdAt` (DateTime)

### 🔧 Modifications Entités Existantes

**Event.php :**
- Ajouter `timeEvent` (Time)
- Ajouter `expirationDate` (DateTime, nullable)
- Ajouter relation `favorites` (OneToMany Favorite)
- Ajouter relation `reviews` (OneToMany Review)
- Ajouter relation `waitingList` (OneToMany WaitingList)

**Booking.php :**
- Ajouter `promoCode` (ManyToOne PromoCode, nullable)
- Ajouter `discountAmount` (float, nullable)

**User.php :**
- Ajouter relation `favorites` (OneToMany Favorite)
- Ajouter relation `reviews` (OneToMany Review)
- Ajouter relation `waitingList` (OneToMany WaitingList)
- Ajouter `maxBookingsPerEvent` (int, default: null)

### 📁 Nouveaux Services

1. **EmailService** - Envoi d'emails
   - `sendBookingConfirmation()`
   - `sendEventReminder()`
   - `sendCancellationNotice()`
   - `sendPaymentConfirmation()`
   - `sendWaitingListNotification()`

2. **PromoCodeService** - Gestion codes promo
   - `validateCode()`
   - `applyCode()`
   - `calculateDiscount()`

3. **NotificationService** - Notifications
   - `notifyEventReminder()`
   - `notifyWaitingListAvailable()`

### 🎮 Nouveaux Controllers

1. **UserProfileController**
   - `/profile` - Voir profil
   - `/profile/edit` - Modifier profil
   - `/profile/change-password` - Changer mot de passe

2. **FavoriteController**
   - `/favorites` - Liste favoris
   - `/favorite/add/{id}` - Ajouter favori
   - `/favorite/remove/{id}` - Retirer favori

3. **CalendarController**
   - `/calendar` - Vue calendrier
   - `/calendar/month/{year}/{month}` - Mois spécifique

4. **ReviewController**
   - `/review/add/{eventId}` - Ajouter avis
   - `/review/edit/{id}` - Modifier avis
   - `/review/delete/{id}` - Supprimer avis

5. **WaitingListController**
   - `/waiting-list/{eventId}/add` - S'inscrire
   - `/waiting-list/remove/{id}` - Se retirer

### 📝 Formulaires à Créer

1. **UserProfileFormType**
2. **ChangePasswordFormType**
3. **ReviewFormType**
4. **PromoCodeFormType**

---

## 🚀 Ordre d'Implémentation Recommandé

### Sprint 1 : Base & Pagination (2-3h)
1. ✅ Configurer pagination
2. Pagination Events
3. Pagination Bookings
4. Pagination Payments

### Sprint 2 : Email & Profil (2-3h)
5. EmailService
6. Templates emails
7. Profil utilisateur
8. Changer mot de passe

### Sprint 3 : Filtres & Calendrier (2h)
9. Filtres avancés
10. Calendrier événements

### Sprint 4 : UX Features (2-3h)
11. Favoris
12. Indicateurs progression
13. Prévisualisation tickets
14. Historique filtré

### Sprint 5 : Métier Advanced (3-4h)
15. Codes promo
16. Expiration & heure événements
17. Reviews
18. File d'attente
19. Remboursement auto
20. Limites réservation

---

**Temps total estimé : 11-15 heures**


