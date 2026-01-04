# 📚 Guide d'Implémentation Complète - Phases 2 & 3

## 🎯 Vue d'Ensemble

Ce document contient le plan complet pour implémenter toutes les fonctionnalités demandées de manière professionnelle.

---

## ✅ Configuration de Base (FAIT)

- [x] KnpPaginatorBundle installé
- [x] Configuration pagination créée
- [x] Bundle enregistré

---

## 📋 FONCTIONNALITÉS PAR PRIORITÉ

### 🔴 PRIORITÉ HAUTE (À faire en premier)

#### 1. Pagination ⏳ EN COURS
**Fichiers à modifier :**
- `src/Controller/BookingController.php` - Méthodes `index()`, `reservations()`, `cart()`, `events()`
- `src/Controller/EventController.php` - Méthode `index()`
- `src/Controller/PaymentController.php` - Méthode `index()`
- `src/Controller/UserController.php` - Méthode `index()` (admin)
- Templates correspondants

**Code à ajouter :**
```php
use Knp\Component\Pager\PaginatorInterface;

// Dans chaque méthode :
$pagination = $paginator->paginate(
    $query, // Query ou array
    $request->query->getInt('page', 1), // Page number
    12 // Items per page
);
```

#### 2. Filtres Avancés
**Nouveaux paramètres dans les controllers :**
- Date début/fin
- Prix min/max
- Statut (pour bookings)

**Fichiers :**
- Repository : méthodes `findWithFilters()`
- Controllers : traitement des paramètres
- Templates : formulaires de filtres

#### 3. Service Email
**Nouveau service :** `src/Service/EmailService.php`

**Templates emails :**
- `templates/emails/booking_confirmation.html.twig`
- `templates/emails/event_reminder.html.twig`
- `templates/emails/payment_confirmation.html.twig`
- `templates/emails/cancellation.html.twig`

#### 4. Profil Utilisateur
**Nouveau controller :** `src/Controller/UserProfileController.php`
**Nouveaux formulaires :**
- `src/Form/UserProfileFormType.php`
- `src/Form/ChangePasswordFormType.php`

**Routes :**
- `/profile` - Voir profil
- `/profile/edit` - Modifier profil
- `/profile/change-password` - Changer mot de passe

---

### 🟡 PRIORITÉ MOYENNE

#### 5. Calendrier Événements
**Nouveau controller :** `src/Controller/CalendarController.php`
**Template :** Vue calendrier mensuelle avec FullCalendar.js ou calendrier custom

#### 6. Favoris/Wishlist
**Nouvelle entité :** `src/Entity/Favorite.php`
**Repository :** `src/Repository/FavoriteRepository.php`
**Controller :** `src/Controller/FavoriteController.php`

**Migration nécessaire**

#### 7. Codes Promo
**Nouvelle entité :** `src/Entity/PromoCode.php`
**Service :** `src/Service/PromoCodeService.php`
**Form :** `src/Form/PromoCodeFormType.php`
**Migration nécessaire**

---

### 🟢 PRIORITÉ BASSE

#### 8. Reviews/Commentaires
**Nouvelle entité :** `src/Entity/Review.php`
**Controller :** `src/Controller/ReviewController.php`
**Migration nécessaire**

#### 9. File d'Attente
**Nouvelle entité :** `src/Entity/WaitingList.php`
**Controller :** `src/Controller/WaitingListController.php`
**Service :** Notification quand place disponible
**Migration nécessaire**

#### 10. Heure & Expiration Événements
**Modification :** `src/Entity/Event.php`
- Ajouter `timeEvent` (Time)
- Ajouter `expirationDate` (DateTime)
**Migration nécessaire**

---

## 🗂️ Structure de Fichiers

```
src/
├── Controller/
│   ├── UserProfileController.php (NOUVEAU)
│   ├── FavoriteController.php (NOUVEAU)
│   ├── CalendarController.php (NOUVEAU)
│   ├── ReviewController.php (NOUVEAU)
│   ├── WaitingListController.php (NOUVEAU)
│   └── [Controllers existants modifiés]
├── Entity/
│   ├── Favorite.php (NOUVEAU)
│   ├── PromoCode.php (NOUVEAU)
│   ├── Review.php (NOUVEAU)
│   ├── WaitingList.php (NOUVEAU)
│   └── [Entities existantes modifiées]
├── Service/
│   ├── EmailService.php (NOUVEAU)
│   ├── PromoCodeService.php (NOUVEAU)
│   └── NotificationService.php (NOUVEAU)
├── Form/
│   ├── UserProfileFormType.php (NOUVEAU)
│   ├── ChangePasswordFormType.php (NOUVEAU)
│   ├── ReviewFormType.php (NOUVEAU)
│   └── PromoCodeFormType.php (NOUVEAU)
└── Repository/
    ├── FavoriteRepository.php (NOUVEAU)
    ├── PromoCodeRepository.php (NOUVEAU)
    ├── ReviewRepository.php (NOUVEAU)
    └── WaitingListRepository.php (NOUVEAU)

templates/
├── emails/ (NOUVEAU dossier)
│   ├── booking_confirmation.html.twig
│   ├── event_reminder.html.twig
│   ├── payment_confirmation.html.twig
│   └── cancellation.html.twig
├── profile/ (NOUVEAU dossier)
│   ├── index.html.twig
│   └── edit.html.twig
├── calendar/ (NOUVEAU dossier)
│   └── index.html.twig
└── [Autres templates modifiés]
```

---

## 📊 Migrations Nécessaires

1. **Favorite** - user_id, event_id, createdAt
2. **PromoCode** - code, discount, maxUses, dates
3. **Review** - user_id, event_id, rating, comment
4. **WaitingList** - user_id, event_id, notified
5. **Event modifications** - timeEvent, expirationDate
6. **Booking modifications** - promoCode_id, discountAmount
7. **User modifications** - maxBookingsPerEvent

---

## 🚀 Ordre d'Exécution Recommandé

1. ✅ Configurer pagination
2. 🔄 Implémenter pagination (Events, Bookings, Payments)
3. ⏳ Créer EmailService + templates
4. ⏳ Profil utilisateur
5. ⏳ Filtres avancés
6. ⏳ Créer entités (Favorite, PromoCode, Review, WaitingList)
7. ⏳ Calendrier
8. ⏳ Favoris
9. ⏳ Codes promo
10. ⏳ Reviews
11. ⏳ File d'attente
12. ⏳ Modifications Event (heure, expiration)
13. ⏳ Améliorations UX (progression, prévisualisation)
14. ⏳ Remboursement auto
15. ⏳ Limites réservation

---

## ⏱️ Estimation Temps

- **Sprint 1 (Pagination) :** 1-2h
- **Sprint 2 (Email + Profil) :** 2-3h
- **Sprint 3 (Filtres + Calendrier) :** 2-3h
- **Sprint 4 (Entités + Services) :** 3-4h
- **Sprint 5 (UX Features) :** 2-3h
- **Sprint 6 (Métier Advanced) :** 3-4h

**Total : 13-19 heures**

---

Ce document servira de référence pour l'implémentation complète. Chaque fonctionnalité sera implémentée de manière professionnelle avec tests et documentation.

