## Manual instalare modul Cargus Prestashop

### Abonarea la API

- Se acceseaza https://urgentcargus.portal.azure-api.net/
- Se apasa butonul `Sign up` si se completeaza formularul (nu se pot folosi credentialele pe care clientul le are pentru UrgentOnline / WebExpress)
- Se confirma inregistrarea prin click pe link-ul primit pe mail (trebuie folosita o adresa de email reala)
- In pagina https://urgentcargus.portal.azure-api.net/developer se da click pe `PRODUCTS` in menu, apoi pe `UrgentOnlineAPI` si se apasa `Subscribe`, apoi `Confirm`
- Dupa ce echipa UrgentCargus confirma subscriptia la API, clientul primeste un email de confirmare
- In pagina https://urgentcargus.portal.azure-api.net/developer se da click pe numele utilizatorului din partea dreapta-sus, apoi se apasa `Profile`
- Cele doua subscription keys sunt mascate de caracterele `xxx...xxx` si se apasa `Show` in dreptul fiecareia pentru afisare
- Se recomanda utilizarea `Primary key` in modulul UrgentCargus

### Instalarea modulului

- Folderul `cargus` se copiaza pe serverul unde este instalata platforma Prestashop, in folderul `modules`
- In admin Prestashop se acceseaza `Modules / Module Catalog`
- Se cauta modulul `Cargus` si se apasa butonul `Install`
- Dupa instalare se acceseaza pagina de configurere prin apasarea butonului `configure` si se completeaza formularul apoi se apasa butonul `Salveaza`

### Configurarea modulului

- API Url: https://urgentcargus.azure-api.net/api
- Subscription Key: Primary key obtinuta in pasul A. Abonarea la API
- Username: numele de utilizator al contului clientului in platforma UrgentOnline / WebExpress
- Password: parola aferenta contului mentionat mai sus
- Se acceseaza pagina `Shipping`, apoi `Carriers` si se apasa `Edit` in dreptul metodei de livrare `Cargus`
- In pasul `2. Shipping locations and costs`, in campul `Tax` se alege clasa de taxe aferenta TVA-ului din Romania, de obicei aceeasi ca la produse

### Setarea preferintelor in modul

- Se acceseaza pagina `Cargus / Preferinte` si se completeaza formularul, dupa care se apasa butonul `Salveaza` din partea dreapta-jos a paginii
- Punctul de ridicare: se alege unul din punctele de ridicare dispnibile. Daca nu exista niciun punct de ridicare disponibil, trebuie adaugat unul din UrgentOnline / WebExpress
- Asigurare expeditie: se alege daca livrarea se face cu asigurare sau fara
- Livrare sambata: se alege daca este permisa livrarea in zilele de sambata
- Livrare dimineata: se alge daca este utilizat serviciul livrare matinala
- Deschidere colet: se alege daca este utilizat serviciul deschidere colet
- Tip ramburs: se alege tipul rambursului – Numerar sau Cont colector
- Platitor expeditie: se alege platitorul costului de livrare – Expeditorul sau Destinatarul
- Tip expeditie: se alege tipul de expeditie uzuala – Colet sau Plic
- Limita transport gratuit: se introduce limita pentru care cumparaturile mai mari de beneficiaza de transport gratuit (plata transportului se face automat la expeditor)
- Cost fix transport: se alege un cost fix de livrare sau se lasa necompletat, pentru ca modulul sa calculeze automat tariful