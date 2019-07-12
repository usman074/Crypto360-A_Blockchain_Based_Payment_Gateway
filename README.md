# Crypto360-A_Blockchain_Based_Payment_Gateway
This application process the bitcoin payments for e-commerce websites.

## Overview:
This Application provides a web interface for E-merchants to create an account. After the account creation E-merchant has to
provide the xpubkey (extensible public key) and also generate an Api key.  
Now the E-merchant can use the services in 2 ways either use a woocomerce plugin or use api end points.  
In case of woocomerce plugin, After the plugin is installed and activated E-merchant has to place the merchant_id and
Api key in plugin settings.  
If E-merchant uses Api end points the merchant has to make a POST request with 7 parameters i.e  
invoiceId => Id of an invoice  
amount => Amount of order in fiat currency  
merchantId => E-merchant Id provided by crypto360  
currency => currency code of amount provided in fiat  
api_key => Api key generated by E-merchant to verify request  
user_email => Email of buyer so we can send payment confirmation email after payment completion  
callback_url => A url where we send the payment confirmation to E-merchant  
