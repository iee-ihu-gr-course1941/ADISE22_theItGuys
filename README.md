# Σύντομη περιγραφή του project

## Σύνδεση χρήστη

Στο project αυτό υλοποιήσαμε το παιχνίδι της μπλόφας.
Αρχικά ο χρήστης ο οποίος θα μπει για πρώτη φορά στην σελίδα του παιχνιδιού του εμφανίζεται ένα modal όπου θα πρέπει να δηλώσει ένα όνομα της επιλογής του και να πατήσει submit. Αφού πατήσει submit αν δεν έχει προκληθεί κάποιο error (επειδή έχει αφήσει κενό το input) του εμφανίζεται ένα μήνυμα επάνω αριστερά και συνδέεται στην σελίδα ενώ ταυτόχρονα αποθηκεύεται και στην βάση δεδομένων το όνομα του, η ώρα σύνδεσης του και το token του. Απο την στιγμή που ο χρήστης είναι συνδεδεμένος μπορεί να πλοηγηθεί στην αρχική σελίδα όπου του εμφανίζονται μόνο τα διαθέσιμα δωμάτια και να συνδεθεί σε όποιο δωμάτιο επιθυμεί.

## Είσοδος σε δωμάτιο

Αρχικά στο κάθε δωμάτιο πρέπει να συνδεθούν 4 παίχτες προκειμένου να αρχίσει το παιχνίδι.
Με το που συνδεθεί ο χρήστης στο δωμάτιο που επιθυμεί καλείται μια function η οποία κάνει μια κλήση στο API και μας επιστρέφει το όνομα του δωματίου, το status του και τον αριθμό των χρηστών που είναι αυτήν την στιγμή συνδεδεμένοι σε αυτό. Επιπλέον σε περίπτωση που το δωμάτιο δεν είναι πλήρες δημιουργείται ένα Interval που καλείται κάθε 1,5 δευτερόλεπτο και κάνοντας μια κλήση στο API ενημερώνει το status του δωματίου. Αν το status αλλάξει σε "full" το συγκεκριμένο interval διαγράφεται.
Απο τους 4 παίχτες του δωματίου ορίζεται ένας admin του δωματίου (αυτός που έχει συνδεθεί πιο νωρίς απο όλους) απο τον οποίο καλείται η δημιουργία της τράπουλας, το ανακάτεμα και το μοίρασμα αυτόματα.

## Κινήσεις παιχτών

Ο κάθε παίχτης έχει το δικαίωμα να ρίξει φύλλα, να καλέσει την μπλόφα κάποιου άλλου παίχτη και να πάει πάσο.
Ο πρώτος παίχτης που παίζει είναι ο admin του δωματίου ο οποίος πρέπει αναγκαστικά να ρίξει φύλλα και του απενεργοποιούνται τα κουμπιά της μπλόφας και του πάσου. Αν κάποιος χρήστης καλέσει την μπλόφα εμφανίζονται στην οθόνη τα χαρτιά που έριξε ο παίχτης που έπαιξε τελευταία φορά. Αν έχει όντως μπλοφάρει τότε μαζέυει όλα τα χαρτιά που βρίσκονται στην μπάνκα καθώς και αυτά που έριξε. Αν δεν έχει κάνει μπλόφα όλα τα χαρτιά τα παίρνει ο παίχτης που την κάλεσε.

## Λήξη παιχνιδιού

Η παρτίδα λήγει όταν ένας παίχτης παίξει όλα του τα χαρτιά ενώ έχει περάσει ένας γύρος στον οποίο δεν έχει σηκώσει κάποια χαρτιά (π.χ. αν έχει κάνει μπλόφα και κάποιος άλλος παίχτης την καλέσει). Όταν λήξει η παρτίδα εμφανίζεται σε όλους τους παίχτες ένα modal με ένα μήνυμα για τον παίχτη που κέρδισε. Το modal αυτό δεν μπορεί να κλείσει παρα μόνο αν ο παίχτης πατήσει το κουμπί "Return Home" το οποίο τον πηγαίνει στην αρχική σελίδα αδειάζοντας ταυτόχρονα το δωμάτιο και όλες τις πληροφορίες που συνδέονται με το συγκεκριμένο παιχνίδι απο την βάση δεδομένων.

# Περιγραφή API

## Methods

### Basic

```php
POST /bluff/
```

Eμάνιση διαθέσιμων δωματίων στην αρχική σελίδα

```php
POST /bluff/getTotalRooms
```

Επιστρέφει των αριθμό των διαθέσιμων δωματίων στην αρχική σελίδα

### Game

```php
GET /game/ + numeric
```

Αφού ελέγξει το status του δωματίου ενημερώνει τα δωμάτια, τις πληροφορίες του χρήστη στην ΒΔ και προσθέτει ένα cookie που κρατάει πληροφορία για το δωμάτιο

```php
POST /game/getInfo
```

Φέρνει τις πληροφορίες του δωματίου

```php
/game/getGameStatus
```

Φέρνει τo status του δωματίου

```php
POST /game/getRoomPlayers
```

Φέρνει τους παίχτες που είναι συνδεδεμένοι στο δωμάτιο

```php
GET /game/start
```

Ξεκινάει το παιχνίδι και το αποθηκέυει στην ΒΔ

```php
GET /game/getGameOwner
```

Επιστρέφει τον ιδιοκτήτη του δωματίου

```php
GET /game/getGameInfo
```

Επιστρέφει τις πληροφρίες του παιχνιδιού και ελέγχει αν υπάρχει νικητής

```php
GET /game/getMyCards
```

Επιστρέφει τα χαρτιά του παίκτη

```php
POST /game/playYourBluff
```

Ρίξιμο χαρτιών

```php
GET /game/callBluff
```

Ελέγχει αν ο προηγούμενος παίκτης έκανε μπλόφα

```php
POST /game/getCalledBluffCards
```

Δίνει στον παίκτη τα χαρτιά που πρέπει να πάρει μετά την κλήση της μπλόφας

```php
GET /game/passOnBluff
```

Κίνηση πάσο

```php
POST /game/resetPasses
```

Μηδενίζει τα συνολικά πάσου του γύρου

```php
POST /game/addCardsToBank
```

Προσθέτει τα χαρτιά που παίχτηκαν στην μπάνκα

```php
POST /game/getWinner
```

Επιστρέφει τον νικητή της παρτίδας

```php
POST /game/restoreRoom
```

Αδειάζει το δωμάτιο, διαγράφει την τράπουλα, το status του παιχνιδιού και ενημέρωνει τις πληροφορίες του χρήστη

### Users

```php
POST /players/
```

Αποθηκεύει τον χρήστη

# Βάση Δεδομένων

## users

Κρατάει πληροφορίες για τους χρήστες

| Attribute   | Description                                                  |
| ----------- | ------------------------------------------------------------ |
| id          |                                                              |
| name        | Το όνομα που έχει επιλέξει ο χρήστης                         |
| room_id     | Συμπληρώνεται αυτόματα όταν ο χρήστης μπει σε κάποιο δωμάτιο |
| Log_in_time | Η ώρα ππου συνδέθηκε ο χρήστης                               |
| token       | Το token του                                                 |

## rooms

Κρατάει πληροφορίες για τα δωμάτια

| Attribute    | Description                                                           |
| ------------ | --------------------------------------------------------------------- |
| id           |                                                                       |
| name         | Το όνομα του δωματίου                                                 |
| users_online | Το σύνολο των χρηστών που είναι συνδεδεμένοι στο δωμάτιο              |
| status       | Το status του δωματίου                                                |
| owner_id     | Ο ιδιοκτήτης του δωματίου(αυτόματα αυτός που έχει συνδεθεί πιο νωρίς) |

## game_status

Κρατάει πληροφορίες για τις ενεργές παρτίδες

| Attribute             | Description                                                               |
| --------------------- | ------------------------------------------------------------------------- |
| id                    |                                                                           |
| player_turn_id        | Το id του παίκτη που είναι να παίξει                                      |
| first_winner_id       | Ο πρώτος νικητής                                                          |
| second_winner_id      | Ο δεύτερος νικητής                                                        |
| last_change           | Η τελευταία αλλαγή στον πίνακα                                            |
| room_id               | Το id του δωματίου                                                        |
| num_of_cards_played   | Το σύνολο των χαρτιών που εχει ρίξει ο τελευταίος παίκτης στην μπλόφα του |
| value_of_cards_played | Ο χαρακτήρας που δήλωσε ότι είναι τα χαρτιά                               |
| played_by             | Το id του παίχτη που έριξε τα τελευταία φύλλα                             |
| passes                | Τα πάσο του κάθε γύρου                                                    |
| game_ended            | Κρατάει αν έχει λήξει το παιχνίδι                                         |

## bluff

Κρατάει τις τράπουλες των ενεργών παιχνιδιών

| Attribute         | Description                                                       |
| ----------------- | ----------------------------------------------------------------- |
| id                |                                                                   |
| card_number       | Ο χαρακτήρας του χαρτιού                                          |
| card_style        | Το σύμβολο του χαρτιού                                            |
| actions           | Αν το χαρτί έχει παιχτεί, είναι στην μπανκα ή στο χέρι του παίκτη |
| actions_timestamp | timestamp                                                         |
| user_id           | Το id του χρήστη που έχει το χαρτί                                |
| room_id           | το id του δωματίου στο οποίο χρησιμοποιείται η τράπουλα           |

# Links

[Uploaded Porject](https://users.it.teithe.gr/~it185416)
