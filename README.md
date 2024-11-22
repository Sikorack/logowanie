Aplikacja służąca do logowania, która zawiera bezpieczną walidację hasła oraz uwierzytelnianie dwuetapowe za pomocą adresu e-mail za pomocą Symfony Mailera i za pomocą klucza sprzętowego YubiKeya za pomocą WebAuthn.
# Instrukcje do odpalenia aplikacji
### Jeśli serwer posiada domenę i HTTPS lub aplikacja działa na localhost:
1. Zaimportuj bazę danych z pliku `logowanie/db/logowanie.sql`.
2. W katalogu `logowanie/db` zmodyfikuj plik konfiguracyjny:
   - Zmień wartość `rpId` na nazwę domeny (dla localhost wpisz `localhost`).
   - Ustaw dane dostępowe do bazy danych.

### Jeśli serwer nie posiada domeny ani HTTPS:
1. **Zmiana pliku hosts:**
   - Otwórz plik `hosts`:
     - Windows: `C:\Windows\System32\drivers\etc\`
     - Linux/FreeBSD: `/etc/hosts`
   - Na końcu pliku dodaj linię z adresem IP serwera i nazwą domeny, np.:
     ```plaintext
     10.15.0.77    logowanie.pl
     ```
   - Zrestartuj komputer.

2. **Konfiguracja Firefoksa:**
   - Otwórz przeglądarkę i wpisz w pasku adresu `about:config`.
   - Znajdź lub utwórz klucz `dom.securecontext.allowlist` i dodaj do niego domenę skonfigurowaną w pliku `hosts`.

3. Zaimportuj bazę danych z pliku `logowanie/db/logowanie.sql`.

4. W katalogu `logowanie/db` zmodyfikuj plik konfiguracyjny:
   - Zmień `rpId` na nazwę domeny z pliku `hosts`.
   - Ustaw dane dostępowe do bazy danych.
   - Ustaw wartość `secure` na `false`.

# Dokumentacja
## Ekran rejestracji
- Wymagane podstawowe dane do wypełnienia. Ważne dane są hashowane.
- Po wysłaniu formularza aplikacja wysyła e-mail z kodem weryfikacyjnym.
- Jeśli e-mail nie dotarł, użytkownik może wysłać go ponownie (limit czasowy dla ponownego wysyłania).
- Po poprawnej weryfikacji użytkownik zostaje przeniesiony do ekranu logowania.

## Ekran logowania
- Funkcja resetu hasła.
- Opcja przejścia do rejestracji.
- Przycisk logowania.
- Weryfikacja dwuetapowa (możliwość wyłączenia weryfikacji za pomocą maila).

## Panel ustawień konta
- Opcja usunięcia konta (usunięcie wszystkich danych użytkownika).
- Opcja zmiany hasła za pomocą wpisania starego hasła oraz nowego.

## Panel ustawień prywatności
- Możliwość włączenia weryfikacji dwuetapowej.
- Opcja "zapamiętaj urządzenie" z Fingerprint JS.
- Dodanie, zmiana nazwy lub usunięcie wielu kluczy Yubikey.

