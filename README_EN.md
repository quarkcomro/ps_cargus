## UrgentCargus PrestaShop module installation manual

### Subscribe to API

- Access https://urgentcargus.portal.azure-api.net/
- Click the 'Sign up' button and fill in the form (you can not use the credentials that the client has for WebExpress)
- Confirm your registration by clicking on the link you received by mail (a real email address should be used)
- On the https://urgentcargus.portal.azure-api.net/developer page, click on `PRODUCTS` in the menu, then`
   UrgentOnlineAPI` and click 'Subscribe', then 'Confirm'
- After the UrgentCargus team confirms subscription to the API, the customer receives a confirmation email
- On the https://urgentcargus.portal.azure-api.net/developer page, click on the user name at the top right, then click
   `Profile '
- The two subscription keys are masked by the characters `xxx ... xxx` and 'Show` in the right of each for display
- It is recommended to use `Primary key` in the UrgentCargus module

### Installing the Module
- The `cargus` folder is copied to the server where the Prestashop platform is installed, in the` modules` folder
- In Prestashop admin you can access `Modules / Module Catalog`
- Search for the `Cargus` module and then press the `Install` button
- After installation access the module configuration page by pressing the `configure` button, fill in the form and press the `Save` button

### Configuring the Module

- API Url: https://urgentcargus.azure-api.net/api
- Subscription Key: Primary key obtained in step A. Subscription to API
- Username: username of the client account in the WebExpress platform
- Password: The password for the account mentioned above
- Go to the `Shipping` page, then` Carriers` and press `Edit` next to the` Cargus` delivery method.
- In step `2. Shipping locations and costs`, in the field `Tax` is chosen the class of taxes related to VAT in Romania, usually the same as for products

### Setting Preferences in app

- Access the tab from the menu `Cargus`, then` Preferences` and fill in the form, then press the orange button `Save preferences` at the bottom of the page
- Pickup Location: one of the available lifting points is chosen. If there is no lift point available, one of the UrgentOnline / WebExpress must be added
- Insurance: choose whether the delivery is with or without insurance
- Saturday Delivery: Choose whether delivery is allowed on Saturdays
- Morning Delivery: Choose if the morning delivery service is used
- Open Package: Choose whether the package opening service is used
- Repayment: Choose the type of repayment - Cash or Bank (Collector Account)
- Payer: Choose the cost of delivery - Sender or Recipinet (Consignee)
- Default shipment type: choose the usual expedition type - Parcel or Envelope (Envelope)
- Free shipping limit: enter the limit for which larger purchases benefit of free shipping (payment of the shipment is
    done automatically to the sender)
- Pickup from Urgent Cargus: it is possible to choose whether the package can be picked up by the recipient of the
    nearest Urgent Cargus warehouse