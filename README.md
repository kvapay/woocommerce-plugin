## Installation
First and foremost, it is necessary to register for an account on kvapay. To proceed with the installation and testing of the kvapay WooCommerce payment module, please follow these simple steps:

Sign up for an account on kvapay. You can use our main website at https://kvapay.com/ for production purposes. However, if you wish to conduct testing, we provide a test environment at https://dev.kvapay.com/. Please ensure you create an account on the test platform even if you already have one on our main website.

The installation process for our WooCommerce payment module is straightforward. To ensure the kvapay payment gateway functions correctly on your website, follow these two quick steps:

* Set up API credentials on kvapay.

* Install the kvapay payment module for WooCommerce.

For testing purposes, it is important to generate separate API credentials on https://dev.kvapay.com since the API credentials generated on https://kvapay.com will not function in the test environment. To create a set of API credentials, log in to your kvapay account. You can either complete the auto-setup wizard or access the API tab from the menu. Click on "Projects" and then click "+Add project."

1. Log in to your WordPress admin panel and navigate to Plugins > Add New.

2. In the "Search Plugins" field, type "kvapay". Once the Kvapay for WooCommerce plugin is displayed, click on "Install Now" (if prompted, enter your FTP credentials).

3. After the plugin is installed, click on "Activate".

4. Go to WooCommerce > Settings > Payments > Method: "Kvapay – Cryptocurrencies via KvaPay" and check the "Enabled" box. Then click on "KvaPay" on the same page. If desired, you can modify the Description and Title according to your preferences.

5. Enter your API credentials on the WooCommerce configuration page.

6. Set the Receive Currency parameter to the currency in which you prefer to receive payouts from kvapay. Additionally, you can configure how kvapay order statuses align with WooCommerce order statuses. If you're unsure, it is recommended to leave the default options unchanged.

7. If you are using Test API credentials, enable the Test Mode.

8. Finally, click on "Save changes", and you're ready to go!

Now you can accept cryptocurrency payments on your WooCommerce store using kvapay as the payment gateway. If you have any further questions or need assistance, feel free to contact our support team
