## [5.1.1] - 13-03-2024
### Description
- Addressed issue where price displayed as 'FREE' despite fixed price being set.
- The problem was that client modified carrier settings. Whenever a change is made to the carriers, hookActionCarrierUpdate is used to update internal ID linking to Cargus. There was a missing value for `CARGUS_CARRIER_ID` which outdated the Cargus Carrier.
### Fix
- Add inside `hookActionCarrierUpdate` check for `CARGUS_CARRIER_ID`.