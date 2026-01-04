# Configuration du système d'emails

## Configuration

### Variables d'environnement (.env)

Ajoutez ces variables dans votre fichier `.env` :

```env
# Configuration Mailer DSN (exemple avec Gmail)
MAILER_DSN=smtp://votre-email@gmail.com:votre-mot-de-passe@smtp.gmail.com:587

# Email de l'expéditeur (optionnel)
MAILER_FROM_EMAIL=noreply@votre-domaine.com
MAILER_FROM_NAME=Event Booking System
```

### Options de configuration Mailer DSN

#### 1. Gmail
```env
MAILER_DSN=smtp://votre-email@gmail.com:votre-mot-de-passe@smtp.gmail.com:587
```

#### 2. SMTP personnalisé
```env
MAILER_DSN=smtp://username:password@smtp.example.com:587
```

#### 3. Pour le développement (null transport - emails non envoyés mais loggés)
```env
MAILER_DSN=null://null
```

## Emails disponibles

Le système envoie automatiquement les emails suivants :

### 1. **Confirmation de réservation**
- **Quand** : Lors de la création d'une nouvelle réservation
- **Template** : `templates/emails/booking_confirmation.html.twig`

### 2. **Confirmation de paiement**
- **Quand** : Après chaque paiement effectué
- **Template** : `templates/emails/payment_confirmation.html.twig`

### 3. **Annulation de réservation**
- **Quand** : Lors de l'annulation d'une réservation
- **Template** : `templates/emails/booking_cancellation.html.twig`

### 4. **Rappel d'événement**
- **Quand** : 24h avant l'événement (via commande)
- **Template** : `templates/emails/event_reminder.html.twig`

### 5. **Rappel de paiement**
- **Quand** : Pour les réservations avec solde restant (via commande)
- **Template** : `templates/emails/payment_reminder.html.twig`

### 6. **Email de bienvenue**
- **Quand** : Lors de l'inscription d'un nouvel utilisateur
- **Template** : `templates/emails/welcome.html.twig`

## Commandes disponibles

### Envoyer les rappels d'événements
```bash
php bin/console app:send-event-reminders
```
Cette commande envoie des emails de rappel 24h avant chaque événement.

### Envoyer les rappels de paiement
```bash
php bin/console app:send-payment-reminders
```
Cette commande envoie des emails de rappel pour les réservations avec solde restant.

## Planification automatique (Cron)

Pour automatiser l'envoi des rappels, ajoutez ces lignes à votre crontab :

```bash
# Envoyer les rappels d'événements tous les jours à 9h
0 9 * * * cd /chemin/vers/votre/projet && php bin/console app:send-event-reminders

# Envoyer les rappels de paiement tous les jours à 10h
0 10 * * * cd /chemin/vers/votre/projet && php bin/console app:send-payment-reminders
```

## Personnalisation

### Modifier les templates d'emails

Tous les templates se trouvent dans `templates/emails/`. Le template de base est `base_email.html.twig`.

### Modifier le service

Le service `EmailNotificationService` dans `src/Service/EmailNotificationService.php` peut être étendu pour ajouter de nouveaux types d'emails.

## Tests

Pour tester l'envoi d'emails sans vraiment les envoyer, utilisez le transport `null` :

```env
MAILER_DSN=null://null
```

Les emails seront loggés mais ne seront pas envoyés.

