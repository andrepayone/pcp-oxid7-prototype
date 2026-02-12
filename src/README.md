# PAYONE PCP Checkout Prototype for OXID eShop 7.4+

This module provides a prototype integration of the PAYONE Commerce Platform (PCP) checkout process for OXID eShop 7.4 and newer. It is intended as a starting point and demonstration for a full integration.

## Requirements

*   OXID eShop (Community, Professional, or Enterprise Edition) 7.4.0 or higher
*   PHP 8.2 or higher
*   Composer 2

## Installation from Archive (e.g., .tar.gz)

These instructions assume you have downloaded the module as an archive file (e.g., from a GitHub release).

### 1. Place the Module Files

1.  Download and unpack the module archive.
2.  In the root directory of your OXID eShop installation, navigate to `source/modules/`.
3.  Create the following directory structure: `Payone/PcpPrototype`.
4.  Copy all the unpacked module files into this new directory.

The final structure should look like this:
```
<shop_root>/
├── source/
│   └── modules/
│       └── Payone/
│           └── PcpPrototype/
│               ├── Application/
│               ├── src/
│               ├── composer.json
│               ├── metadata.php
│               └── README.md
└── ...
```

### 2. Register the Module with Composer

1.  Open the main `composer.json` file located in the root directory of your OXID shop.
2.  Add a "path" repository entry to make Composer aware of your local module. If the `repositories` key does not exist, create it.

    ```json
    {
        "name": "oxid-esales/eshop-community-project",
        "type": "project",
        "description": "...",
        "repositories": [
            {
                "type": "path",
                "url": "source/modules/Payone/PcpPrototype"
            }
        ],
        "require": {
            ...
        },
        ...
    }
    ```

### 3. Install Module Dependencies

1.  Open your command line terminal and navigate to the root directory of your OXID shop.
2.  Run the following command to add the module as a dependency to your shop. Composer will create a symbolic link in the `vendor` directory.

    ```bash
    composer require payone-gmbh/pcp-oxid7-prototype
    ```

### 4. Activate the Module

1.  Log in to your OXID eShop Admin panel.
2.  Navigate to **Extensions -> Modules**.
3.  Find the **PAYONE PCP Checkout Prototype** in the module list and click the **Activate** button. This will automatically register the payment method.
4.  After successful activation, clear the shop's cache. The easiest way is to delete the contents of the `/tmp` directory in your shop's root.

### 5. Configure the Module

1.  In the module list, find the activated module and switch to the **Settings** tab.
2.  Enter your PAYONE Commerce Platform credentials. The most important settings are:
    *   `pcpMerchantId`
    *   `pcpApiKey`
    *   `pcpApiSecret`
3.  The `pcpApiEndpoint` defaults to the pre-production environment. Adjust if necessary.

The installation is now complete. The "PAYONE Checkout" payment method should be available in your shop's checkout process.

## License

This project is licensed under the GPL-3.0-or-later. See the LICENSE file for details.