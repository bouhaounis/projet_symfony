# 🔒 Phase 1 - Sécurité : Instructions de Migration

## ✅ Ce qui a été fait

### 1. **Relation User dans Booking Entity**
- ✅ Ajout de la propriété `user` (ManyToOne) dans `Booking`
- ✅ Ajout de la relation inverse `bookings` (OneToMany) dans `User`
- ✅ Ajout des getters/setters nécessaires

### 2. **Migration MySQL**
- ✅ Migration créée : `migrations/Version20251215140000.php`
- ✅ Script SQL alternatif : `migration_user_id.sql`

### 3. **Sécurité des Controllers**
- ✅ `BookingController::reservations()` - Filtre par utilisateur
- ✅ `BookingController::cart()` - Filtre par utilisateur
- ✅ `BookingController::new()` - Associe le user à la réservation
- ✅ Voter créé : `BookingVoter` pour sécuriser l'accès (VIEW, EDIT, DELETE, CANCEL)

### 4. **Repository**
- ✅ Méthode `findByUser(User $user)` ajoutée
- ✅ Méthode `findActiveByUser(User $user)` ajoutée

### 5. **Amélioration Payment**
- ✅ Remplacement de `rand()` par hash SHA-256 déterministe
- ✅ Simulation plus réaliste (95% succès paiement, 90% succès remboursement)

---

## 📋 Instructions d'Exécution

### Option 1 : Via Symfony Migrations (Recommandé)

```bash
cd c:\dev\symfony_login
php bin/console doctrine:migrations:migrate
```

### Option 2 : Via SQL Direct (Si migration échoue)

1. Ouvrez phpMyAdmin (XAMPP)
2. Sélectionnez votre base de données
3. Allez dans l'onglet SQL
4. Exécutez le contenu du fichier `migration_user_id.sql`

---

## ⚠️ IMPORTANT : Mise à jour des données existantes

**Si vous avez déjà des bookings dans votre base de données**, vous devez les associer à un utilisateur avant d'exécuter la migration.

### Script SQL pour associer les bookings existants :

```sql
-- Afficher les bookings sans user (à exécuter AVANT la migration)
SELECT * FROM booking WHERE user_id IS NULL;

-- Si vous voulez associer les bookings existants au premier utilisateur :
-- ATTENTION : À adapter selon vos besoins !
UPDATE booking 
SET user_id = (SELECT id FROM user LIMIT 1) 
WHERE user_id IS NULL;
```

### Alternative : Migration sans contrainte NOT NULL

Si vous avez des bookings existants et voulez les migrer progressivement, modifiez la migration pour permettre NULL temporairement :

```sql
ALTER TABLE `booking` ADD `user_id` INT DEFAULT NULL AFTER `created_at`;
ALTER TABLE `booking` ADD INDEX `IDX_BOOKING_USER` (`user_id`);
ALTER TABLE `booking` ADD CONSTRAINT `FK_BOOKING_USER` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

-- Ensuite, associez les bookings existants :
UPDATE booking SET user_id = (SELECT id FROM user LIMIT 1) WHERE user_id IS NULL;

-- Puis, rendez la colonne obligatoire :
ALTER TABLE `booking` MODIFY `user_id` INT NOT NULL;
```

---

## 🧪 Vérification

Après la migration, vérifiez que tout fonctionne :

1. **Vérifier la structure de la table :**
```sql
DESCRIBE booking;
-- Vous devriez voir la colonne user_id
```

2. **Tester dans l'application :**
   - Créer un nouveau booking → Vérifier qu'il est associé à votre user
   - Voir "Mes Réservations" → Ne voir que vos bookings
   - Essayer d'accéder à `/booking/{id}` d'un autre user → Devrait être bloqué

3. **Vérifier les logs :**
```bash
php bin/console cache:clear
```

---

## 📝 Notes Techniques

- La migration utilise `ON DELETE CASCADE` : si un user est supprimé, ses bookings le sont aussi
- Le Voter permet aux admins d'accéder à tous les bookings
- Les méthodes du repository optimisent les requêtes avec `createQueryBuilder`

---

## 🔄 Rollback (si nécessaire)

Pour annuler la migration :

```bash
php bin/console doctrine:migrations:migrate prev
```

Ou manuellement :

```sql
ALTER TABLE `booking` DROP FOREIGN KEY `FK_BOOKING_USER`;
ALTER TABLE `booking` DROP INDEX `IDX_BOOKING_USER`;
ALTER TABLE `booking` DROP COLUMN `user_id`;
```

