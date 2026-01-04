# 🔴 URGENT : Correction de l'erreur user_id

## Erreur actuelle
```
Column not found: 1054 Unknown column 't0.user_id' in 'field list'
```

## Cause
La colonne `user_id` n'existe pas encore dans votre table `booking`. Doctrine essaie de l'utiliser car nous avons ajouté la relation dans le code, mais la migration n'a pas été exécutée.

## ✅ Solution Rapide (XAMPP/MySQL)

### Option 1 : Via phpMyAdmin (RECOMMANDÉ)

1. **Ouvrez phpMyAdmin** (http://localhost/phpmyadmin)
2. **Sélectionnez votre base de données** (celle utilisée par Symfony)
3. **Cliquez sur l'onglet "SQL"**
4. **Copiez-collez ce script** :

```sql
-- Étape 1 : Ajouter la colonne user_id
ALTER TABLE `booking` ADD `user_id` INT DEFAULT NULL AFTER `created_at`;

-- Étape 2 : Associer les bookings existants au premier utilisateur (si vous avez des données)
UPDATE `booking` SET `user_id` = (SELECT id FROM `user` LIMIT 1) WHERE `user_id` IS NULL;

-- Étape 3 : Rendre la colonne obligatoire
ALTER TABLE `booking` MODIFY `user_id` INT NOT NULL;

-- Étape 4 : Ajouter l'index
ALTER TABLE `booking` ADD INDEX `IDX_BOOKING_USER` (`user_id`);

-- Étape 5 : Ajouter la clé étrangère
ALTER TABLE `booking` ADD CONSTRAINT `FK_BOOKING_USER` 
    FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;
```

5. **Cliquez sur "Exécuter"**

### Option 2 : Via ligne de commande MySQL

```bash
mysql -u root -p votre_base_de_donnees < QUICK_FIX_USER_ID.sql
```

### Option 3 : Via Symfony Migrations

```bash
cd c:\dev\symfony_login
php bin/console doctrine:migrations:sync-metadata-storage
php bin/console doctrine:migrations:migrate
```

---

## ⚠️ Si vous avez des bookings existants

Si vous avez déjà des réservations dans votre base de données, l'étape 2 du script les associera au premier utilisateur de votre table `user`.

**Pour vérifier vos bookings existants :**
```sql
SELECT * FROM booking;
```

**Pour voir quel user sera utilisé :**
```sql
SELECT id, email FROM user LIMIT 1;
```

---

## ✅ Après l'exécution

1. **Videz le cache Symfony :**
```bash
php bin/console cache:clear
```

2. **Vérifiez que ça fonctionne :**
```sql
DESCRIBE booking;
-- Vous devriez voir user_id dans la liste
```

3. **Testez l'application :**
   - Allez sur la page des événements
   - L'erreur devrait disparaître

---

## 📝 Fichier SQL disponible

Un fichier `QUICK_FIX_USER_ID.sql` est disponible dans votre projet avec le script complet.

