## Installation
First and foremost, it is necessary to register for an account on kvapay. To proceed with the installation and testing of the kvapay WooCommerce payment module, please follow these simple steps:

Sign up for an account on kvapay. You can use our main website at https://kvapay.com/ for production purposes. However, if you wish to conduct testing, we provide a test environment at https://dev.crypay.com/. Please ensure you create an account on the test platform even if you already have one on our main website.

The installation process for our WooCommerce payment module is straightforward. To ensure the kvapay payment gateway functions correctly on your website, follow these two quick steps:

* Set up API credentials on kvapay.

* Install the kvapay payment module for WooCommerce.

For testing purposes, it is important to generate separate API credentials on https://dev.crypay.com since the API credentials generated on https://kvapay.com will not function in the test environment. To create a set of API credentials, log in to your kvapay account. You can either complete the auto-setup wizard or access the API tab from the menu. Click on "Projects" and then click "+Add project."

# How to Install Kvapay WooCommerce Plugin from GitHub Releases

## 📥 Download the Plugin
1. Go to the [Kvapay WooCommerce Plugin Releases](https://github.com/kvapay/woocommerce-plugin/releases/) page.
2. Find the latest version (usually at the top of the list).
3. Download the `.zip` file (e.g., `kvapay-woocommerce.zip`).

## ⚙️ Install via WordPress Admin Panel
1. Log in to your WordPress admin panel.
2. Navigate to **Plugins > Add New**.
3. Click **Upload Plugin** and select the downloaded `.zip` file.
4. Click **Install Now**, then **Activate** once the installation is complete.

## 🔧 Configure the Plugin
1. Go to **WooCommerce > Settings > Payments**.
2. Find **Kvapay – Cryptocurrencies via KvaPay**, enable it, and click on its settings.
3. Enter your **API credentials**.
4. Set the **Receive Currency** parameter to your preferred payout currency.
5. (Optional) Configure how Kvapay order statuses align with WooCommerce order statuses.
6. If using **Test API credentials**, enable **Test Mode**.
7. Click **Save changes**.

✅ Now your Kvapay payment gateway is ready to use! 🚀

Now you can accept cryptocurrency payments on your WooCommerce store using kvapay as the payment gateway. If you have any further questions or need assistance, feel free to contact our support team
