# DV Pay API

DV Pay API is a free and open-source cryptocurrency payment processor which allows you to accept cryptocurrency without fees or intermediaries.

<p align="center">
<img src="https://i.ibb.co/1bLT6v6/dv-logo.png" alt="dv-logo" border="0">
</p>

## 🎨 Features

* Direct, peer-to-peer cryptocurrency payments
* No transaction fees (other than the network fee)
* No fees, middleman or KYC
* Non-custodial (complete control over the private key)
* Enhanced privacy & security
* Self-hosted
* Share your instance with friends (multi-tenant)
* Invoice management and Payment requests


## 💵 Support currency
- [x] Bitcoin
- [x] USDT (TRC20)
- [ ] ETH
- [ ] USDT (ERC20)

## ⚙️ Requirements
* СentOS 7 or 8
* PHP ^8.1
* MySQL Percona 8.0+
* Redis
* Nginx

## 👨‍💻 Using Technology

Based on laravel framework with any popular laravel package

# 🚀 How to install project:

Easy to launch - just follow steps below!

## Prepairing for installation

For successful installation you will need 3 domains:

- **Frontend domain.** Domain on which the personal account user interface will be deployed;
- **Backend domain.** Domain where the backend will be located;
- **Payment domain.** The domain where the payment form for your clients will be located;
- **Processing URL** contact [DV Support](https://t.me/dv_pay_support) to get it.

> For example, if your main site is on the `mybestshop.com` domain, then you can create the following additional domains:
>- `app.mybestshop.com` (frontend)
>- `api.mybestshop.com` (backend)
>- `pay.mybestshop.com` (payment)

Prepare a virtual machine with CentOS. All necessary update packages will be installed automatically during script execution.

## Installation process

### Step 1. Launch

Login to your virtual machine as a **root user** and launch the script below:

```shell
bash <(curl -Ls https://raw.githubusercontent.com/dvpay/dv-backend/master/init.sh)
```
System will ask you to enter domains, follow next step.

### Step 2. Input domains

Enter your domains, which you got during [preparation](#prepairing-for-installation)

<p align="center">
<img src="https://i.ibb.co/pLXL2qk/Domains.jpg" alt="Domains border="0">
</p>

After entering processing URL script will continue installation. In the end of script you will be provided with **DV Credentials - copy it to a safe place.**

DV backend is now successfully installed! Now you are ready to launch DV admin app - use your domain and credentials you were provided earlier to log in.

Feel free to contact us in [DV Support](https://t.me/dv_pay_support)!
